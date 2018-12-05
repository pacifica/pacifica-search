<?php

namespace PacificaSearchBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class GuiController
{

    /** @string **/
    protected $elasticSearchHost;

    private $page_data = [
        'script_uris' => [
            'assets/js/lib/spinner/spin.min.js',
            'assets/js/lib/fancytree/dist/jquery.fancytree-all.js',
            'assets/js/lib/select2/dist/js/select2.js'
        ],
        'css_uris' => [
            'assets/js/lib/fancytree/dist/skin-lion/ui.fancytree.min.css',
            'assets/js/lib/select2/dist/css/select2.css',
            'assets/css/file_directory_styling.css',
            'assets/css/combined.css'
        ]
    ];

    public function __construct(
        $elasticSearchHost,
        EngineInterface $renderingEngine
    ) {
        $this->renderingEngine = $renderingEngine;
        $this->elasticSearchHost = $elasticSearchHost;
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
                'elastic_search_host' => $this->elasticSearchHost
            ]
        );

        return new Response($renderedContent);
    }
}
