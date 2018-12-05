<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class InstrumentRepository extends Repository
{
    /**
     * @inheritdoc
     */
    public function getOwnIdsFromTransactionResults(array $transactionResults) : array
    {
        $ids = array_map(function ($result) {
            return $result['_source']['instruments'];
        }, $transactionResults);
        return array_values(array_unique($ids));
    }

    /**
     * @inheritdoc
     */
    protected function getType() : string
    {
        return ElasticSearchQueryBuilder::TYPE_INSTRUMENT;
    }

    /**
     * Gets the IDs of all instruments belonging to a set of instrument types
     * @param string[] $instrumentTypeIds
     * @return string[]
     */
    public function getIdsByType(array $instrumentTypeIds) : array
    {
        $qb = $this->getQueryBuilder()->whereIn('groups.group_id', $instrumentTypeIds);
        $results = $this->searchService->getIds($qb);

        return $results;
    }
}
