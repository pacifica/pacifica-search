<?php

namespace PacificaSearchBundle\Service;

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
     * @return array The results of the search in the form:
     * [
     *   'hits' => array of ES records, each itself represented by an array
     *   'total_hits' => <int> The number of total records matching the search. May differ from the size of 'hits' if
     *                   a requests 'size' property was less than the total number of search matches.
     * ]
     */
    public function getResults(ElasticSearchQueryBuilder $queryBuilder);

    /**
     * Retrieve only the IDs of the fields matched by a query
     * @param ElasticSearchQueryBuilder $queryBuilder
     * @return int[]
     */
    public function getIds(ElasticSearchQueryBuilder $queryBuilder);
}
