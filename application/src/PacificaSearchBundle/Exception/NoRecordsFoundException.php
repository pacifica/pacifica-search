<?php

namespace PacificaSearchBundle\Exception;

/**
 * Class NoRecordsFoundException
 *
 * Thrown when a Repository returns no records under circumstances in which finding no records indicates something
 * has gone horribly wrong
 */
class NoRecordsFoundException extends \RuntimeException
{

}
