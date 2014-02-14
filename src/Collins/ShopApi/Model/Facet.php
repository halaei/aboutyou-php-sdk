<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class Facet
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * Constructor.
     *
     * @param object $jsonObject The facet data.
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }

    /**
     * Get the facet id.
     *
     * @return integer
     */
    public function getFacetId()
    {
        return $this->jsonObject->facet_id;
    }

    /**
     * Get the facet name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->jsonObject->name;
    }

    /**
     * Get the value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->jsonObject->value;
    }

    /**
     * Get the group id.
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->jsonObject->id;
    }

    /**
     * Get the group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->jsonObject->group_name;
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
        if (isset($this->jsonObject->options)) {
            foreach ($this->jsonObject->options as $option) {
                if ($option->key == $key) {
                    return $option->value;
                }
            }
        }
        return null;
    }
}