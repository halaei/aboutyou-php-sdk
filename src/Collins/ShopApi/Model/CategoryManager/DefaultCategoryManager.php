<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\Category;

class DefaultCategoryManager implements CategoryManagerInterface
{
    /** @var Category[] */
    private $categories;

    /** @var integer[] */
    private $parentChildIds;

    /**
     * @param \stdObject $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return $this
     */
    public function parseJson($jsonObject, ModelFactoryInterface $factory)
    {
        $this->categories = array();
        // this hack converts the array keys to integers, otherwise $this->parentChildIds[$id] fails
        $this->parentChildIds = json_decode(json_encode($jsonObject->parent_child), true);

        foreach ($jsonObject->ids as $id => $jsonCategory) {
            $this->categories[$id] = $factory->createCategory($jsonCategory, $this);
        }

        return $this;
    }

    public function isEmpty()
    {
        return $this->categories === null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryTree()
    {
        return $this->getSubCategories(0);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory($id)
    {
        if (!isset($this->categories[$id])) {
            return null;
        }

        return $this->categories[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories(array $ids)
    {
        if (empty($this->categories)) {
            return array();
        }

        $categories = array();
        foreach ($ids as $id) {
            if (isset($this->categories[$id])) {
                $categories[] = $this->categories[$id];
            }
        }

        return $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubCategories($id)
    {
        if (!isset($this->parentChildIds[$id])) {
            return array();
        }

        $ids = $this->parentChildIds[$id];

        return $this->getCategories($ids);
    }
}
