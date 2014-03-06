<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Product;

class ProductFacetsTest extends AbstractShopApiTest
{
    /** @var Product */
    private $product;

    /** @var ShopApi */
    private $shopApi;

    public function setup()
    {
        $this->shopApi = $this->getShopApiWithResultFile('facets-for-product.json');

        $json = $this->getJsonObjectFromFile('product/product-with-attributes.json');
        $this->product = new ShopApi\Model\Product($json);
    }

    public function testGetBrandWorkaround()
    {
        $json = $this->getJsonObjectFromFile('product/product-257770.json');
        $product = new ShopApi\Model\Product($json);
        $brand = $product->getBrand();

        $this->assertNotNull($brand);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $brand);
        $this->assertEquals(0, $brand->getGroupId());
        $this->assertEquals(596, $brand->getId());
        $this->assertEquals('MARC O`POLO', $brand->getName());
        $this->assertEquals('brand', $brand->getGroupName());
    }

    public function testGetBrand()
    {
        $brand = $this->product->getBrand();

        $this->assertNotNull($brand);

        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $brand);
        $this->assertEquals(0, $brand->getGroupId());
        $this->assertEquals(264, $brand->getId());
        $this->assertEquals('TOM TAILOR', $brand->getName());
        $this->assertEquals('brand', $brand->getGroupName());
    }

    public function testGetFacetGroupSet()
    {
        $attributes = $this->product->getFacetGroupSet();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroupSet', $attributes);

        $groups = $attributes->getGroups();
        $this->assertCount(4, $groups);

        $brands = $groups[ShopApi\Constants::FACET_BRAND];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $brands);
        $this->assertEquals(0, $brands->getId());
        $this->assertEquals('brand', $brands->getName());

        $facets = $brands->getFacets(); // save in new variable because only variables should be passed as reference for reset
        $attribute = reset($facets);

        $this->assertEquals($attribute, $this->product->getBrand());
        $this->assertEquals(0, $attribute->getGroupId());
        $this->assertEquals('brand', $attribute->getGroupName());
        $this->assertEquals(264, $attribute->getId());
        $this->assertEquals('TOM TAILOR', $attribute->getName());

        $color = $groups[ShopApi\Constants::FACET_COLOR];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $color);
        $this->assertEquals(1, $color->getId());
        $this->assertEquals('color', $color->getName());
    }

    public function testGetGroupFacets()
    {
        $colors = $this->product->getGroupFacets(ShopApi\Constants::FACET_COLOR);
        $this->assertNotNull($colors);
        $this->assertInternalType('array', $colors);
        $color = $colors[12];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $color);
        $this->assertEquals(12, $color->getId());
        $this->assertEquals('Grau', $color->getName());
        $this->assertEquals('grau', $color->getValue());
        $this->assertEquals('color', $color->getGroupName());
    }

    public function testGetFacetGroups()
    {
        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = new ShopApi\Model\Product($json);

        $facetGroups = $product->getFacetGroups(206);
        $this->assertCount(5, $facetGroups);
        foreach ($facetGroups as $group) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $group);
            $this->assertEquals(206, $group->getId());
        }
    }

    public function testGetVariantByFacets()
    {
        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = new ShopApi\Model\Product($json);

        $facetGroupSet = new ShopApi\Model\FacetGroupSet([206 => [2402]]);
        $variant = $product->getVariantByFacets($facetGroupSet);
        $this->assertNull($variant);

        $variants = $product->getVariants();
        foreach ($variants as $expected) {
            $variant = $product->getVariantByFacets($expected->getFacetGroupSet());
            $this->assertEquals($expected, $variant);
        }
    }

    public function testGetVariantsByFacetId()
    {
        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = new ShopApi\Model\Product($json);

        $facet = new ShopApi\Model\Facet(2402, '', '', 206, '');
        $variants = $product->getVariantsByFacetId($facet->getId(), $facet->getGroupId());
        $this->assertCount(1, $variants);

        $brand = $product->getBrand();
        $variants = $product->getVariantsByFacetId($brand->getId(), $brand->getGroupId());
        $this->assertCount(5, $variants);

    }


    /*
    variante 1: rot,      M,  baumwolle
    variante 2: rot,      L,  baumwolle
    variante 3: rot,      XL, baumwolle
    variante 4: rot,      XL, metall
    variante 5: blau,     L,  baumwolle
    variante 6: rot/gelb, M,  baumwolle

    wenn []         =>   [rot,blau,gelb], [M,L,XL], [metall,baumwolle]

    wenn XL         =>   [rot],      [metall,baumwolle]
    wenn L          =>   [rot,blau], [baumwolle]
    wenn M          =>   [rot,gelb], [baumwolle]

    wenn rot        =>   [M,L,XL],   [metall,baumwolle]

    wenn rot,XL     =>   [metall,baumwolle]
    wenn rot,L      =>   [baumwolle]
    wenn blau,XL    =>   []
    */
    /**
     * @param $ids
     * @param $expectedValues
     * @dataProvider selectableFacetGroupsProvider
     */
    public function testGetSelectableFacetGroups($ids, $expectedValues)
    {
        $this->getShopApiWithResultFile('facets-for-product-variant-facets.json');

        $json = $this->getJsonObjectFromFile('product/product-variant-facets.json');
        $product = new ShopApi\Model\Product($json);


        $facetGroupSet = new ShopApi\Model\FacetGroupSet($ids);
        $groups = $product->getSelectableFacetGroups($facetGroupSet);
        $this->assertCount(count($expectedValues), $groups);
        foreach ($expectedValues as $index => $expected) {
            $this->assertEquals($expected, $groups[$index]->getUniqueKey());
        }
    }

    public function selectableFacetGroupsProvider()
    {
        // array of [<ids array>, <expected group keys array>]
        return [
            // wenn []         =>       [rot,blau,gelb], [M,L,XL], [metall,baumwolle]
            [[],                        ['0:264', '1:1001,1002,1003', '2:2001,2002,2003', '3:3001,3002']],

            // wenn XL         =>       [rot],      [metall,baumwolle]
            [["2"=>[2003]],             ['0:264', '1:1001', '3:3001,3002']],
            // wenn L          =>       [rot,blau], [baumwolle]
            [["2"=>[2002]],             ['0:264', '1:1001,1002', '3:3001']],
            // wenn M          =>       [rot,gelb], [baumwolle]
            [["2"=>[2001]],             ['0:264', '1:1001,1003', '3:3001']],

            // wenn rot        =>       [M,L,XL],   [metall,baumwolle]
            [["1"=>[1001]],             ['0:264', '2:2001,2002,2003', '3:3001,3002']],

            // wenn rot,XL     =>       [metall,baumwolle]
            [["1"=>[1001],"2"=>[2003]], ['0:264', '3:3001,3002']],
            // wenn rot,L      =>       [baumwolle]
            [["1"=>[1001],"2"=>[2002]], ['0:264', '3:3001']],
            // wenn blau,XL    =>       []
            [["1"=>[1002],"2"=>[2003]], []],
        ];
    }
}
