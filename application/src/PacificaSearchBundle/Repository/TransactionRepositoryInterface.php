<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Filter;

interface TransactionRepositoryInterface
{
    /**
     * Retrieves the IDs of Transactions that are associated with at least one record of each type in the passed filter.
     * That is to say, if the filter has InstrumentTypes 1, 2, 3, and Institutions 5, 6, and 7, then this will retrieve
     * the IDs of all Transactions associated with ( (Instrument Type 1 OR 2 OR 3) AND (Institution 5 OR 6 OR 7) )
     * @param Filter $filter
     * @return int[]
     */
    public function getIdsByFilter(Filter $filter) : array;

    /**
     * Gets a set of associative arrays representing the Transactions fitting a filter
     *
     * @param Filter $filter
     * @return array
     */
    public function getAssocArrayByFilter(Filter $filter) : array;

    /**
     * @deprecated Included only as a stop-gap until we update ES database to include only items that have relationships with at least one Transaction
     * @param string $modelClass Pass e.g. InstitutionRepository::class
     * @return int[]
     */
    public function getIdsOfTypeAssociatedWithAtLeastOneTransaction($modelClass);
}
