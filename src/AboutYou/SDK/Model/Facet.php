<?php
namespace AboutYou\SDK\Model;

use AboutYou\SDK\ModelFactoryInterface;

/**
 *
 */
class Facet implements FacetUniqueKeyInterface, FacetGetGroupInterface
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $value;

    /** @var integer */
    protected $groupId;

    /** @var string */
    protected $groupName;

    /** @var array */
    protected $options;

    /**
     * @param $jsonObject
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject)
    {
        return new static(
            $jsonObject->facet_id,
            $jsonObject->name,
            isset($jsonObject->value) ? $jsonObject->value : null,
            $jsonObject->id,
            $jsonObject->group_name,
            isset($jsonObject->options) ? $jsonObject->options : null
        );
    }

    /**
     * @param integer $id
     * @param string  $name
     * @param string  $value
     * @param integer $groupId
     * @param string  $groupName
     * @param array   $options
     */
    public function __construct($id, $name, $value, $groupId, $groupName, array $options = null)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->value     = $value;
        $this->groupId   = $groupId;
        $this->groupName = $groupName;
        $this->options   = $options;
    }

    /**
     * Get the facet id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * a facet is unique with the combination about id and group id
     *
     * @return string
     */
    public function getUniqueKey()
    {
        return $this->getGroupId() . ':' . $this->getId();
    }

    /**
     * a facet is unique with the combination about id and group id
     *
     * @return string
     */
    public static function uniqueKey($groupId, $facetId)
    {
        return $groupId . ':' . $facetId;
    }

    /**
     * Get the facet name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the group id.
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Get the group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Get option value.
     *
     * @param string $key The option key.
     *
     * @return mixed
     */
    public function getOption($key)
    {
        if (isset($this->options)) {
            foreach ($this->options as $option) {
                if ($option->key == $key) {
                    return $option->value;
                }
            }
        }

        return null;
    }
}