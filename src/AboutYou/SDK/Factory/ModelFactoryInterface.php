<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Factory;

use AboutYou\SDK\Model\Product;

interface ModelFactoryInterface extends ResultFactoryInterface
{
    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketItem
     */
    public function createBasketItem(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketSet
     */
    public function createBasketSet(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketSetItem
     */
    public function createBasketSetItem(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     *
     * @return \AboutYou\SDK\Model\Category
     */
    public function createCategory(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \AboutYou\SDK\Model\Facet
     */
    public function createFacet(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \AboutYou\SDK\Model\Image
     */
    public function createImage(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \AboutYou\SDK\Model\Product
     */
    public function createProduct(\stdClass $json);

    /**
     * @param \stdClass $json
     * @param \AboutYou\SDK\Model\Product $product
     *
     * @return \AboutYou\SDK\Model\Variant
     */
    public function createVariant(\stdClass $json, Product $product);

    /***************************************+
     * ProductSearchResult Facets
     +++++++++++++++++++++++++++++++++++++++++*/

    /**
     * @param \stdClass $jsonObject
     *
     * @return \AboutYou\SDK\Model\ProductSearchResult\PriceRange[]
     */
    public function createPriceRanges(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return \AboutYou\SDK\Model\ProductSearchResult\FacetCounts[]
     */
    public function createFacetsCounts(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return \AboutYou\SDK\Model\ProductSearchResult\SaleCounts
     */
    public function createSaleFacet(\stdClass $jsonObject);

    /**
     * @param \stdClass[] $jsonObject
     *
     * @return \AboutYou\SDK\Model\ProductSearchResult\
     */
    public function createCategoriesFacets(array $jsonObject);
}
