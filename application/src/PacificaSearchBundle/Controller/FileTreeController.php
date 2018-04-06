<?php

namespace PacificaSearchBundle\Controller;

use PacificaSearchBundle\Filter;
use PacificaSearchBundle\Model\Instrument;
use PacificaSearchBundle\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use PacificaSearchBundle\Repository\InstitutionRepository;
use PacificaSearchBundle\Repository\InstrumentRepository;
use PacificaSearchBundle\Repository\InstrumentTypeRepository;
use PacificaSearchBundle\Repository\ProposalRepository;
use PacificaSearchBundle\Repository\TransactionRepositoryInterface;
use PacificaSearchBundle\Repository\UserRepository;

/**
 * Class FileTreeController
 *
 * Generates JSON used to construct the file tree used by the GUI
 */
class FileTreeController extends BaseRestController
{
    // Number of Proposals to include in one page of the file tree
    const PAGE_SIZE = 30;

    /** @var FileRepository */
    protected $fileRepository;

    public function __construct(
        InstitutionRepository $institutionRepository,
        InstrumentRepository $instrumentRepository,
        InstrumentTypeRepository $instrumentTypeRepository,
        ProposalRepository $proposalRepository,
        UserRepository $userRepository,
        TransactionRepositoryInterface $transactionRepository,
        FileRepository $fileRepository
    ) {
        parent::__construct(
            $institutionRepository,
            $instrumentRepository,
            $instrumentTypeRepository,
            $proposalRepository,
            $userRepository,
            $transactionRepository
        );

        $this->fileRepository = $fileRepository;
    }

    /**
     * Gets the top level of the tree (everything excluding the files, which have to be lazy-loaded on a per-transaction
     * basis). Pagination is done at the level of Proposals, so we limit the number of Proposals returned but return all
     * children of any Proposals in the page.
     *
     * The response is formatted like this:
     * [
     *     {
     *         "title": "Proposal #31390",
     *         "key": "31390",
     *         "folder": true,
     *         "children": [
     *             {
     *                 "title": "TOF-SIMS 2007 (Instrument ID: 34073)",
     *                 "key": "34073",
     *                 "folder": true,
     *                 "children": [
     *                     {
     *                         "title": "Files Uploaded 2017-01-02 (Transaction 37778)",
     *                         "key": "37778",
     *                         "folder": true,
     *                         "lazy": true
     *                     }, ... ( More transactions )
     *                 ]
     *             }, ... ( More instruments )
     *         ]
     *     }, ... ( More proposals )
     * ]
     *  {
     *
     *
     * This gives a directory hierarchy like this:
     *
     * Root directory
     *   |-- Proposal #31390
     *     |-- TOF-SIMS 2007 (Instrument ID: 34073)
     *       |-- Files Uploaded 2017-01-02 (Transaction 37778)
     *         |-- (Contents of this folder are the actual files, which are lazy loaded)
     *
     *
     * @param int $pageNumber
     * @return Response
     */
    public function getPageAction($pageNumber) : Response
    {
        if ($pageNumber < 1 || intval($pageNumber) != $pageNumber) {
            return $this->handleView(View::create([]));
        }

        /** @var Filter $filter */
        // TODO: Instead of storing the filter in the session, pass it as a request variable
        $filter = $this->getSession()->get('filter');

        if (null === $filter || $filter->isEmpty()) {
            // Without a filter we do not show a file tree - the result set would be too large to handle in any case
            return $this->handleView(View::create([]));
        }

        // TODO: Obviously paginating the proposalIds at the query level would be better but there's not an obvious way
        // to add pagination support to getFilteredIds(), so until we can we just use array_slice() here.
        $proposalIds = $this->proposalRepository->getFilteredIds($filter);
        $proposalIds = array_slice($proposalIds, ($pageNumber-1) * self::PAGE_SIZE, self::PAGE_SIZE);

        // If there are no proposals on the requested page, return an empty set
        if (empty($proposalIds)) {
            return $this->handleView(View::create([]));
        }

        $response = [];
        foreach ($proposalIds as $proposalId) {
            $response[$proposalId] = [
                'title' => "Proposal #$proposalId",
                'key' => $proposalId,
                'folder' => true,
                'children' => []
            ];
        }

        $transactions = $this->transactionRepository->getAssocArrayByFilter($filter);
        $instrumentIds = [];
        foreach ($transactions as $transaction) {
            $instrumentId = $transaction['_source']['instrument'];
            $instrumentIds[$instrumentId] = $instrumentId;
        }

        $instrumentCollection = $this->instrumentRepository->getById($instrumentIds);
        $instrumentNames = [];
        foreach ($instrumentCollection->getInstances() as $instrument) {
            /** @var $instrument Instrument */
            $instrumentNames[$instrument->getId()] = $instrument->getDisplayName();
        }

        foreach ($transactions as $transaction) {
            $proposalId = $transaction['_source']['proposal'];
            $instrumentId = $transaction['_source']['instrument'];
            $transactionId = $transaction['_id'];

            // TODO: This outermost array_key_exists() check is necessary because we are artificially limiting the
            // transactions we handle to 1000 - see Respository::getIdsByTransactionIds(). We need to remove that
            // limitation, and once we have we can remove the check
            if (array_key_exists($proposalId, $response)) {
                if (!array_key_exists($instrumentId, $response[$proposalId]['children'])) {
                    $instrumentName = $instrumentNames[$instrumentId];
                    $response[$proposalId]['children'][$instrumentId] = [
                        'title' => "$instrumentName (Instrument ID: $instrumentId)",
                        'key' => $instrumentId,
                        'folder' => true,
                        'children' => []
                    ];
                }
            }

            $dateCreated = new \DateTime($transaction['_source']['created']);
            $dateFormatted = $dateCreated->format('Y-m-d');
            $response[$proposalId]['children'][$instrumentId]['children'][] = [
                'title' => "Files uploaded $dateFormatted (<a href='http://status.local/view/$transactionId'>Transaction $transactionId</a>)",
                'key' => $transactionId,
                'folder' => true,
                'lazy' => true
            ];

            $instrumentIdsToTransactionIds[$transaction['_source']['instrument']][] = $transaction['_id'];
        }

        // We use ID values above to make it easier to refer to specific records in code - for items that are meant to
        // be arrays rather than hashes in the produced JSON, we have to strip those out
        $response = array_values($response);
        foreach ($response as &$proposal) {
            $proposal['children'] = array_values($proposal['children']);
        }

        return $this->handleView(View::create($response));
    }

