<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Aboutyou\Common\Cache\CacheProvider;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\Category;

class DefaultCategoryManager implements CategoryManagerInterface
{
    const DEFAULT_CACHE_DURATION = 7200;

    /** @var Category[] */
    private $categories;

    /** @var integer[] */
    private $parentChildIds;

    /** @var Cache */
    private $cache;

    /**
     * @param string $appId  This must set, when you use more then one instances with different apps
     * @param CacheProvider $cache
     */
    public function __construct($appId = '', CacheProvider $cache = null)
    {
        if ($cache !== null) {
            $cache->setNamespace('AY:SDK:' . $appId);
        }
        $this->cache = $cache;

        $this->loadCachedCategories();
    }

    public function loadCachedCategories()
    {
        if ($this->cache) {
            $this->categories = $this->cache->fetch('categories') ?: null;
        }
    }

    public function cacheCategories()
    {
        if ($this->cache) {
            $this->cache->save('categories', $this->categories, self::DEFAULT_CACHE_DURATION);
        }
    }

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

        $this->cacheCategories();

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
                    $categories[$id] = $category;
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
        $categories = array_filter($this->categories, function ($category) use ($name, $activeOnly) {
            return (
                $category->getName() === $name
                && ($activeOnly === Category::ALL || $category->isActive())
            );
        });

        return $categories;
    }
}
