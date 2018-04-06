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
     * InstrumentType constructor.
     * @param int $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->name = $name;
        parent::__construct($id);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public static function getTypeDisplayName()
    {
        // The display name, by convention, is "Class Name" for class ClassName
        return preg_replace('/(?<!^)[A-Z]/', ' $0', static::getClassNameWithoutNamespace());
    }

    /**
     * @inheritdoc
     */
    public static function getMachineName()
    {
        // The machine name, by convention, is "class_name" for class "ClassName"
        $withUnderscores = preg_replace('/(?<!^)[A-Z]/', '_$0', static::getClassNameWithoutNamespace());
        return strtolower($withUnderscores);
    }

    /**
     * @return string
     */
    private static function getClassNameWithoutNamespace()
    {
        if ($pos = strrpos(static::class, '\\')) {
            return substr(static::class, $pos + 1);
        }
        return static::class;
    }
}
