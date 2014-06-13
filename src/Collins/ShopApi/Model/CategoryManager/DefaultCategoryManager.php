<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi;
use Collins\ShopApi\Factory\ResultFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DefaultCategoryManager implements CategoryManagerInterface
{
    protected $jsonObject;

    /** @var ResultFactoryInterface */
    private $factory;

    /** @var Category[] */
    private $categoryInstances;

    public function __construct(ResultFactoryInterface $factory) {
        $this->factory = $factory;

    }

    /**
     * @param $jsonObject
     * @private
     */
    public function setRawCategoryTree($jsonObject)
    {
        $this->jsonObject = $jsonObject;
        return $this;
    }

    public function isEmpty()
    {
        return empty($this->jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'collins.shop_api.category_tree.create_model.before' => array('onCategoryTreeFetched', 0),
        );
    }

    public function onCategoryTreeFetched(GenericEvent $event) {
        $this->jsonObject = $event->getSubject();
    }

    public function getCategoryTree($level = -1)
    {
        $jsonObjects = [];
        foreach($this->jsonObject->parent_child->{"0"} as $categoryId) {
            $jsonObjects[] = $this->jsonObject->ids->{$categoryId};
        }
        return $this->factory->createCategoryTree($jsonObjects);
    }

    /**
     * @param $id
     * @param bool $activeOnly
     * @return ShopApi\Model\Category|null
     */
    public function getCategory($id, $activeOnly=true) {
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

            $this->categoryInstances[$id] = $this->factory->createCategory($rawObject, $parentObject);
        }

        return($this->categoryInstances[$id]);
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