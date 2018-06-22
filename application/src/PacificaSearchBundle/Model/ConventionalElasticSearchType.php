<?php

namespace PacificaSearchBundle\Model;

/**
 * Class NamedElasticSearchType
 *
 * Implements the most common form of ElasticSearchType, which has a "name" field containing the type's display name,
 * and whose display and machine names can be derived from the type's class name
 */
abstract class ConventionalElasticSearchType extends ElasticSearchType
{
    /** @var string */
    protected $name;

    /**
     * The number of transactions associated with this object
     *
     * @var int
     */
    protected $transactionCount;

    /**
     * InstrumentType constructor.
     *
     * @param string $id
     * @param string $name
     * @param int $transactionCount
     */
    public function __construct(string $id, string $name, int $transactionCount)
    {
        $this->name = $name;
        $this->transactionCount = $transactionCount;
        parent::__construct($id);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName() : string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public static function getTypeDisplayName() : string
    {
        // The display name, by convention, is "Class Name" for class ClassName
        return preg_replace('/(?<!^)[A-Z]/', ' $0', static::getClassNameWithoutNamespace());
    }

    /**
     * @inheritdoc
     */
    public static function getMachineName() : string
    {
        // The machine name, by convention, is "class_name" for class "ClassName"
        $withUnderscores = preg_replace('/(?<!^)[A-Z]/', '_$0', static::getClassNameWithoutNamespace());
        return strtolower($withUnderscores);
    }

    /**
     * @return string
     */
    private static function getClassNameWithoutNamespace() : string
    {
        if ($pos = strrpos(static::class, '\\')) {
            return substr(static::class, $pos + 1);
        }
        return static::class;
    }
}
