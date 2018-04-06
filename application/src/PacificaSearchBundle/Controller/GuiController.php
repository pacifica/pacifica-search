<?php

namespace PacificaSearchBundle\Controller;

use PacificaSearchBundle\Exception\NoRecordsFoundException;
use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;
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

    private $page_data = [];

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

        $this->page_data['script_uris'] = array(
            'js/lib/spinner/spin.min.js',
            'js/lib/fancytree/dist/jquery.fancytree-all.js',
            'js/lib/select2/dist/js/select2.js'
        );
        $this->page_data['css_uris'] = array(
            'js/lib/fancytree/dist/skin-lion/ui.fancytree.min.css',
            'js/lib/select2/dist/css/select2.css',
            'css/file_directory_styling.css',
            'css/combined.css'
        );
    }

    /**
     * Renders the GUI of the Pacifica Search application
     * @return Response
     */
    public function indexAction() : Response
    {
        $emptyFilter = new Filter();

        /** @var ElasticSearchTypeCollection[] $filters */
        $filters = array_map(function (Repository $repository) use ($emptyFilter) {
            $instances = $repository->getFilteredPage($emptyFilter, 1);

            // If a repo returns an empty set then something has gone wrong
            if (!count($instances)) {
                $repositoryClass = get_class($repository);
                throw new NoRecordsFoundException(
                    "No records found for $repositoryClass, this is probably an error in your Elastic Search "
                    . "configuration or the corresponding type in your Elastic Search database is not populated"
                );
            }

            return $instances;
        }, $this->getFilterableRepositories());

        $renderedContent = $this->renderingEngine->render(
            'PacificaSearchBundle::search.html.twig',
            [
                'filters' => $filters,
                'page_data' => $this->page_data,
                'version_information' => $this->getVersionInformation()
            ]
        );

        return new Response($renderedContent);
    }

    /**
     * Returns information about the current version, such as the latest Git hash and
     * commit date. Be careful about what information is included here - the value
     * is publicly visible.
     *
     * @return string
     */
    protected function getVersionInformation()
    {
        $hash = trim(exec('git log --pretty="%H" -n1 HEAD'));
        $commitDate = trim(exec('git log -n1 --pretty=%ci HEAD'));

        return $commitDate . ' - ' . $hash;
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
