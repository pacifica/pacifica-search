<?php

namespace PacificaSearchBundle\Service;

class ElasticSearchQueryBuilder
{
    /**
     * TYPE_* constants contain the Elasticsearch names of the types. Note that these do NOT map 1:1 to types that
     * are represented by this application's model classes. The reason is that, for example, an InstrumentType is
     * actually a subset of the records from type Group in Elasticsearch.
     */
    const TYPE_GROUP = 'groups';
    const TYPE_INSTRUMENT = 'instruments';
    const TYPE_INSTITUTION = 'institutions';
    const TYPE_USER = 'users';
    const TYPE_PROPOSAL = 'proposals';
    const TYPE_TRANSACTION = 'transactions';
    const TYPE_FILE = 'files';
    const TYPE_ANY = null;

    private const VALID_TYPES = [
        self::TYPE_GROUP,
        self::TYPE_INSTRUMENT,
        self::TYPE_INSTITUTION,
        self::TYPE_USER,
        self::TYPE_PROPOSAL,
        self::TYPE_TRANSACTION,
        self::TYPE_FILE
    ];

    /**
     * Defines values of fields that must be present in a record for it to be returned
     *
     * @var array[]
     */
    private $fields = [];

    /**
     * Allows the values to be returned for a particular field to be filtered
     *
     * @var array
     */
    private $returnFilter = [];

    /**
     * The Elasticsearch index that will be queried
     *
     * @var string
     */
    private $index;

    /**
     * The Type that will be queried. One of this class's TYPE_* constants
     *
     * @var string
     */
    private $type;

    /**
     * IDs on which to filter the results of this request
     *
     * @var int[]
     */
    private $ids;

    /**
     * IDs that will be excluded from the results of this request
     *
     * @var int[]
     */
    private $idsToExclude;

    /**
     * A text-based query string - results will be filtered to include records with any value that matches the string
     *
     * @var string
     */
    private $text;

    /** @var int */
    private $pageNumber;

    /** @var int */
    private $pageSize;

    /**
     * If TRUE then field values will not be returned by the query, instead only metadata will be returned. This is
     * useful primarily for queries that only need to retrieve the ID of a record and don't care about the rest of the
     * data.
     *
     * @var bool
     */
    private $metadataOnly = false;

    /**
     * @param string $index
     * @param string $type
     * @throws \Exception
     */
    public function __construct(string $index, string $type)
    {
        $this->assertValidType($type);

        $this->index = $index;
        $this->type = $type;

        // Set defaults for pagination - The first page but with a very large page size, which is equivalent to
        // having no pagination but a sane maximum number of results.
        $this->pageNumber = 1;
        $this->pageSize = 10000;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Equivalent to SQL "WHERE {fieldName} = {value}" or "WHERE {fieldName} IN ({values})"
     *
     * @param string $fieldName
     * @param string|string[] $value
     * @return ElasticSearchQueryBuilder
     */
    public function whereEq($fieldName, $value) : ElasticSearchQueryBuilder
    {
        if ($fieldName === 'id') {
            return $this->byId($value);
        }

        $newValues = is_array($value) ? $value : [ $value ];

        if (!array_key_exists($fieldName, $this->fields)) {
            $this->fields[$fieldName] = [];
        }

        $this->fields[$fieldName] = array_merge($this->fields[$fieldName], array_values($newValues));

        return $this;
    }

    /**
     * Alias of whereEq(), for use with multiple values for readability
     * @param $fieldName
     * @param $values
     * @return ElasticSearchQueryBuilder
     */
    public function whereIn($fieldName, $values) : ElasticSearchQueryBuilder
    {
        return $this->whereEq($fieldName, $values);
    }

    /**
     * For fields that return arrays of values, return only the requested values
     *
     * Note: Currently this is implemented by actually returning all the values but filtering them out in the
     * SearchService. It looks like a script field might be required to actually filter values before returning them.
     *
     * @param string $fieldName
     * @param array $values
     * @return ElasticSearchQueryBuilder
     */
    public function filterReturned(string $fieldName, array $values) : ElasticSearchQueryBuilder
    {
        $this->returnFilter[$fieldName] = $values;
        return $this;
    }

    public function getReturnFilter() : array
    {
        return $this->returnFilter;
    }

    /**
     * @param int|int[] $ids
     * @return ElasticSearchQueryBuilder
     */
    public function byId($ids) : ElasticSearchQueryBuilder
    {
        if (!is_array($ids)) {
            $ids = [ $ids ];
        }

        $this->ids = array_values($ids); // array_values() required because IDs queries break on associative arrays

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function byText($text) : ElasticSearchQueryBuilder
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param int|int[] $ids
     * @return ElasticSearchQueryBuilder
     */
    public function excludeIds($ids) : ElasticSearchQueryBuilder
    {
        if (!is_array($ids)) {
            $ids = [ $ids ];
        }

        $this->idsToExclude = array_values($ids); // array_values() required because IDs queries break on associative arrays

        return $this;
    }

    /**
     * Makes the query a paginated query
     *
     * @param int $pageNumber
     * @param int $pageSize
     * @return ElasticSearchQueryBuilder
     */
    public function paginate($pageNumber, $pageSize) : ElasticSearchQueryBuilder
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * Restricts the query so that it will only retrieve the IDs of the matching fields
     * @return ElasticSearchQueryBuilder
     */
    public function fetchOnlyMetaData() : ElasticSearchQueryBuilder
    {
        $this->metadataOnly = true;
        return $this;
    }

    public function toArray() : array
    {
        $array = [
            'index' => $this->index,
            'size' =>  $this->pageSize,
            'from' => ($this->pageNumber-1) * $this->pageSize, // The -1 is necessary because pageSize is 1-based
        ];

        // https://www.elastic.co/guide/en/elasticsearch/guide/current/multi-index-multi-type.html
        // ElasticSearch supports comma-separated lists of Types, which we use to make sure no Types other than those
        // we care about are included in our result
        if ($this->type === self::TYPE_ANY) {
            $array['type'] = implode(',', self::VALID_TYPES);
        } else {
            $array['type'] = $this->type;
        }

        if ($this->metadataOnly) {
            $array['body']['_source'] = false;
        }

        if ($this->ids) {
            // TODO: Once IDs are integers again, we can remove this.
            if ($this->type === self::TYPE_ANY) {
                throw new \Exception("Because of the type strings currently prepended to IDs, it's not possible to support ID queries for queries of TYPE_ANY");
            }

            $ids = array_map(function ($id) { return $this->type . '_' . $id; }, $this->ids);

            $array['body']['query']['terms'] = [ '_id' => $ids ];
        }

        if ($this->idsToExclude) {
            $array['body']['query']['bool']['must_not'][]['ids']['values'] = $this->idsToExclude;
        }

        foreach ($this->fields as $fieldName => $fieldValues) {
            $array['body']['query']['bool']['filter'][] = ['terms' => [$fieldName => $fieldValues]];
        }

        if ($this->text) {
            $array['body']['query']['bool']['must']['query_string']['default_field'] = '_all';
            $array['body']['query']['bool']['must']['query_string']['query'] = $this->text;
        }

        return $array;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    private function assertValidType(string $type)
    {
        // TYPE_ANY is checked separately because it's really not a type but rather represents all valid types
        if ($type !== self::TYPE_ANY && !in_array($type, self::VALID_TYPES)) {
            throw new \Exception("Type '$type' is not a valid value. Allowed values are '" . implode(',', self::VALID_TYPES) . "'");
        }
    }
}
