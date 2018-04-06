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
    protected function getOwnIdsFromTransactionResults(array $transactionResults)
    {
        $transactionIds = array_map(function ($result) {
            return (int) $result['_id'];
        }, $transactionResults);

        $qb = $this->getQueryBuilder()->whereIn('transaction_id', $transactionIds);
        $fileIds = $this->searchService->getIds($qb);

        return $fileIds;
    }

    /**
     * @inheritdoc
     */
    protected function getType()
    {
        return ElasticSearchQueryBuilder::TYPE_FILE;
    }

    /**
     * Files can be the results of a filter, but they cannot be filtered on themselves
     *
     * @inheritdoc
     */
    protected function isFilterRepository()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected static function getNameFromSearchResult(array $result)
    {
        return $result['_source']['subdir'] . '/' . $result['_source']['name'];
    }

    protected static function getFileMetadataFromSearchResult(array $result)
    {
        $filesize = friendlyFileSize(intval($result['_source']['size']));
        $modtime = new DateTime($result['_source']['mtime']);
        $modtimestring = $modtime->format('D, F jS Y \a\t g:ia');
        return "(size: {$filesize} / last modified: {$modtimestring}";
    }

    protected static function friendlyFileSize($filesizebytes)
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        } elseif ($bytes < pow(1024, 2)) {
            return round($bytes / 1024, 0).' KB';
        } elseif ($bytes < pow(1024, 3)) {
            return round($bytes / pow(1024, 2), 1).' MB';
        } elseif ($bytes < pow(1024, 4)) {
            return round($bytes / pow(1024, 3), 2).' GB';
        } else {
            return round($bytes / pow(1024, 4), 2).' TB';
        }
    }

    /**
     * Retrieves the Files associated with a Transaction
     *
     * @param $transactionId
     * @return ElasticSearchTypeCollection
     */
    public function getByTransactionId($transactionId)
    {
        $qb = $this->getQueryBuilder()->whereEq('transaction_id', $transactionId);
        $fileArrays = $this->searchService->getResults($qb);
        $files = $this->resultsToTypeCollection($fileArrays);
        return $files;
    }
}
