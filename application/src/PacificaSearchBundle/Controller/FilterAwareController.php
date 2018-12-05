<?php

namespace PacificaSearchBundle\Controller;

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
use PacificaSearchBundle\Repository\UserRepository;

trait FilterAwareController
{
    /** @var InstitutionRepository */
    protected $institutionRepository;

    /** @var InstrumentRepository */
    protected $instrumentRepository;

    /** @var InstrumentTypeRepository */
    protected $instrumentTypeRepository;

    /** @var ProposalRepository */
    protected $proposalRepository;

    /** @var UserRepository */
    protected $userRepository;

    protected function initFilterableRepositories(
        InstitutionRepository $institutionRepository,
        InstrumentRepository $instrumentRepository,
        InstrumentTypeRepository $instrumentTypeRepository,
        ProposalRepository $proposalRepository,
        UserRepository $userRepository
    ) {
        $this->institutionRepository = $institutionRepository;
        $this->instrumentRepository = $instrumentRepository;
        $this->instrumentTypeRepository = $instrumentTypeRepository;
        $this->proposalRepository = $proposalRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Gets all Repository classes that implement the FilterRepository base class, which is the same as the set of
     * Repositories that contain items that can be filtered on in the GUI.
     *
     * @return Repository[] Keys are the machine names of the models managed by each repository
     */
    protected function getFilterableRepositories() : array
    {
        return [
            Institution::getMachineName()    => $this->institutionRepository,
            Instrument::getMachineName()     => $this->instrumentRepository,
            InstrumentType::getMachineName() => $this->instrumentTypeRepository,
            Proposal::getMachineName()       => $this->proposalRepository,
            User::getMachineName()           => $this->userRepository
        ];
    }

    /**
     * @return string[]
     */
    protected function getFilterableTypes() : array
    {
        return array_keys($this->getFilterableRepositories());
    }
}
