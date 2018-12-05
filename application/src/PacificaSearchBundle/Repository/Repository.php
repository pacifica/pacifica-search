<?php

namespace PacificaSearchBundle\Repository;

use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;
use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;
use PacificaSearchBundle\Service\RepositoryManagerInterface;
use PacificaSearchBundle\Service\SearchServiceInterface;

/**
 * Class Repository
 *
 * Base class for Pacifica Search repositories
 */
abstract class Repository
{
    const DEFAULT_PAGE_SIZE = 10;

    /** @var SearchServiceInterface */
    protected $searchService;

    /** @var RepositoryManagerInterface */
    protected $repositoryManager;

    final public function __construct(SearchServiceInterface $searchService, RepositoryManagerInterface $repositoryManager)
    {
        $this->searchService = $searchService;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * Gets a set of the IDs of all instances that are consistent with the passed Filter.
     *
     * Note that this method differs from getFilterIdsConsistentWithFilter() in that we *do* allow the type to filter
     * itself. The concrete reason for that difference is that we use this method to get That is because it is possible
     * for a user to select Proposals in the Filter in order to reduce the set of Proposals shown in the file tree.
     *
     * @throws \Exception
     * @param Filter $filter
     * @return array
     */
    public function getFilteredIds(Filter $filter) : array
    {
        $transactionIds = $this->repositoryManager->getTransactionRepository()->getIdsByFilter($filter, true);
        return $this->getIdsByTransactionIds($transactionIds);
    }

    /**
     * Gets the name of the model class of the type that this repository is responsible for. Will attempt to find the
     * class by the convention that repository classes are named <ModelClass>Repository, override this method if that's
     * not the case.
     *
     * @throws \Exception
     */
    public function getModelClass() : string
    {
        // Remove the "Repository" suffix from the repo's class name
        $modelClassName = preg_replace('/Repository$/', '', static::class);

        // Change the namespace from "Repository" to "Model"
        $modelClassName = str_replace('\\Repository\\', '\\Model\\', $modelClassName);

        if (!class_exists($modelClassName)) {
            throw new \Exception(
                "No class $modelClassName could be found. If your Model and/or Repository don't follow the " .
                "expected naming convention, your repository must implement its own version of getModelClass()"
            );
        }

        return $modelClassName;
    }

    /**
     * @throws \Exception
     * @param string[] $ids
     * @return ElasticSearchTypeCollection
     */
    public function getById(array $ids) : ElasticSearchTypeCollection
    {
        $response = $this->searchService->getResults($this->getQueryBuilder()->whereIn('id', $ids));
        return $this->searchResultsToTypeCollection($response);
    }

    /**
     * Retrieves a page of model objects that fit the passed filter.
     *
     * @throws \Exception
     * @param Filter $filter
     * @param int $pageNumber 1-based page number
     * @return ElasticSearchTypeCollection
     */
    public function getFilteredPage(Filter $filter, $pageNumber) : ElasticSearchTypeCollection
    {
        $qb = $this->getQueryBuilder();
        $qb->paginate($pageNumber, self::DEFAULT_PAGE_SIZE);

        $filteredIds = $this->getIdsThatMayBeAddedToFilter($filter);

        // We exclude any IDs from the result that are already in the filter, because otherwise the returned
        // page would include items the user has already selected and added to the filter.
        $idsToExclude = $filter->getIdsByType($this->getModelClass());

        // We can only call byId() or excludeIds() - the two are mutually incompatible calls (TODO: maybe fix that)
        if (empty($filteredIds)) {
            $qb->excludeIds($idsToExclude);
        } else {
            $filteredIds = array_diff($filteredIds, $idsToExclude);

            // If the result of removing $idsToExclude from the legal filter options is that
            // no filter options remain, return an empty collection instead of running an ES query
            if (empty($filteredIds)) {
                return new ElasticSearchTypeCollection();
            }

            $qb->byId($filteredIds);
        }

        $response = $this->searchService->getResults($qb);
        return $this->searchResultsToTypeCollection($response);
    }

    /**
     * Retrieves a page of model objects that are related to the passed set of transactions.
     *
     * @throws \Exception
     * @param string[] $transactionIds
     * @param int $pageNumber
     * @param string[] $ownIdsToExclude And IDs of the repository's own type that should be excluded from the result. This
     *        allows us for example to exclude currently selected items from the result set.
     * @return ElasticSearchTypeCollection
     */
    public function getPageByTransactionIds(array $transactionIds, int $pageNumber, array $ownIdsToExclude) : ElasticSearchTypeCollection
    {
        $qb = $this->getQueryBuilder();
        $qb->paginate($pageNumber, self::DEFAULT_PAGE_SIZE);

        if (count($transactionIds)) {
            $qb->whereIn('transaction_ids', $transactionIds);
            $qb->filterReturned('transaction_ids', $transactionIds);
        }

        $qb->excludeIds($ownIdsToExclude);

        return $this->searchResultsToTypeCollection($this->searchService->getResults($qb));
    }

    /**
     * Retrieves a page of model objects that fit the passed query string
     *
     * @throws \Exception
     * @param string $searchQuery
     * @param int $pageNumber 1-based page number
     * @return ElasticSearchTypeCollection
     */
    public function getPageByTextSearch($searchQuery, $pageNumber) : ElasticSearchTypeCollection
    {
        $qb = $this->getQueryBuilder()
            ->paginate($pageNumber, self::DEFAULT_PAGE_SIZE)
            ->byText($searchQuery);

        $response = $this->searchService->getResults($qb);
        return $this->searchResultsToTypeCollection($response);
    }

    /**
     * Retrieve the IDs of all instances that could be added to the passed filter without resulting in an empty result.
     * It is important to note that each repository must ignore filters of its own type. That is to say, if an
     * Institution is selected, that should not prohibit the addition of further Institutions to the Filter - only
     * adding Filter items of other types should impact the options of a given type.
     *
     * @throws \Exception
     * @param Filter $filter
     * @return array
     */
    protected function getIdsThatMayBeAddedToFilter(Filter $filter) : array
    {
        // Clone the filter before making any changes so that the caller's filter doesn't get changed
        $filter = clone $filter;

        // Remove filters of own type if applicable - you can't restrict Users by picking a User
        if ($this->isFilterRepository()) {
            $filter->setIdsByType($this->getModelClass(), []);
        }

        $transactionIds = $this->repositoryManager->getTransactionRepository()->getIdsByFilter($filter, true);
        $ownIds = $this->getIdsByTransactionIds($transactionIds);

        return $ownIds;
    }

    /**
     * Returns true for repositories that store objects that can be a part of a filter. Override for repositories that
     * cannot be included in the filter to avoid triggering behavior that is specific to filter repositories.
     *
     * @return bool
     */
    protected function isFilterRepository() : bool
    {
        return true;
    }

    /**
     * Gets IDs of this type that are associated with a set of transaction IDs
     *
     * @throws \Exception
     * @param string[] $transactionIds
     * @return string[]
     */
    protected function getIdsByTransactionIds(array $transactionIds)
    {
        $qb = $this->getQueryBuilder()
            ->fetchOnlyMetaData()
            ->whereIn('transaction_ids', $transactionIds);
        $results = $this->searchService->getResults($qb)['hits'];
        return array_map(function ($r) {
            return $r['_id'];
        }, $results);
    }

    /**
     * Given a set of IDs of the repository's own type, retrieves the IDs of all transactions associated with all of
     * the records with those IDs.
     *
     * @param string[] $ownIds
     * @return string[]
     */
    public function getTransactionIdsByOwnIds(array $ownIds) : array
    {
        $qb = $this->getQueryBuilder()->byId($ownIds);

        $results = $this->searchService->getAggregationResults(
            $qb,
            [
                'transaction_ids' => [
                    'terms' => [
                        'field' => 'transaction_ids',
                        'size' => 1000000
                    ]
                ]
            ]
        );

        $transactionIds = array_map(function ($bucket) {
            return $bucket['key'];
        }, $results['transaction_ids']['buckets']);

        return $transactionIds;
    }

    /**
     * @param array $transactionResults An array returned by SearchService when given a query for TYPE_TRANSACTION
     * @return string[] Array containing all IDs of this Repository's type that correspond to the returned transaction
     * records
     */
    abstract protected function getOwnIdsFromTransactionResults(array $transactionResults) : array;

    /**
     * @throws \Exception
     * @param array $searchResult in the form returned by SearchServiceInterface::getResults()
     * @return ElasticSearchTypeCollection
     */
    protected function searchResultsToTypeCollection(array $searchResult) : ElasticSearchTypeCollection
    {
        ['hits' => $hits, 'total_hits' => $totalHits] = $searchResult;

        $collection = new ElasticSearchTypeCollection([], $totalHits);
        foreach ($hits as $curHit) {
            $modelClass = $this->getModelClass();
            $instance = new $modelClass(
                $curHit['_id'],
                static::getNameFromSearchResult($curHit),
                count($curHit['_source']['transaction_ids'])
            );
            $collection->add($instance);
        }

        return $collection;
    }

    /**
     * Gets a query builder that, when submitted to the SearchService, will return all records of the type this
     * repository is responsible for. Further filters can then be added by calling additional methods on the query
     * builder.
     *
     * @return \PacificaSearchBundle\Service\ElasticSearchQueryBuilder
     */
    protected function getQueryBuilder() : ElasticSearchQueryBuilder
    {
        return $this->searchService->getQueryBuilder($this->getType());
    }

    /**
     * Gets the type (one of the ElasticSearchQueryBuilder::TYPE_* constants) that this repository is responsible for
     */
    abstract protected function getType() : string;

    /**
     * Given a single result (an entry from the "hits" field of an Elastic Search results object), returns a string
     * representing the display name of the result. The most common case is implemented here - for other cases, override
     * this function.
     *
     * @param array $result
     * @return string
     */
    protected static function getNameFromSearchResult(array $result) : string
    {
        return $result['_source']['display_name'];
    }
}
