<?php

namespace PacificaSearchBundle\Service;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class SearchService implements SearchServiceInterface
{
    /** @var string */
    private $host;

    /** @var string */
    private $index;

    /** @var Client */
    private $client;

    /**
     * @param string $host
     * @param string $index
     */
    public function __construct($host, $index)
    {
        $this->host = $host;
        $this->index = $index;
    }

    /**
     * Factory method that creates a new instance of ElasticSearchQueryBuilder
     * @throws \Exception
     * @param string $type One of the ElasticSearchQueryBuilder::TYPE_* constants
     * @return ElasticSearchQueryBuilder
     */
    public function getQueryBuilder($type) : ElasticSearchQueryBuilder
    {
        $qb = new ElasticSearchQueryBuilder($this->index, $type);
        return $qb;
    }

    /**
     * @inheritdoc
     */
    public function getResults(ElasticSearchQueryBuilder $queryBuilder) : array
    {
        $request = $queryBuilder->toArray();

        try {
            $response = $this->getClient()->search($request);
        } catch (\Exception $e) {
            throw new \RuntimeException("ES search request failed. Request was \n\n" . json_encode($request) . "\n\nException message:" . $e->getMessage());
        }

        $hits = $response['hits']['hits'];
        $totalHits = $response['hits']['total'];

        // If a "return filter" is set, only return the values requested.
        // TODO: Obviously we should update our code so that we can filter on values in the request instead of in script
        if ($totalHits > 0) {
            $returnFilter = $queryBuilder->getReturnFilter();
            foreach ($returnFilter as $fieldName => $valuesToKeep) {
                foreach ($hits as &$result) {
                    $result['_source'][$fieldName] = array_intersect($result['_source'][$fieldName], $valuesToKeep);
                }
            }
        }

        return [
            'hits' => $hits,
            'total_hits' => $totalHits
        ];
    }

    /**
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @return int
     */
    public function count(ElasticSearchQueryBuilder $queryBuilder) : int
    {
        $queryBuilder->fetchOnlyMetaData();
        $request = $queryBuilder->toArray();

        try {
            $response = $this->getClient()->search($request);
        } catch (\Exception $e) {
            throw new \RuntimeException("ES search request failed. Request was \n\n" . json_encode($request) . "\n\nException message:" . $e->getMessage());
        }

        return $response['hits']['total'];
    }

    /**
     * Retrieve only the IDs of the fields matched by a query
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @return int[]
     */
    public function getIds(ElasticSearchQueryBuilder $queryBuilder) : array
    {
        $results = $this->getResults($queryBuilder->fetchOnlyMetaData());

        $ids = array_map(function ($result) {
            return (int) $result['_id'];
        }, $results);

        return array_unique($ids);
    }

    /**
     * @return Client
     */
    protected function getClient() : Client
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()
                ->setHosts([$this->host])
                ->build();
        }

        return $this->client;
    }
}
