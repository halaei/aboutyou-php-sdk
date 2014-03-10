<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class Basket
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * @var BasketItem[]
     */
    protected $items = array();
    
    
    /**
     *
     * @var Product[]
     */
    protected $products = array();
    

    /**
     * Constructor.
     *
     * @param object $jsonObject The basket data.
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }
    /**
     * Get the total price.
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->jsonObject->total_price;
    }

    /**
     * Get the total net.
     *
     * @return integer
     */
    public function getTotalNet()
    {
        return $this->jsonObject->total_net;
    }

    /**
     * Get the total vat.
     *
     * @return integer
     */
    public function getTotalVat()
    {
        return $this->jsonObject->total_vat;
    }

    /**
     * Get the total amount of all items.
     *
     * @return integer
     */
    public function getTotalAmount()
    {
        return $this->jsonObject->amount_variants;
    }

    /**
     * Get the number of variants.
     *
     * @return integer
     */
    public function getTotalVariants()
    {
        return $this->jsonObject->total_variants;
    }

    /**
     * Get all basket items.
     *
     * @return BasketItem[]
     */
    public function getItems()
    {
        if(!$this->items) {
            foreach($this->jsonObject->order_lines as $orderLine) {
                if(isset($orderLine->set_items)) { // is it a set of variants?
                    $this->items[] = new BasketSet($orderLine, $this);
                }
                else { // or a variant?
                    $this->items[] = new BasketVariant($orderLine, $this);
                }
            }
        }
        
        return $this->items;
    }
    
    public function getProducts()
    {
        if(!$this->products) {
            foreach($this->jsonObject->products as $product) {
                $this->products[$product->id] = new Product($product);
            }
        }
        
        return $this->products;
    }
}