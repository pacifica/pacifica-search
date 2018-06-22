<?php

namespace PacificaSearchBundle\Controller;

use FOS\RestBundle\View\View;
use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\Institution;
use PacificaSearchBundle\Model\Instrument;
use PacificaSearchBundle\Model\InstrumentType;
use PacificaSearchBundle\Model\Proposal;
use PacificaSearchBundle\Model\User;
use PacificaSearchBundle\Repository\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore - Because this controller relies on functionality provided by the FOSRestController there is
 * no practical way for us to convert it to use dependency injection and so it cannot be unit tested.
 */
class RestController extends BaseRestController
{
    /**
     * Retrieves a page of allowable filter items based on which items are already selected in the other filter types
     *
     * @throws \Exception
     * @param string $type
     * @param int $pageNumber
     * @param Request $request
     * @return Response
     */
    public function postFilterPageAction($type, $pageNumber, Request $request) : Response
    {
        if ($pageNumber < 1 || intval($pageNumber) != $pageNumber) {
            return $this->handleView(View::create([]));
        }

        $filterableRepositories = $this->getFilterableRepositories();
        if (!array_key_exists($type, $filterableRepositories)) {
            throw new \Exception("'$type' is not a valid type. Valid options are: " . implode(', ', array_keys($filterableRepositories)));
        }

        $repository = $filterableRepositories[$type];
        $filter = Filter::fromRequest($request);
        $filteredPageContents = $repository->getFilteredPage($filter, $pageNumber);

        return $this->handleView(View::create($filteredPageContents));
    }

    /**
     * Retrieves a page for each searchable type based on the contents of the filter
     * @throws \Exception
     * @param Request $request
     * @return Response
     */
    public function postFilterPagesAction(Request $request) : Response
    {
        $filter = Filter::fromRequest($request);
        $transactionIdsByFilterItem = $this->transactionRepository->getIdsByFilter($filter);

        $filterPages = [];
        foreach ($this->getFilterableRepositories() as $type => $repository) {
            // Make a copy of the set of transactions but remove each type's own filter results - the contents of any
            // given filter type are not constrained by what's already been chosen in that filter type, only by the
            // selection of other filter types. For example, if you pick a user, you can still pick any other user, not
            // only other users that share transactions with the original user.
            $transIds = $transactionIdsByFilterItem;
            unset($transIds[$type]);
            $filteredTransactionIds = array_of_arrays_intersect($transIds);

            $filterPages[$type] = $repository->getPageByTransactionIds(
                $filteredTransactionIds,
                1,

                 // Exclude results that are already selected in the filter - we don't want selected options to be offered to the user again
                $filter->getIdsByType($repository->getModelClass())
            );
        }

        // Get a set of all transactions that fit the filter so that we can provide the UI with a count of transactions
        // that pass the current filter.
        $allTransactionIds = array_of_arrays_intersect($transactionIdsByFilterItem);

        return $this->handleView(View::create([
            'transaction_count' => count($allTransactionIds),
            'filter_pages' => $filterPages
        ]));
    }

    /**
     * @throws \InvalidArgumentException
     * @param $type
     * @return Repository
     */
    protected function getRepositoryByType($type) : Repository
    {
        $repositoriesByType = [
            Institution::getMachineName() => $this->institutionRepository,
            Instrument::getMachineName() => $this->instrumentRepository,
            InstrumentType::getMachineName() => $this->instrumentTypeRepository,
            Proposal::getMachineName() => $this->proposalRepository,
            User::getMachineName() => $this->userRepository
        ];

        if (!array_key_exists($type, $repositoriesByType)) {
            throw new \InvalidArgumentException("Type $type is not a valid type. Valid types are " . implode(', ', array_keys($repositoriesByType)));
        }

        return $repositoriesByType[$type];
    }
}
