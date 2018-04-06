<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class InstitutionRepository extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getOwnIdsFromTransactionResults(array $transactionResults)
    {
        $userRepo = $this->repositoryManager->getUserRepository();

        $userIds = $userRepo->getOwnIdsFromTransactionResults($transactionResults);
        $ids = $this->getIdsByUserIds($userIds);
        return $ids;
    }

    /**
     * Gets the IDs of a set of Institutions associated with a set of Users
     * @param int[] $userIds
     * @return int[]
     */
    protected function getIdsByUserIds(array $userIds)
    {
        $qb = $this->getQueryBuilder()->whereIn('users', $userIds);
        $ids = $this->searchService->getIds($qb);
        return $ids;
    }

    /**
     * @inheritdoc
     */
    protected function getType()
    {
        return ElasticSearchQueryBuilder::TYPE_INSTITUTION;
    }
}
