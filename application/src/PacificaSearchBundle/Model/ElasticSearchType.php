<?php

namespace PacificaSearchBundle\Model;

/**
 * Class ElasticSearchType
 *
 * A base class for Model classes representing ElasticSearch records
 */
abstract class ElasticSearchType
{
    /** @var string */
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function toArray() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getDisplayName()
        ];
    }

    /**
     * Returns a string that can represent the Type instance in the GUI
     * @return string
     */
    abstract public function getDisplayName() : string;

    /**
     * Returns the string that represents the Type in the REST API
     * @return string
     */
    abstract public static function getMachineName() : string;

    /**
     * Returns the string that represents the Type in the GUI
     * @return string
     */
    abstract public static function getTypeDisplayName() : string;
}