    /**
     * Fetches the contents of a single file folder for the purpose of lazy-loading those folders on demand.
     * The response is formatted like
     *
     * [
     *     {
     *     "fullpath": "<full path to file if a file, current fullpath otherwise>",
     *     "title": "<subdir name if in subdir, filename otherwise>",
     *     "key": "<file id if file, otherwise this key is absent>",
     *     "folder": <true if not a file, otherwise this key is absent>,
     *     "children": [
     *         ...next layer of folder structure until we hit a file or files
     *     ]
     *     }, ... ( More file definitions )
     * ]
     *
     * @param int $transactionId
     * @return Response
     */
    public function getTransactionFilesAction($transactionId) : Response
    {
        if ($transactionId < 1 || intval($transactionId) != $transactionId) {
            return $this->handleView(View::create([]));
        }

        $directories = [];
        $files = $this->fileRepository->getByTransactionId($transactionId);
        foreach ($files->getInstances() as $file) {
            $filePathParts = explode('/', $file->getDisplayName());
            $this->addToDirectoryStructure($directories, $filePathParts, $file->getId());
        }

        $response = $this->convertDirectoryStructureToResponseArray($directories);
        return $this->handleView(View::create($response));
    }

    private function addToDirectoryStructure(array &$directory, array &$nodes, $fileId)
    {
        $node = array_shift($nodes);
        if (strlen($node) === 0) { // Ignore nodes without a name
            $this->addToDirectoryStructure($directory, $nodes, $fileId);
        } elseif (count($nodes)) { // $node is a directory: recurse
            if (!array_key_exists($node, $directory)) {
                $directory[$node] = [];
            }
            $this->addToDirectoryStructure($directory[$node], $nodes, $fileId);
        } else { // $node is a file
            $directory[] = $node . "_*_ID_*_$fileId";
        }
    }

    private function convertDirectoryStructureToResponseArray($nodes, $path = "")
    {
        $responseArray = [];
        foreach ($nodes as $nodeName => $node) {
            $nodeResult = [];
            $isFolder = false;

            if (is_array($node)) { // This node is a directory: recurse
                $title = $nodeName;
                $isFolder = true;
                $children = $this->convertDirectoryStructureToResponseArray($node, ($path ? $path . '/' : '') . $nodeName);
            } else { // This node is a file
                list($fileName, $fileId) = explode('_*_ID_*_', $node);
                $title = $fileName;
            }

            $nodeResult['fullpath'] = $path . '/' . $title;
            $nodeResult['title'] = $title;
            if ($isFolder) {
                $nodeResult['folder'] = true;
                $nodeResult['children'] = $children;
            } else {
                $nodeResult['key'] = $fileId;
            }

            $responseArray[] = $nodeResult;
        }
        return $responseArray;
    }
}
