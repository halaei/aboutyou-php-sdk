<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi;

interface CategoryManagerInterface
{

    /**
     * @param $id
     * @return ShopApi\Model\Category|null
     */
    public function getCategory($id);

    /**
     * @param array $ids
     * @return ShopApi\Model\Category[]
     */
    public function getCategories(array $ids);

}