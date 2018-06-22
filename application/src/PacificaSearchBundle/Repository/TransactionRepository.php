<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;
use PacificaSearchBundle\Service\RepositoryManagerInterface;
use PacificaSearchBundle\Service\SearchServiceInterface;

/**
 * Class TransactionRepository
 *
 * Note that the TransactionRepository doesn't inherit from Repository - this is because Transactions are not used
 * in a similar way to any of the other data types. They are only queried to retrieve their IDs, and so there is no
 * corresponding Model class to represent them, and this class doesn't have to perform any of the duties the other
 * repositories have to do.
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /** @var SearchServiceInterface */
    protected $searchService;

    /** @var RepositoryManagerInterface */
    protected $repositoryManager;

    public function __construct(
        SearchServiceInterface $searchService,
        RepositoryManagerInterface $repositoryManager
    ) {
        $this->searchService = $searchService;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * Retrieves the IDs of all transactions matching a text search. Because Transactions contain the searchable texts
     * of all related Persons, Proposals, etc, this gives us the set of all Transactions with a relationship to any
     * searchable type that matches the search.
     *
     * @param string $searchString
     * @return int[]
     */
    public function getIdsByTextSearch(string $searchString) : array
    {
        $qb = $this->searchService->getQueryBuilder(ElasticSearchQueryBuilder::TYPE_TRANSACTION)
            ->byText($searchString)
            ->fetchOnlyMetaData();

        ['hits' => $results] = $this->searchService->getResults($qb);
        $transactionIds = array_map(function ($result) {
            // The object IDs in the transaction object are formatted like transaction_<ID> but no other type uses
            // that format, so convert to standard integer IDs before returning
            return (int)explode('_', $result['_id'])[1];
        }, $results);

        return $transactionIds;
    }

    /**
     * @throws \Exception
     * @inheritdoc
     */
    public function getIdsByFilter(Filter $filter, bool $flatten = false) : array
    {
        $results = [];

        $filterText = $filter->getText();
        if (strlen($filterText)) {
            $results['text'] = $this->getIdsByTextSearch($filterText);
        }

        $repositories = $this->repositoryManager->getFilterableRepositories();
        foreach ($repositories as $typeMachineName => $repository) {
            $idsByType = $filter->getIdsByType($repository->getModelClass());
            if (count($idsByType) > 0) {
                $results[$typeMachineName] = $repository->getTransactionIdsByOwnIds($idsByType);
            }
        }

        if ($flatten) {
            $resultsFlattened = [];
            foreach ($results as $result) {
                $resultsFlattened = array_merge($result, $resultsFlattened);
            }
            $results = array_unique($resultsFlattened);
        }

        return $results;
    }
}
