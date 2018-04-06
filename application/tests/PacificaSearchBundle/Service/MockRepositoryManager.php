<?php

namespace Pacifica\Search\Tests\Repository;


use Pacifica\Search\Tests\TestCase;
use PacificaSearchBundle\Repository\FileRepository;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\Repository;
use PacificaSearchBundle\Repository\TransactionRepository;
use PacificaSearchBundle\Repository\TransactionRepositoryInterface;
use PacificaSearchBundle\Repository\UserRepository;
use PacificaSearchBundle\Service\RepositoryManagerInterface;
use PacificaSearchBundle\Service\SearchServiceInterface;

/**
 * Class MockRepositoryManager
 *
 * An extension of the RepositoryManager that returns mocked versions of all repositories.
 */
class MockRepositoryManager implements RepositoryManagerInterface
{
    /** @var SearchServiceInterface */
    protected $searchService;

    /** @var TestCase */
    protected $testCase;

    /** @var array */
    protected $repositories;

    public function __construct(SearchServiceInterface $searchService, TestCase $testCase)
    {
        $this->searchService = $searchService;
        $this->testCase = $testCase;
    }

    public function getInstitutionRepository(): Repository
    {
        return $this->getMockRepository(InstitutionRepository::class);
    }

    public function getInstrumentRepository(): Repository
    {
        return $this->getMockRepository(InstrumentRepository::class);
    }

    public function getInstrumentTypeRepository(): Repository
    {
        return $this->getMockRepository(InstrumentTypeRepository::class);
    }

    public function getProposalRepository(): Repository
    {
        return $this->getMockRepository(InstitutionRepository::class);
    }

    public function getUserRepository(): Repository
    {
        return $this->getMockRepository(UserRepository::class);
    }

    public function getTransactionRepository(): TransactionRepositoryInterface
    {
        return $this->getMockRepository(TransactionRepository::class);
    }

    public function getFileRepository(): Repository
    {
        return $this->getMockRepository(FileRepository::class);
    }

    public function getRepositoryByClass($class)
    {
        return $this->getMockRepository($class);
    }
}