<?php

namespace Pacifica\Search\Tests\Model;

use Pacifica\Search\Tests\TestCase;
use PacificaSearchBundle\Model\ElasticSearchType;

class ElasticSearchTypeTest extends TestCase
{
    /**
     * This class is extremely simple so we can test all of its functions in a single simple function
     * @test
     */
    public function basicFunctionality()
    {
        $stubId = 1;
        $stubName = 'myDisplayName';

        /** @var ElasticSearchType $stub */
        $stub = $this->getMockForAbstractClass(
            ElasticSearchType::class,
            [ $stubId ]
        );

        $this->assertEquals($stubId, $stub->getId());

        // ElasticSearchType::toArray() returns the ID and whatever is returned by the class's concrete implementation
        // of getDisplayName(), which we have to mock here since it's an abstract method
        $stub->expects($this->once())->method('getDisplayName')->willReturn($stubName);
        $this->assertEquals([ 'id' => $stubId, 'name' => $stubName ], $stub->toArray());
    }
}