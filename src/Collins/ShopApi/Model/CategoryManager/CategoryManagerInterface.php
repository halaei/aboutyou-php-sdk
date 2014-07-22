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
     * @param integer $id
     *
     * @return Category|null
     */
    public function getCategory($id);

    /**
     * @param integer[] $ids
     *
     * @return Category[]
     */
    public function getCategories(array $ids);

    /**
     * @param integer $id
     *
     * @return Category[]
     */
    public function getSubCategories($id);

    /**
     * @return Category[]
     */
    public function getCategoryTree();
}