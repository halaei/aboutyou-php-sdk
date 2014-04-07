<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\ProductSearchResult\FacetCounts;
use Collins\ShopApi\Model\ProductSearchResult\PriceRange;
use Collins\ShopApi\Model\ProductSearchResult\SaleCounts;
use Collins\ShopApi;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductSearchResult
{
    /** @var Product[] */
    protected $products;

    /** @var string */
    protected $pageHash;

    /** @var integer */
    protected $productCount;

    /** @var SaleCounts */
    protected $saleCounts;

    /** @var PriceRange[] */
    protected $priceRanges;

    /** @var FacetCounts[] */
    protected $facets;

    /** @var Category[] */
    protected $categories = array();

    /**
     * @var array
     * @deprcated
     */
    protected $rawFacets;

    public function __construct($jsonObject, ModelFactoryInterface $factory)
    {
        $this->products = array();

        $event = new GenericEvent($this, func_get_args());
        ShopApi::getEventDispatcher()->dispatch("collins.shop_api.product_search_result.from_json.before", $event);
        $this->fromJson($jsonObject, $factory);
        ShopApi::getEventDispatcher()->dispatch("collins.shop_api.product_search_result.from_json.after", $event);
    }

    public function fromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $this->pageHash = $jsonObject->pageHash;
        $this->productCount = $jsonObject->product_count;
        $this->rawFacets = $jsonObject->facets;

        foreach ($jsonObject->products as $key => $jsonProduct) {
            $this->products[$key] = $factory->createProduct($jsonProduct);
        }

        $this->parseFacets($jsonObject->facets, $factory);
    }

    /**
     * @return string
     */
    public function getPageHash()
    {
        return $this->pageHash;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    protected function parseFacets($jsonObject, ModelFactoryInterface $factory)
    {
        if (isset($jsonObject->categories)) {
            $this->categories = $factory->createCategoriesFacets($jsonObject->categories);
            unset($jsonObject->categories);
        }
        if (isset($jsonObject->prices)) {
            $this->priceRanges = $factory->createPriceRanges($jsonObject->prices);
            unset($jsonObject->prices);
        }
        if (isset($jsonObject->sale)) {
            $this->saleCounts = $factory->createSaleFacet($jsonObject->sale);
            unset($jsonObject->sale);
        }

        $this->facets = $factory->createFacetsCounts($jsonObject);
        unset($jsonObject->facets);
    }


    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @return object
     */
    public function getRawFacets()
    {
        return $this->rawFacets;
    }

    /**
     * @return PriceRange[]
     */
    public function getPriceRanges()
    {
        return $this->priceRanges;
    }

    /**
     * Returns the min price in euro cent or null, if the price range was not requested/selected
     *
     * @return integer|null
     */
    public function getMinPrice()
    {
        if (empty($this->priceRanges)) return null;

        return $this->priceRanges[0]->getMin();
    }

    /**
     * Returns the max price in euro cent, if the price range was not requested/selected
     *
     * @return integer|null
     */
    public function getMaxPrice()
    {
        if (empty($this->priceRanges)) return null;

        foreach ($this->priceRanges as $priceRange) {
            if (!$priceRange->getMax()) break;
            $maxPrice = $priceRange->getMax();
        }

        return $maxPrice;
    }

    /**
     * @return SaleCounts
     */
    public function getSaleCounts()
    {
        return $this->saleCounts;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return Category[]
     */
    public function getCategoryTree()
    {
        $topLevelCategories = array();
        foreach ($this->categories as $category) {
            if ($category->getParent() === null) {
                $topLevelCategories[] = $category;
            }
        }

        return $topLevelCategories;
    }
}