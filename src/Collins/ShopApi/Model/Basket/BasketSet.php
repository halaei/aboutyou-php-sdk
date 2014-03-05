<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\ResultErrorTrait;

class BasketSet implements BasketItemInterface
{
    use ResultErrorTrait;
    use AddionalDataTrait;

    /** @var string */
    protected $id;

    /** @var BasketVariantItem[] */
    protected $items;

    /** @var ResultErrorTrait[] */
    protected $errors;

    public function __construct(\stdClass $jsonObject, ModelFactoryInterface $factory, $products)
    {
        $this->id = $jsonObject->id;
        if (isset($jsonObject->additional_data)) {
            $this->additionalData = $jsonObject->additional_data;
        }

        $this->parseErrorResult($jsonObject);

        foreach ($jsonObject->set_items as $index => $jsonItem) {
            $item = $factory->createBasketSetItem($jsonItem, $products);
            if ($item->hasErrors()) {
                $this->errors[$index] = $item;
            } else {
                $this->items[$index] = $item;
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function hasErrors()
    {
        return $this->errorCode || count($this->errors) > 0;
    }
} 