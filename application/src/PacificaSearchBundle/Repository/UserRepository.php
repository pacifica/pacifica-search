<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class UserRepository extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getType()
    {
        return ElasticSearchQueryBuilder::TYPE_USER;
    }

    /**
     * @inheritdoc
     *
     * Differs from the default implementation because users have no "name" field, instead their names are built from
     * their first, middle, and last name fields.
     */
    protected static function getNameFromSearchResult(array $result)
    {
        $lastName = $result['_source']['last_name'];
        $firstName = $result['_source']['first_name'];
        $middleInitial = $result['_source']['middle_initial'];

        return "$lastName, $firstName" . ($middleInitial ? " $middleInitial." : '');
    }

    /**
     * Gets the IDs of all users associated with a set of institutions
     * @param int[] $institutionIds
     * @return int[]
     */
    public function getIdsByInstitution(array $institutionIds)
    {
        $qb = $this->getQueryBuilder()->whereIn('institutions', $institutionIds);
        $results = $this->searchService->getIds($qb);

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getOwnIdsFromTransactionResults(array $transactionResults)
    {
        $ids = array_map(function ($result) {
            return $result['_source']['submitter'];
        }, $transactionResults);
        return $ids;
    }
}
