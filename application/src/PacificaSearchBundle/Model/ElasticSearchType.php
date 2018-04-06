<?php

namespace PacificaSearchBundle\Model;

/**
 * Class ElasticSearchType
 *
 * A base class for Model classes representing ElasticSearch records
 */
abstract class ElasticSearchType
{

    /** @var int */
    protected $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray()
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
    abstract public function getDisplayName();

    /**
     * Returns the string that represents the Type in the REST API
     * @return string
     */
    abstract public static function getMachineName();

    /**
     * Returns the string that represents the Type in the GUI
     * @return string
     */
    abstract public static function getTypeDisplayName();
}
