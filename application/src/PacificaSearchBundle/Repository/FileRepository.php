<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\File;
use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;

class FileRepository extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getOwnIdsFromTransactionResults(array $transactionResults) : array
    {
        $transactionIds = array_map(function ($result) {
            return $result['_id'];
        }, $transactionResults);
        $transactionIds = array_unique($transactionIds);

        $qb = $this->getQueryBuilder()->whereIn('transaction_id', $transactionIds);
        $fileIds = $this->searchService->getIds($qb);

        return $fileIds;
    }

    /**
     * @inheritdoc
     */
    protected function getType() : string
    {
        return ElasticSearchQueryBuilder::TYPE_FILE;
    }

    /**
     * Files can be the results of a filter, but they cannot be filtered on themselves
     *
     * @inheritdoc
     */
    protected function isFilterRepository() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected static function getNameFromSearchResult(array $result) : string
    {
        return $result['_source']['subdir'] . '/' . $result['_source']['name'];
    }

    /**
     * Retrieves the Files associated with a Transaction
     *
     * @throws \Exception
     * @param $transactionId
     * @return ElasticSearchTypeCollection
     */
    public function getByTransactionId($transactionId) : ElasticSearchTypeCollection
    {
        $qb = $this->getQueryBuilder()->whereEq('transaction_id', $transactionId);
        $fileArrays = $this->searchService->getResults($qb);
        $files = $this->searchResultsToTypeCollection($fileArrays);
        return $files;
    }
}
