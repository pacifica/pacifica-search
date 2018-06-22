<?php

namespace PacificaSearchBundle\Model;

/**
 * Class FilterItemCollection
 *
 * Represents a collection of filter items of a single type - this is basically a container for an array of
 * ElasticSearchType instances of the same type, which exists to abstract some of the logic of rendering those
 * collections out of the template.
 */
class ElasticSearchTypeCollection implements \Countable
{
    /**
     * @var ElasticSearchType[]
     */
    private $instances = [];

    /**
     * It is common for an ElasticSearchTypeCollection to contain only a subset (usually a page) of the results of a
     * search. In that case the creator of an instance of ElasticSearchTypeCollection has the option of setting this
     * property to indicate the total number of hits, including any that weren't included in the collection.
     *
     * @var int
     */
    private $totalHits;

    /**
     * @param ElasticSearchType[] $instances
     * @param int|null $totalHits If this instance only represents a subset (the intended use case being pagination) of
     *        a larger set, pass this to indicate the size of the full set.
     */
    public function __construct(array $instances = [], int $totalHits = null)
    {
        foreach ($instances as $instance) {
            $this->add($instance);
        }

        $this->totalHits = $totalHits;
    }

    /**
     * @param ElasticSearchType $item
     * @return ElasticSearchTypeCollection
     */
    public function add(ElasticSearchType $item) : ElasticSearchTypeCollection
    {
        // Enforce that all items in a collection have to be of the same class
        if (!empty($this->instances) && !($item instanceof $this->instances[0])) {
            throw new \InvalidArgumentException('All items in a FilterItemCollection must be of the same class');
        }

        $this->instances[] = $item;

        return $this;
    }

    /**
     * @return ElasticSearchType[]
     */
    public function getInstances() : array
    {
        return $this->instances;
    }

    /**
     * Reorders the wrapped instances to be alphabetical by display name
     * @return ElasticSearchTypeCollection
     */
    public function sortByDisplayName() : ElasticSearchTypeCollection
    {
        usort($this->instances, function ($a, $b) {
            /** @var $a ElasticSearchType */
            /** @var $b ElasticSearchType */
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });

        return $this;
    }

    /**
     * Returns the string that represents the contained Type in the GUI
     * @return string|NULL NULL if no items have been added
     */
    public function getTypeDisplayName() : ?string
    {
        if (empty($this->instances)) {
            return null;
        }

        return $this->instances[0]::getTypeDisplayName();
    }

    /**
     * Returns the string that represents the contained Type in the REST API
     * @return string|NULL NULL if no items have been added
     */
    public function getMachineName() : ?string
    {
        if (empty($this->instances)) {
            return null;
        }

        return $this->instances[0]::getMachineName();
    }

    public function count() : int
    {
        return count($this->instances);
    }
}
