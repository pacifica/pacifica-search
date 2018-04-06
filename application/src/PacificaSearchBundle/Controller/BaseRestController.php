<?php

namespace PacificaSearchBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\TransactionRepositoryInterface;
use PacificaSearchBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class BaseRestController
 *
 * Common functionality for RESTful controllers used in Pacifica Search
 */
abstract class BaseRestController extends FOSRestController
{
    use FilterAwareController;

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    public function __construct(
        InstitutionRepository $institutionRepository,
        InstrumentRepository $instrumentRepository,
        InstrumentTypeRepository $instrumentTypeRepository,
        ProposalRepository $proposalRepository,
        UserRepository $userRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->initFilterableRepositories(
            $institutionRepository,
            $instrumentRepository,
            $instrumentTypeRepository,
            $proposalRepository,
            $userRepository
        );

        $this->transactionRepository = $transactionRepository;
    }

    /**
     * TODO: We're only using the Session for storing the filter at the moment, and we need to remove that because it
     * breaks in multi-tabbed browsing of the GUI and makes testing difficult. Don't add new dependencies to this method
     * because it will be removed in a future update.
     *
     * @deprecated
     * @return Session
     */
    protected function getSession()
    {
        return $this->container->get('session');
    }
}
