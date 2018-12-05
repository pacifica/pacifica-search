<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;

class ProposalRepository extends Repository
{
    /**
     * @inheritdoc
     */
    public function getOwnIdsFromTransactionResults(array $transactionResults) : array
    {
        $ids = array_map(function ($result) {
            return $result['_source']['proposal'];
        }, $transactionResults);
        $ids = array_unique($ids);
        return $ids;
    }

    /**
     * @inheritdoc
     */
    protected function getType() : string
    {
        return ElasticSearchQueryBuilder::TYPE_PROPOSAL;
    }

    /**
     * @inheritdoc
     */
    protected static function getNameFromSearchResult(array $result) : string
    {
        return $result['_source']['display_name'] . ' (Proposal #' . explode('_', $result['_id'])[1] . ')';
    }
}
