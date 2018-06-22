<?php

namespace PacificaSearchBundle\Controller;

use PacificaSearchBundle\Exception\NoRecordsFoundException;
use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;
use PacificaSearchBundle\Model\Institution;
use PacificaSearchBundle\Model\Instrument;
use PacificaSearchBundle\Model\InstrumentType;
use PacificaSearchBundle\Model\Proposal;
use PacificaSearchBundle\Model\User;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\Repository;
use PacificaSearchBundle\Repository\TransactionRepository;
use PacificaSearchBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class GuiController
{
    use FilterAwareController;

    /** @var TransactionRepository */
    protected $transactionRepository;

    /** @var EngineInterface */
    protected $renderingEngine;

    private $page_data = [
        'script_uris' => [
            'js/lib/spinner/spin.min.js',
            'js/lib/fancytree/dist/jquery.fancytree-all.js',
            'js/lib/select2/dist/js/select2.js'
        ],
        'css_uris' => [
            'js/lib/fancytree/dist/skin-lion/ui.fancytree.min.css',
            'js/lib/select2/dist/css/select2.css',
            'css/file_directory_styling.css',
            'css/combined.css'
        ]
    ];

    public function __construct(
        InstitutionRepository $institutionRepository,
        InstrumentRepository $instrumentRepository,
        InstrumentTypeRepository $instrumentTypeRepository,
        ProposalRepository $proposalRepository,
        UserRepository $userRepository,
        TransactionRepository $transactionRepository,
        EngineInterface $renderingEngine
    ) {
        $this->initFilterableRepositories(
            $institutionRepository,
            $instrumentRepository,
            $instrumentTypeRepository,
            $proposalRepository,
            $userRepository
        );

        $this->transactionRepository = $transactionRepository;
        $this->renderingEngine = $renderingEngine;
    }

    /**
     * Renders the GUI of the Pacifica Search application
     * @return Response
     */
    public function indexAction() : Response
    {
        $renderedContent = $this->renderingEngine->render(
            'PacificaSearchBundle::search.html.twig',
            [
                'page_data' => $this->page_data,
                'filter_types' => [
                    Institution::getMachineName()    => Institution::getTypeDisplayName(),
                    Instrument::getMachineName()     => Instrument::getTypeDisplayName(),
                    InstrumentType::getMachineName() => InstrumentType::getTypeDisplayName(),
                    Proposal::getMachineName()       => Proposal::getTypeDisplayName(),
                    User::getMachineName()           => User::getTypeDisplayName()
                ]
            ]
        );

        return new Response($renderedContent);
    }

    /**
     * Gets all Repository classes that implement the FilterRepository base class, which is the same as the set of
     * Repositories that contain items that can be filtered on in the GUI.
     *
     * @return Repository[]
     */
    protected function getFilterableRepositories() : array
    {
        return [
            $this->institutionRepository,
            $this->instrumentRepository,
            $this->instrumentTypeRepository,
            $this->proposalRepository,
            $this->userRepository
        ];
    }
}
