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

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->categories === null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryTree($activeOnly = Category::ACTIVE_ONLY)
    {
        return $this->getSubCategories(0, $activeOnly);
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
    public function getCategories(array $ids, $activeOnly = Category::ACTIVE_ONLY)
    {
        if (empty($this->categories)) {
            return array();
        }

        $categories = array();
        foreach ($ids as $id) {
            if (isset($this->categories[$id])) {
                $category = $this->categories[$id];
                if ($activeOnly === Category::ALL || $category->isActive()) {
                    $categories[] = $category;
                }
            }
        }

        return $categories;
    }

    public function getAllCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubCategories($id, $activeOnly = Category::ACTIVE_ONLY)
    {
        if (!isset($this->parentChildIds[$id])) {
            return array();
        }

        $ids = $this->parentChildIds[$id];

        return $this->getCategories($ids, $activeOnly);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstCategoryByName($name, $activeOnly = Category::ACTIVE_ONLY)
    {
        foreach ($this->categories as $category) {
            if ($category->getName() === $name && ($activeOnly === Category::ALL || $category->isActive())) {
                return $category;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByName($name, $activeOnly = Category::ACTIVE_ONLY)
    {
        return array_values(array_filter($this->categories, function ($category) use ($name, $activeOnly) {
            return ($category->getName() === $name && ($activeOnly === Category::ALL || $category->isActive()));
        }));
    }
}
