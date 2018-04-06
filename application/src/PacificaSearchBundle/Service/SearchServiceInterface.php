<?php

namespace PacificaSearchBundle\Service;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

interface SearchServiceInterface
{
    /**
     * Factory method that creates a new instance of ElasticSearchQueryBuilder
     * @param string $type One of the ElasticSearchQueryBuilder::TYPE_* constants
     * @return ElasticSearchQueryBuilder
     */
    public function getQueryBuilder($type);

    /**
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @param bool $assertResultsFound Pass TRUE to throw an exception if results aren't found - useful for early
     *   detection of a misconfigured or misconnected database for queries that we know should always return results.
     * @return array The results of the search
     */
    public function getResults(ElasticSearchQueryBuilder $queryBuilder, $assertResultsFound = false);

    /**
     * Retrieve only the IDs of the fields matched by a query
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @return int[]
     */
    public function getIds(ElasticSearchQueryBuilder $queryBuilder);
}
