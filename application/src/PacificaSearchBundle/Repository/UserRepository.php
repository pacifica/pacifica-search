<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class UserRepository extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getType() : string
    {
        return ElasticSearchQueryBuilder::TYPE_USER;
    }

    /**
     * Gets the IDs of all users associated with a set of institutions
     * @param int[] $institutionIds
     * @return int[]
     */
    public function getIdsByInstitution(array $institutionIds) : array
    {
        $qb = $this->getQueryBuilder()->whereIn('institutions', $institutionIds);
        $results = $this->searchService->getIds($qb);

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getOwnIdsFromTransactionResults(array $transactionResults) : array
    {
        $ids = array_map(function ($result) {
            return $result['_source']['submitter'];
        }, $transactionResults);
        $ids = array_unique($ids);
        return $ids;
    }
}
