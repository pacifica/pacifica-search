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


    public function getAssocArrayByFilter(Filter $filter)
    {
        $qb = $this->searchService->getQueryBuilder(ElasticSearchQueryBuilder::TYPE_TRANSACTION);
        $proposalIds = $filter->getProposalIds();
        $instrumentIds = $filter->getInstrumentIds();
        $userIds = $filter->getUserIds();
        $institutionIds = $filter->getInstitutionIds();
        $groupIds = $filter->getInstrumentTypeIds();
        if(count($proposalIds)) {
            $qb->whereIn('proposals.obj_id', $proposalIds);
        }
        if(count($instrumentIds)) {
            $qb->whereIn('instruments.obj_id', $instrumentIds);
        }
        if(count($groupIds)) {
            $qb->whereIn('instrument_groups.obj_id', $groupIds);
        }
        if(!count($userIds)) {
            if (count($institutionIds)) {
                $userIds = $this->repositoryManager->getUserRepository()->getIdsByInstitution($institutionIds);
            }
        }
        if(count($userIds)) {
            $qb->whereIn('users.obj_id', $userIds);
        }
        if($filter->getText()) {
            $qb->byText($filter->getText());
        }

        $transactions = $this->searchService->getResults($qb);
        return $transactions['hits'];
    }

    /**
     * Retrieves the IDs of all transactions matching a text search. Because Transactions contain the searchable texts
     * of all related Persons, Proposals, etc, this gives us the set of all Transactions with a relationship to any
     * searchable type that matches the search.
     *
     * @param string $searchString
     * @return string[]
     */
    public function getIdsByTextSearch(string $searchString) : array
    {
        $qb = $this->searchService->getQueryBuilder(ElasticSearchQueryBuilder::TYPE_TRANSACTION)
            ->byText($searchString)
            ->fetchOnlyMetaData();

        ['hits' => $results] = $this->searchService->getResults($qb);
        $transactionIds = array_map(function ($result) { return $result['_id']; }, $results);

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
