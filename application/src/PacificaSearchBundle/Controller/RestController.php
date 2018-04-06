<?php

namespace PacificaSearchBundle\Controller;

use FOS\RestBundle\View\View;
use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;
use PacificaSearchBundle\Model\Institution;
use PacificaSearchBundle\Model\Instrument;
use PacificaSearchBundle\Model\InstrumentType;
use PacificaSearchBundle\Model\Proposal;
use PacificaSearchBundle\Model\User;
use PacificaSearchBundle\Repository\FileRepository;
use PacificaSearchBundle\Repository\Repository;
use PacificaSearchBundle\Service\ElasticSearchQueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Annotations - IDE marks "unused" but they are not
use FOS\RestBundle\Controller\Annotations\Get;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\TransactionRepositoryInterface;
use PacificaSearchBundle\Repository\UserRepository;

/**
 * @codeCoverageIgnore - Because this controller relies on functionality provided by the FOSRestController there is
 * no practical way for us to convert it to use dependency injection and so it cannot be unit tested.
 */
class RestController extends BaseRestController
{
    /**
     * Sets the current filter
     *
     * The filter is made up of collections of IDs, formatted like:
     * {
     *   "instrument_types" : ["12", "22", "23"],
     *   "instruments" : ["1", "3"],
     *   "institutions" : [],
     *   "users" : ["5"],
     *   "proposals" : []
     * }
     *
     * @param Request $request
     * @return Response
     */
    public function putFilterAction(Request $request)
    {
        $filterValues = json_decode($request->getContent(), true);

        $filter = Filter::fromArray($filterValues);
        $this->getSession()->set('filter', $filter);

        return $this->handleView(View::create([ 'result' => 'Ok']));
    }

    /**
     * Retrieves a page of allowable filter items based on which items are already selected in the other filter types
     *
     * @throws \Exception
     * @param string $type
     * @param int $pageNumber
     * @return Response
     */
    public function getFilterPageAction($type, $pageNumber) : Response
    {
        if ($pageNumber < 1 || intval($pageNumber) != $pageNumber) {
            return $this->handleView(View::create([]));
        }

        /** @var Filter $filter */
        // TODO: Instead of storing the filter in the session, pass it as a request variable
        $filter = $this->getSession()->get('filter');
        if (null === $filter) {
            $filter = new Filter();
        }

        $filterableRepositories = $this->getFilterableRepositories();
        if (!array_key_exists($type, $filterableRepositories)) {
            throw new \Exception("'$type' is not a valid type. Valid options are: " . implode(', ', array_keys($filterableRepositories)));
        }

        $this->setPageByType($type, $pageNumber);

        $repository = $filterableRepositories[$type];
        $filteredPageContents = $repository->getFilteredPage($filter, $pageNumber);

        return $this->handleView(View::create($filteredPageContents));
    }

    /**
     * Retrieves a page for each filter type based on the current contents of the filter
     * @return Response
     */
    public function getFilterPagesAction() : Response
    {
        /** @var Filter $filter */
        // TODO: Instead of storing the filter in the session, pass it as a request variable
        $filter = $this->getSession()->get('filter');

        $filterPages = [];
        foreach ($this->getFilterableRepositories() as $type => $repository) {
            $filterPages[$type] = $repository->getFilteredPage(
                $filter,
                $this->getPageByType($type)
            );
        }
        return $this->handleView(View::create($filterPages));
    }

    private function getPageByType($type)
    {
        $pagesByType = $this->getPagesByType();
        return $pagesByType[$type];
    }
    private function getPagesByType()
    {
        $pagesByType = $this->getSession()->get('pages_by_type');
        if (null === $pagesByType) {
            $pagesByType = [
                Institution::getMachineName() => 1,
                Instrument::getMachineName() => 1,
                InstrumentType::getMachineName() => 1,
                Proposal::getMachineName() => 1,
                User::getMachineName() => 1
            ];
            $this->getSession()->set('pages_by_type', $pagesByType);
        }
        return $pagesByType;
    }
    private function setPageByType($type, $page)
    {
        $pagesByType = $this->getPagesByType();
        $pagesByType[$type] = $page;
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
