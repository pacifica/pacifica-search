<?php

namespace PacificaSearchBundle\Service;

use PacificaSearchBundle\Repository\FileRepository;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\Repository;
use PacificaSearchBundle\Repository\TransactionRepository;
use PacificaSearchBundle\Repository\TransactionRepositoryInterface;
use PacificaSearchBundle\Repository\UserRepository;
use phpDocumentor\Reflection\Types\Mixed_;

/**
 * Class RepositoryManager
 *
 * We require a manager class that can be used to offer repositories access to one another because we have circular
 * relationships between some repositories that make simple dependency injection impossible
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /** @var SearchServiceInterface */
    private $searchService;

    /** @var InstitutionRepository */
    private $institutionRepository;

    /** @var InstrumentRepository */
    private $instrumentRepository;

    /** @var InstrumentTypeRepository */
    private $instrumentTypeRepository;

    /** @var ProposalRepository */
    private $proposalRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var TransactionRepository */
    private $transactionRepository;

    /** @var FileRepository */
    private $fileRepository;

    public function __construct(SearchServiceInterface $searchService)
    {
        $this->searchService = $searchService;
    }

    public function getInstitutionRepository() : Repository
    {
        if ($this->institutionRepository === null) {
            $this->institutionRepository = new InstitutionRepository($this->searchService, $this);
        }

        return $this->institutionRepository;
    }

    public function getInstrumentRepository() : Repository
    {
        if ($this->instrumentRepository === null) {
            $this->instrumentRepository = new InstrumentRepository($this->searchService, $this);
        }

        return $this->instrumentRepository;
    }

    public function getInstrumentTypeRepository() : Repository
    {
        if ($this->instrumentTypeRepository === null) {
            $this->instrumentTypeRepository = new InstrumentTypeRepository($this->searchService, $this);
        }

        return $this->instrumentTypeRepository;
    }

    public function getProposalRepository() : Repository
    {
        if ($this->proposalRepository === null) {
            $this->proposalRepository = new ProposalRepository($this->searchService, $this);
        }

        return $this->proposalRepository;
    }

    public function getUserRepository() : Repository
    {
        if ($this->userRepository === null) {
            $this->userRepository = new UserRepository($this->searchService, $this);
        }

        return $this->userRepository;
    }

    public function getTransactionRepository() : TransactionRepositoryInterface
    {
        if ($this->transactionRepository === null) {
            $this->transactionRepository = new TransactionRepository($this->searchService, $this);
        }

        return $this->transactionRepository;
    }

    public function getFileRepository() : Repository
    {
        if ($this->fileRepository === null) {
            $this->fileRepository = new FileRepository($this->searchService, $this);
        }

        return $this->fileRepository;
    }
}
