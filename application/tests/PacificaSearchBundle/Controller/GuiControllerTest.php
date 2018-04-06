<?php

namespace Pacifica\Search\Tests\Controller;

use Pacifica\Search\Tests\Repository\MockRepositoryManager;
use Pacifica\Search\Tests\TestCase;
use PacificaSearchBundle\Controller\GuiController;
use PacificaSearchBundle\Exception\NoRecordsFoundException;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\Repository;
use PacificaSearchBundle\Repository\TransactionRepository;
use PacificaSearchBundle\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\TwigEngine;

class GuiControllerTest extends TestCase
{
    /** @var MockObject */
    private $mockSearchService;

    /** @var MockObject */
    private $mockRepositoryManager;

    /** @var MockObject */
    private $mockInstitutionRepository;

    /** @var MockObject */
    private $mockInstrumentRepository;

    /** @var MockObject */
    private $mockInstrumentTypeRepository;

    /** @var MockObject */
    private $mockProposalRepository;

    /** @var MockObject */
    private $mockUserRepository;

    /** @var MockObject */
    private $mockTransactionRepository;

    /**
     * The class being tested
     *
     * @var GuiController
     */
    private $controller;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mockSearchService = $this->getMockBuilder('\\PacificaSearchBundle\\Service\\SearchService')
            ->setConstructorArgs(['dummyHost', 'dummyIndex'])
            ->getMock();

        $this->mockRepositoryManager = new MockRepositoryManager($this->mockSearchService, $this);

        $this->mockInstitutionRepository    = $this->getMockRepository(InstitutionRepository::class);
        $this->mockInstrumentRepository     = $this->getMockRepository(InstrumentRepository::class);
        $this->mockInstrumentTypeRepository = $this->getMockRepository(InstrumentTypeRepository::class);
        $this->mockProposalRepository       = $this->getMockRepository(ProposalRepository::class);
        $this->mockUserRepository           = $this->getMockRepository(UserRepository::class);

        // We use createMock() directly for TransactionRepository because TransactionRepository is not a child class of
        // Repository (because it works very differently from other repository types)
        $this->mockTransactionRepository = $this->createMock(TransactionRepository::class);

        $this->controller = new GuiController(
            $this->mockInstitutionRepository,
            $this->mockInstrumentRepository,
            $this->mockInstrumentTypeRepository,
            $this->mockProposalRepository,
            $this->mockUserRepository,
            $this->mockTransactionRepository,
            $this->createMock(TwigEngine::class)
        );
    }

    /** @test */
    public function indexAction()
    {
        // If we haven't wired up any of our repos to return values yet, then we should get a NoRecordsFound exception
        // because in the real world an empty repository would indicate a serious problem with either the ElasticSearch
        // database or with our ElasticSearch configuration
        $this->assertThrows(function () {
            $this->controller->indexAction();
        },NoRecordsFoundException::class);


        // Now we wire up all of the repositories with fixtures that will allow the search page to be rendered, and verify
        // that a valid response is generated.

        $repositoriesNeedingFixtures = [
            $this->mockInstitutionRepository,
            $this->mockInstrumentRepository,
            $this->mockInstrumentTypeRepository,
            $this->mockProposalRepository,
            $this->mockUserRepository
        ];
        /** @var MockObject $repository */
        foreach ($repositoriesNeedingFixtures as $mockRepo) {
            // The GuiController renders a template that expects each ElasticSearchTypeCollection it receives (which
            // are returned by the repositories' getById() methods) to return a display and machine name, but we don't
            // care what those names are for the purposes of our tests
            $mockElasticSearchTypeCollection = $this->createMock(ElasticSearchTypeCollection::class);
            $mockElasticSearchTypeCollection->expects($this->any())
                ->method('getTypeDisplayName')
                ->willReturn('dummyCollectionName');
            $mockElasticSearchTypeCollection->expects($this->any())
                ->method('getMachineName')
                ->willReturn('dummyMachineName');

            // If count() doesn't return a positive value the controller will error out
            $mockElasticSearchTypeCollection->expects($this->any())
                ->method('count')
                ->willReturn(1);

            // Since we are not testing the template's behavior, it is convenient (to save the trouble of mocking up
            // model instances for each repository) to have getInstances return an empty array.
            $mockElasticSearchTypeCollection->expects($this->any())
                ->method('getInstances')
                ->willReturn([]);

            $mockRepo->expects($this->once())
                ->method('getById')
                ->willReturn($mockElasticSearchTypeCollection);
        }

        $response = $this->controller->indexAction();
        $this->assertInstanceOf('\\Symfony\\Component\\HttpFoundation\\Response', $response);
    }

    /**
     * @throws \InvalidArgumentException if $className is not an instance of Repository
     * @param string $className The class name (without namespace) of the PacificaSearchBundle
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockRepository($className)
    {
        if (!is_subclass_of($className,Repository::class)) {
            throw new \InvalidArgumentException("Class $className is not an instance of " . Repository::class);
        }

        $mockRepo = $this->getMockBuilder($className)
            ->setConstructorArgs([ $this->mockSearchService, $this->mockRepositoryManager ])
            ->getMock();

        return $mockRepo;
    }
}