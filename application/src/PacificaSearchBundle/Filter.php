<?php

namespace PacificaSearchBundle;

use PacificaSearchBundle\Model\ElasticSearchType;
use PacificaSearchBundle\Model\Institution;
use PacificaSearchBundle\Model\Instrument;
use PacificaSearchBundle\Model\InstrumentType;
use PacificaSearchBundle\Model\Proposal;
use PacificaSearchBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Filter
 *
 * Represents the facets that have been selected by the user to restrict the set of further Filter options and Files
 * that are to be shown to the user
 */
class Filter
{
    /**
     * A free text that the user can enter to filter transactions
     *
     * @var string
     */
    private $text;

    /**
     * IDs of instrument types that are included in this filter
     * @var string[]
     */
    private $instrumentTypeIds = [];

    /**
     * IDs of instruments that are included in this filter
     * @var string[]
     */
    private $instrumentIds = [];

    /**
     * IDs of institutions that are included in this filter
     * @var string[]
     */
    private $institutionIds = [];

    /**
     * IDs of users that are included in this filter
     * @var string[]
     */
    private $userIds = [];

    /**
     * IDs of proposals that are included in this filter
     * @var string[]
     */
    private $proposalIds = [];

    /**
     * @param array[] $filterValues Keys are the getMachineName() values of ElasticSearchType classes, values are arrays
     * of IDs:
     *
     * [
     *     'instrument_type' => [ 102 ],
     *     'instrument' => [ 34150, 24151 ],
     *     ...
     * ]
     * @return Filter
     */
    public static function fromArray(array $filterValues) : Filter
    {
        $machineNamesToSetters = self::machineNamesToMethods('set');

        $filterKeys = array_keys($filterValues);
        $machineNames = array_keys($machineNamesToSetters);
        $unexpectedFilterValues = array_diff($filterKeys, $machineNames);
        if (count($unexpectedFilterValues)) {
            throw new \InvalidArgumentException('Filter values array contained unexpected key(s): ' . implode($unexpectedFilterValues));
        }

        $missingFilterValues = array_diff($machineNames, $filterKeys);
        if (count($missingFilterValues)) {
            throw new \InvalidArgumentException('Filter values array is missing expected key(s): ' . implode($missingFilterValues));
        }

        $filter = new static();
        foreach ($machineNamesToSetters as $machineName => $setter) {
            $filter->$setter($filterValues[$machineName]);
        }
        $filter->setText($filterValues['text']);
        return $filter;
    }

    /**
     * Pass a request whose body contains the JSON representation of a Filter instance, and this factory method
     * will return an equivalent instance of Filter
     *
     * @param Request $request
     * @return Filter
     */
    public static function fromRequest(Request $request) : Filter
    {
        $content = $request->getContent();
        if (strlen($content) === 0) {
            throw new \InvalidArgumentException('Request was empty. Expected a JSON-encoded representation of the Filter class.');
        }

        $filterValues = json_decode($content, true);
        if (null === $filterValues) {
            throw new \InvalidArgumentException('Request could not be parsed. Expected a JSON-encoded representation of the Filter class.');
        }
        $filter = Filter::fromArray($filterValues);
        return $filter;
    }

    /**
     * Inverse of fromArray(), returned array format is documented there.
     * @return array
     */
    public function toArray() : array
    {
        $machineNamesToGetters = self::machineNamesToMethods('get');

        $array = [];
        foreach ($machineNamesToGetters as $machineName => $getter) {
            $array[$machineName] = $this->$getter();
        }
        return $array;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        foreach ($this->toArray() as $vals) {
            if (!empty($vals)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text === null ? '' : $this->text;
    }

    /**
     * @param string $text
     * @return Filter
     */
    public function setText(string $text) : Filter
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getInstrumentTypeIds(): array
    {
        return $this->instrumentTypeIds;
    }

    /**
     * @param string[] $instrumentTypeIds
     * @return Filter
     */
    public function setInstrumentTypeIds(array $instrumentTypeIds): Filter
    {
        $this->instrumentTypeIds = $instrumentTypeIds;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getInstrumentIds(): array
    {
        return $this->instrumentIds;
    }

    /**
     * @param string[] $instrumentIds
     * @return Filter
     */
    public function setInstrumentIds(array $instrumentIds): Filter
    {
        $this->instrumentIds = $instrumentIds;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getInstitutionIds(): array
    {
        return $this->institutionIds;
    }

    /**
     * @param string[] $institutionIds
     * @return Filter
     */
    public function setInstitutionIds(array $institutionIds): Filter
    {
        $this->institutionIds = $institutionIds;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getUserIds(): array
    {
        return $this->userIds;
    }

    /**
     * @param string[] $userIds
     * @return Filter
     */
    public function setUserIds(array $userIds) : Filter
    {
        $this->userIds = $userIds;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getProposalIds(): array
    {
        return $this->proposalIds;
    }

    /**
     * @param string[] $proposalIds
     * @return Filter
     */
    public function setProposalIds(array $proposalIds) : Filter
    {
        $this->proposalIds = $proposalIds;
        return $this;
    }

    /**
     * Use this method like $filter->setIdsByType(Instrument::class, [1, 2, 3])
     * @param string $class
     * @param string[] $ids
     * @return $this
     */
    public function setIdsByType(string $class, array $ids) : Filter
    {
        if (!is_subclass_of($class, ElasticSearchType::class)) {
            throw new \InvalidArgumentException("$class is not a subclass of ElasticSearchType, setByClass() cannot be called on other classes");
        }

        $machineNamesToSetters = self::machineNamesToMethods('set');
        $setter = $machineNamesToSetters[$class::getMachineName()];
        $this->$setter($ids);
        return $this;
    }

    /**
     * Use this method like $filter->getIdsByType(Instrument::class)
     * @param string $class
     * @return string[]
     */
    public function getIdsByType(string $class) : array
    {
        if (!is_subclass_of($class, ElasticSearchType::class)) {
            throw new \InvalidArgumentException("$class is not a subclass of ElasticSearchType, getByClass() cannot be called on other classes");
        }

        $machineNamesToSetters = self::machineNamesToMethods('get');
        $getter = $machineNamesToSetters[$class::getMachineName()];

        return $this->$getter();
    }

    /**
     * Retrieves all of the setters or getters mapped to their associated type's machine name
     * @param $prefix
     * @return array
     */
    private static function machineNamesToMethods(string $prefix) : array
    {
        if (!in_array($prefix, [ 'set', 'get' ])) {
            throw new \InvalidArgumentException("Unexpected prefix '$prefix'");
        }

        $machineNamesToMethods = [
            InstrumentType::getMachineName() => "${prefix}InstrumentTypeIds",
            Instrument::getMachineName()     => "${prefix}InstrumentIds",
            Institution::getMachineName()    => "${prefix}InstitutionIds",
            User::getMachineName()           => "${prefix}UserIds",
            Proposal::getMachineName()       => "${prefix}ProposalIds",
            'text'                           => "${prefix}Text"
        ];
        return $machineNamesToMethods;
    }
}
