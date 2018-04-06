<?php

namespace PacificaSearchBundle\Service;

class ElasticSearchQueryBuilder
{
    /**
     * TYPE_* constants contain the Elasticsearch names of the types. Note that these do NOT map 1:1 to types that
     * are represented by this application's model classes. The reason is that, for example, an InstrumentType is
     * actually a subset of the records from type Group in Elasticsearch.
     */
    const TYPE_GROUP = 'Groups';
    const TYPE_INSTRUMENT = 'Instruments';
    const TYPE_INSTITUTION = 'Institutions';
    const TYPE_USER = 'Users';
    const TYPE_PROPOSAL = 'Proposals';
    const TYPE_TRANSACTION = 'Transactions';
    const TYPE_FILE = 'Files';

    /**
     * Defines values of fields that must be present in a record for it to be returned
     *
     * @var array[]
     */
    private $fields = [];

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

    public function __construct($index, $type)
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
    public function getType()
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
    public function whereEq($fieldName, $value)
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
    public function whereIn($fieldName, $values)
    {
        return $this->whereEq($fieldName, $values);
    }

    /**
     * @param int|int[] $ids
     * @return ElasticSearchQueryBuilder
     */
    public function byId($ids)
    {
        if (!is_array($ids)) {
            $ids = [ $ids ];
        }

        $this->ids = array_values($ids); // array_values() required because IDs queries break on associative arrays

        return $this;
    }

    /**
     * @param int|int[] $ids
     * @return ElasticSearchQueryBuilder
     */
    public function excludeIds($ids)
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
     */
    public function paginate($pageNumber, $pageSize)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
    }

    /**
     * Restricts the query so that it will only retrieve the IDs of the matching fields
     * @return ElasticSearchQueryBuilder
     */
    public function fetchOnlyMetaData()
    {
        $this->metadataOnly = true;
        return $this;
    }

    public function toArray()
    {
        $array = [
            'index' => $this->index,
            'size' =>  $this->pageSize,
            'from' => ($this->pageNumber-1) * $this->pageSize, // The -1 is necessary because pageSize is 1-based
            'type' => $this->type
        ];

        if ($this->metadataOnly) {
            $array['body']['_source'] = false;
        }

        if ($this->ids) {
            $array['body']['query']['terms'] = [
                '_id' => $this->ids
            ];

            return $array;
        }

        if ($this->idsToExclude) {
            $array['body']['query']['bool']['must_not'][]['ids']['values'] = $this->idsToExclude;
            return $array;
        }

        foreach ($this->fields as $fieldName => $fieldValues) {
            $array['body']['query']['bool']['filter'][] = ['terms' => [$fieldName => $fieldValues]];
        }

        return $array;
    }

    private function assertValidType($type)
    {
        $validTypes = [
            self::TYPE_GROUP,
            self::TYPE_INSTITUTION,
            self::TYPE_INSTRUMENT,
            self::TYPE_PROPOSAL,
            self::TYPE_USER,
            self::TYPE_TRANSACTION,
            self::TYPE_FILE
        ];

        if (!in_array($type, $validTypes)) {
            throw new \Exception("Type '$type' is not a valid value. Allowed values are '" . implode(',', $validTypes) . "'");
        }
    }
}
