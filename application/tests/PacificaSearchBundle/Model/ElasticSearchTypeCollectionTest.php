<?php

namespace Pacifica\Search\Tests\Model;

require_once('MockElasticSearchType.php');

use Pacifica\Search\Tests\TestCase;
use PacificaSearchBundle\Model\ElasticSearchType;
use PacificaSearchBundle\Model\ElasticSearchTypeCollection;

class ElasticSearchTypeCollectionTest extends TestCase
{
    /**
     * This class is simple enough to do basic coverage tests in a single test
     * @test
     */
    public function basicFunctionality()
    {
        $letters = 'abcdefghijklmnopqrstuvwxwzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_ ';
        $instances = [];
        foreach (range(1, 25) as $instanceId) {
            // Generate random display names
            $displayName = '';
            foreach (range(1, 25) as $i) {
                $displayName .= substr($letters, rand(0, strlen($letters)), 1);
            }
            $instances[] = new MockElasticSearchType($instanceId, $displayName);
        }

        $collection = new ElasticSearchTypeCollection($instances);
        $emptyCollection = new ElasticSearchTypeCollection();

        $this->assertEquals($instances, $collection->getInstances());

        // The display name for a collection should be the same as for the type class contained, or NULL for empty collections
        $this->assertEquals(MockElasticSearchType::getTypeDisplayName(), $collection->getTypeDisplayName());
        $this->assertEquals(null, $emptyCollection->getTypeDisplayName());

        // The machine name for a collection should be the same as for the type class contained, or NULL for empty collections
        $this->assertEquals(MockElasticSearchType::getMachineName(), $collection->getMachineName());
        $this->assertEquals(null, $emptyCollection->getMachineName());

        $this->assertEquals(count($instances), count($collection));
        $this->assertEquals(0, count($emptyCollection));

        // It should not be possible to add an instance to a collection that is a different class than the other instances
        // in the collection
        $this->assertThrows(function () use ($collection) {
            /** @var ElasticSearchType $instanceOfDifferentClass */
            $instanceOfDifferentClass = $this->getMockForAbstractClass(ElasticSearchType::class, [ 'id' => 1 ], 'DifferentMockedElasticSearchType');
            $collection->add($instanceOfDifferentClass);
        },\InvalidArgumentException::class);

        // sortByDisplayName() should do what it says
        $collection->sortByDisplayName();

        /** @var ElasticSearchType $instance */
        /** @var ElasticSearchType $prevInstance */
        $prevInstance = null;
        foreach ($collection->getInstances() as $instance) {
            if (null !== $prevInstance) {
                // Each instance's name should be earlier in alphabetical order than the next one
                $this->assertLessThanOrEqual(0, strcmp($prevInstance->getDisplayName(), $instance->getDisplayName()));
            }

            $prevInstance = $instance;
        }
    }
}