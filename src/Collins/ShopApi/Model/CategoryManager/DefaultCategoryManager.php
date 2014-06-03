<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi;

class DefaultCategoryManager implements CategoryManagerInterface
{
    protected $categoryTree;

    private $shopApi;


    public function __construct(ShopApi $shopApi)
    {
        $this->shopApi = $shopApi;
    }

    public function setCategoryTree($categoryTree) {
        $this->categoryTree = $categoryTree;
    }

    public function getCategoryTree()
    {
        if(is_null($this->categoryTree)) {
            $this->categoryTree = $this->shopApi->fetchCategoryTree();
        }

        return($this->categoryTree);
    }

    /**
     * @param $id
     * @param bool $activeOnly
     * @return ShopApi\Model\Category|null
     */
    public function getCategory($id, $activeOnly=true) {
        return($this->getCategoryTree()->getCategory($id, $activeOnly));
    }

    /**
     * @param array $ids
     * @param bool $activeOnly
     * @return ShopApi\Model\Category[]
     */
    public function getCategories(array $ids, $activeOnly=true)
    {
        /**
         *
         */
        $result = array();

        foreach($ids as $id) {
            $result[$id] = $this->getCategory($id, $activeOnly);
        }

        return($result);
    }
}