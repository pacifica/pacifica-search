<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class InstitutionRepository extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getOwnIdsFromTransactionResults(array $transactionResults) : array
    {
        $userRepo = $this->repositoryManager->getUserRepository();

        $userIds = $userRepo->getOwnIdsFromTransactionResults($transactionResults);
        $ids = $this->getIdsByUserIds($userIds);
        return $ids;
    }

    /**
     * Gets the IDs of a set of Institutions associated with a set of Users
     * @param string[] $userIds
     * @return string[]
     */
    protected function getIdsByUserIds(array $userIds)
    {
        $qb = $this->getQueryBuilder()->whereIn('users', $userIds);
        $ids = $this->searchService->getIds($qb);
        $ids = array_unique($ids);
        return $ids;
    }

    /**
     * @inheritdoc
     */
    protected function getType() : string
    {
        return ElasticSearchQueryBuilder::TYPE_INSTITUTION;
    }
}
