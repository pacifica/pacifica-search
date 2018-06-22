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
     * @return Session
     */
    protected function getSession()
    {
        return $this->container->get('session');
    }
}
