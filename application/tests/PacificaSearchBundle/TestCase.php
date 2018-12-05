<?php

namespace Pacifica\Search\Tests;

class TestCase extends \Symfony\Bundle\FrameworkBundle\Tests\TestCase
{
    /**
     * Assert that an Exception is thrown when $f is executed
     * @param callable $f
     * @param string $expectedExceptionClass
     */
    protected function assertThrows(callable $f, $expectedExceptionClass = '\\Exception')
    {
        try {
            $f();
        } catch (\Exception $e) {
            $this->assertInstanceOf($expectedExceptionClass, $e);
            return;
        }

        $this->fail("Expected an Exception of class $expectedExceptionClass to be thrown, but no Exception was thrown");
    }
}