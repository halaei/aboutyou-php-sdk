<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi\Model\Category;

interface CategoryManagerInterface
{
    /**
     * @return boolean
     */
    public function isEmpty();

    /**
     * @param integer $id
     *
     * @return Category|null
     */
    public function getCategory($id);

    /**
     * @param integer[] $ids
     * @param boolean   $activeOnly
     *
     * @return Category[]
     */
    public function getCategories(array $ids, $activeOnly = Category::ACTIVE_ONLY);

    /**
     * @param integer $id
     * @param boolean $activeOnly
     *
     * @return Category[]
     */
    public function getSubCategories($id, $activeOnly = Category::ACTIVE_ONLY);

    /**
     * @param boolean $activeOnly
     *
     * @return Category[]
     */
    public function getCategoryTree($activeOnly = Category::ACTIVE_ONLY);

    /**
     * @return Category[]
     */
    public function getAllCategories();

    /**
     * @expimental
     *
     * @param string $name
     *
     * @return Category
     */
    public function getFirstCategoryByName($name, $activeOnly = Category::ACTIVE_ONLY);

    /**
     * @expimental
     *
     * @param string $name
     *
     * @return Category[]
     */
    public function getCategoriesByName($name, $activeOnly = Category::ACTIVE_ONLY);
}