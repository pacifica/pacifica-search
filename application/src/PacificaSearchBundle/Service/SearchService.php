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
        // Convenience for the development environment: You can specify your host option as "devserver:PORT" and the
        // "devserver" bit will automatically be set to the machine hosting your client, which will also be the ES
        // server in the Docker dev environment. This is nice because your IP address can change from network to network
        // and it's annoying to have to update your parameters file every time.
        if (strpos($host, 'devserver') === 0) {
            $host = str_replace('devserver', $_SERVER['REMOTE_ADDR'], $host);
        }

        $this->host = $host;
        $this->index = $index;
    }

    /**
     * Factory method that creates a new instance of ElasticSearchQueryBuilder
     * @param string $type One of the ElasticSearchQueryBuilder::TYPE_* constants
     * @return ElasticSearchQueryBuilder
     */
    public function getQueryBuilder($type)
    {
        $qb = new ElasticSearchQueryBuilder($this->index, $type);
        return $qb;
    }

    /**
     * @throws \RuntimeException
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @param bool $assertResultsFound Pass TRUE to throw an exception if results aren't found - useful for early
     *   detection of a misconfigured or misconnected database for queries that we know should always return results.
     * @return array The results of the search
     */
    public function getResults(ElasticSearchQueryBuilder $queryBuilder, $assertResultsFound = false)
    {
        $client = $this->getClient();
        $request = $queryBuilder->toArray();

        try {
            $response = $client->search($request);
        } catch (\Exception $e) {
            throw new \RuntimeException("ES search request failed. Request was \n\n" . json_encode($request) . "\n\nException message:" . $e->getMessage());
        }

        if ($assertResultsFound && $response['hits']['total'] === 0) {
            throw new \RuntimeException("The " . $queryBuilder->getType() . " type in the Elasticsearch DB appears to be empty.");
        }

        return $response['hits']['hits'];
    }

    /**
     * TODO: Make this more elegant so that a getResults() and a count() on the same QB don't run the query twice
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @return int
     */
    public function count(ElasticSearchQueryBuilder $queryBuilder)
    {
        $client = $this->getClient();

        $queryBuilder->fetchOnlyMetaData();
        $request = $queryBuilder->toArray();

        try {
            $response = $client->search($request);
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
    public function getIds(ElasticSearchQueryBuilder $queryBuilder)
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
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()
                ->setHosts([$this->host])
                ->build();
        }

        return $this->client;
    }
}
