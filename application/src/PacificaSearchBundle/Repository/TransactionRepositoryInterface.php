<?php

namespace PacificaSearchBundle\Repository;


use PacificaSearchBundle\Filter;

interface TransactionRepositoryInterface
{
    /**
     * Retrieves the IDs of all transactions matching a text search. Because Transactions contain the searchable texts
     * of all related Persons, Proposals, etc, this gives us the set of all Transactions with a relationship to any
     * searchable type that matches the search.
     *
     * @param string $searchString
     * @return int[]
     */
    public function getIdsByTextSearch(string $searchString) : array;

    /**
     * @param Filter $filter
     * @param bool $flatten Pass TRUE to get a flattened array of IDs instead of by type
     * @return array in the form:
     * [
     *   'text' => [<int>, ...],
     *   Institution::getMachineName() => [<int>, ...]
     *   ...
     * ]
     * Where each array of <int> is the set of all Transaction IDs that are related to items selected in that facet.
     * If a facet has no items selected (i.e. no filtering), then that key is not present
     */
    public function getIdsByFilter(Filter $filter, bool $flatten = false) : array;
}
