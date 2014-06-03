<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

class CategoryTree implements \IteratorAggregate, \Countable
{
    /** @var StdClass[] */
    private $jsonObject;

    /** @var Category[] */
    private $categoryInstances;

    /** @var  Category[] */
    private $activeCategoryInstances;

    /** @var ModelFactoryInterface */
    private $factoryInstance;




    /**
     * @param array $jsonArray
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson($jsonArray, ModelFactoryInterface $factory)
    {
        $categoryTree = new static();
        $categoryTree->jsonObject = $jsonArray;
        $categoryTree->categoryInstances = array();
        $categoryTree->factoryInstance = $factory;
        return $categoryTree;
    }

    protected function __construct()
    {

    }

    /**
     * @param bool $activeOnly if true, then only active categories will returned, otherwise all categories
     *
     * @return array|Category[]
     */
    public function getCategories($activeOnly = true)
    {
        if(!is_null($this->activeCategoryInstances)) {
            return($this->activeCategoryInstances);
        }

        $result = array();

        if(!isset($this->jsonObject->parent_child) || !isset($this->jsonObject->parent_child->{"0"})) {
            return($result);
        }

        foreach($this->jsonObject->parent_child->{"0"} as $categoryId) {
            $result[] = $this->getCategory($categoryId, $activeOnly);
        }

        $result = array_filter($result);

        if($activeOnly) {
            $this->activeCategoryInstances = $result;
        }

        return($result);
    }

    public function getCategory($id, $activeOnly=true)
    {
        if(!isset($this->jsonObject->ids->{$id})) {
            return null;
        }

        $rawObject = $this->jsonObject->ids->{$id};

        if($activeOnly && $rawObject->active===false) {
            return(null);
        }


        if(!isset($this->categoryInstances[$id])) {
            if(!empty($rawObject->parent) &&
                isset($this->categoryInstances[$rawObject->parent])) {
                $parentObject = $this->categoryInstances[$rawObject->parent];
            } else {
                $parentObject = null;
            }

            $this->categoryInstances[$id] = $this->factoryInstance->createCategory($rawObject, $parentObject);
        }

        return($this->categoryInstances[$id]);
    }

    /**
     * allows foreach iteration on active top categories
     *
     * {@inheritdoc}
     *
     * @return Iterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->getCategories());
    }

    /**
     * Count of the sub categories
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getCategories());
    }
}