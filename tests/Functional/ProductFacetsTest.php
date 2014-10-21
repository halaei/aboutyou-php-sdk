<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Constants;
use AboutYou\SDK\Factory\DefaultModelFactory;
use AboutYou\SDK\Model\CategoryManager\DefaultCategoryManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProductFacetsTest extends AbstractShopApiTest
{
    /**
     * @param string $facetsFile
     *
     * @return \AboutYou\SDK\Factory\DefaultModelFactory
     */
    public function getFactory($facetsFile)
    {
        $facetManager = $this->getStaticFacetManagerFromFile($facetsFile);
        $factory = new DefaultModelFactory(new \AY('id', 'token'), $facetManager, new DefaultCategoryManager(), new EventDispatcher());

        return $factory;
    }

    public function getProduct($filename = 'product/product-with-attributes.json', $facetsFile = 'facet-for-product.json')
    {
        $json = $this->getJsonObjectFromFile($filename);
        $product = $this->getFactory($facetsFile)->createSingleProduct($json);

        return $product;
    }

    public function testGetBrandWorkaround()
    {
        $product = $this->getProduct('product/product-257770.json');
        $brand = $product->getBrand();

        $this->assertNotNull($brand);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
        $this->assertEquals(0, $brand->getGroupId());
        $this->assertEquals(596, $brand->getId());
        $this->assertEquals('MARC O`POLO', $brand->getName());
        $this->assertEquals('brand', $brand->getGroupName());
    }

    public function testGetBrand()
    {
        $brand = $this->getProduct()->getBrand();

        $this->assertNotNull($brand);

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
        $this->assertEquals(0, $brand->getGroupId());
        $this->assertEquals(264, $brand->getId());
        $this->assertEquals('TOM TAILOR', $brand->getName());
        $this->assertEquals('brand', $brand->getGroupName());
    }

    public function testGetFacetGroupSet()
    {
        $attributes = $this->getProduct()->getFacetGroupSet();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetGroupSet', $attributes);

        $groups = $attributes->getGroups();
        $this->assertCount(4, $groups);

        $brands = $groups[Constants::FACET_BRAND];
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetGroup', $brands);
        $this->assertEquals(0, $brands->getId());
        $this->assertEquals('brand', $brands->getName());

        $facets = $brands->getFacets(); // save in new variable because only variables should be passed as reference for reset
        $attribute = reset($facets);

        $this->assertEquals($attribute, $this->getProduct()->getBrand());
        $this->assertEquals(0, $attribute->getGroupId());
        $this->assertEquals('brand', $attribute->getGroupName());
        $this->assertEquals(264, $attribute->getId());
        $this->assertEquals('TOM TAILOR', $attribute->getName());

        $color = $groups[Constants::FACET_COLOR];
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetGroup', $color);
        $this->assertEquals(1, $color->getId());
        $this->assertEquals('color', $color->getName());
    }

    public function testGetGroupFacets()
    {
        $colors = $this->getProduct()->getGroupFacets(Constants::FACET_COLOR);
        $this->assertNotNull($colors);
        $this->assertInternalType('array', $colors);
        $color = $colors[12];
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $color);
        $this->assertEquals(12, $color->getId());
        $this->assertEquals('Grau', $color->getName());
        $this->assertEquals('grau', $color->getValue());
        $this->assertEquals('color', $color->getGroupName());
    }

    public function testGetFacetGroups()
    {
        $product = $this->getProduct('product/product-full.json');

        $facetGroups = $product->getFacetGroups(206);
        $this->assertCount(6, $facetGroups);
        foreach ($facetGroups as $group) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetGroup', $group);
            $this->assertEquals(206, $group->getId());
        }
    }

    public function testGetVariantByFacets()
    {
        $product = $this->getProduct('product/product-full.json');

        $facetGroupSet = new \AboutYou\SDK\Model\FacetGroupSet([206 => [2402]]);
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
        $product = $this->getProduct('product/product-full.json');

        $facet = new \AboutYou\SDK\Model\Facet(2402, '', '', 206, '');
        $variants = $product->getVariantsByFacetId($facet->getId(), $facet->getGroupId());
        $this->assertCount(1, $variants);

        $brand = $product->getBrand();
        $variants = $product->getVariantsByFacetId($brand->getId(), $brand->getGroupId());
        $this->assertCount(7, $variants);

    }


    /*
    variante 1: rot,      M,  baumwolle
    variante 2: rot,      L,  baumwolle
    variante 3: rot,      XL, baumwolle
    variante 4: rot,      XL, metall
    variante 5: blau,     L,  baumwolle
    variante 6: rot/gelb, M,  baumwolle

    wenn []         =>   [rot,blau,rot/gelb], [M,L,XL], [metall,baumwolle]

    wenn XL         =>   [rot], [M,L,XL], [metall,baumwolle]

    wenn L          =>   [rot,blau], [M,L,XL], [baumwolle]
    wenn M          =>   [rot,rot/gelb], [M,L,XL], [baumwolle]

    wenn rot        =>   [rot,blau,rot/gelb], [M,L,XL],   [metall,baumwolle]

    wenn rot,XL     =>   [rot], [M,L,XL], [metall,baumwolle]
    wenn rot,L      =>   [rot,blau], [M,L,XL], [baumwolle]
    wenn blau,XL    =>   [rot], [L]

    wenn gelb,M     =>   [rot,rot/gelb]
    wenn rot/gelb,M =>   [rot,rot/gelb], [M], [baumwolle]
    wenn rot,M      =>   [rot/gelb,rot], [M,L,XL], [baumwolle]
    */
    /**
     * @param $ids
     * @param $expectedValues
     * @dataProvider selectableFacetGroupsProvider
     */
    public function testGetSelectableFacetGroups($ids, $expectedValues)
    {
        $product = $this->getProduct('product/product-variant-facets.json', 'facets-for-product-variant-facets.json');


        $facetGroupSet = new \AboutYou\SDK\Model\FacetGroupSet($ids);
        $groups = $product->getSelectableFacetGroups($facetGroupSet);
        $this->assertCount(count($expectedValues), $groups);

        foreach ($expectedValues as $index => $expected) {
            $keys = array_keys($groups[$index]);
            
            $this->assertEquals($expected, $keys);
        }
    }

    public function selectableFacetGroupsProvider()
    {
        // array of [<ids array>, <expected group keys array>]
        return [
            // wenn []         =>   [rot,blau,rot/gelb], [M,L,XL], [metall,baumwolle]
            [[],                        [['0:264'], ['1:1001','1:1002','1:1001,1003'], ['2:2001','2:2002','2:2003'], ['3:3001','3:3002']]],

            // wenn XL         =>   [[rot], [M,L,XL], [metall,baumwolle]
            [['2'=>[2003]],             [['0:264'], ['1:1001'], ['2:2001','2:2002','2:2003'], ['3:3001','3:3002']]],

            // wenn L          =>   [rot,blau], [M,L,XL], [baumwolle]
            [['2'=>[2002]],             [['0:264'], ['1:1001','1:1002'], ['2:2001','2:2002','2:2003'], ['3:3001']]],
            // wenn M          =>   [rot,gelb], [M,L,XL], [baumwolle]
            [['2'=>[2001]],             [['0:264'], ['1:1001','1:1001,1003'], ['2:2001','2:2002','2:2003'], ['3:3001']]],

            // wenn rot        =>   [rot,blau,rot/gelb], [M,L,XL],   [metall,baumwolle]
            [['1'=>[1001]],             [['0:264'], ['1:1001','1:1002','1:1001,1003'], ['2:2001','2:2002','2:2003'], ['3:3001','3:3002']]],

            // wenn rot,XL     =>   [rot], [M,L,XL], [metall,baumwolle]
            [['1'=>[1001],'2'=>[2003]], [['0:264'], ['1:1001'], ['2:2001','2:2002','2:2003'], ['3:3001','3:3002']]],
            // wenn rot,L      =>   [rot,blau], [M,L,XL], [baumwolle]
            [['1'=>[1001],'2'=>[2002]], [['0:264'], ['1:1001','1:1002'], ['2:2001','2:2002','2:2003'], ['3:3001']]],
            // wenn blau,XL    =>   [rot], [L]
            [['1'=>[1002],'2'=>[2003]], [1=>['1:1001'], 2=>['2:2002']]],

            // wenn gelb,M       =>   [rot,rot/gelb]
            [['1'=>[1003],'2'=>[2001]], [1=>['1:1001','1:1001,1003']]],
            // wenn rot/gelb,M   =>   [rot,rot/gelb], [M], [baumwolle]
            [['1'=>[1001,1003],'2'=>[2001]], [['0:264'], ['1:1001','1:1001,1003'], ['2:2001'], ['3:3001']]],
            // wenn rot,M        =>   [rot,rot/gelb], [M,L,XL], [baumwolle]
            [['1'=>[1001],'2'=>[2001]], [['0:264'], ['1:1001','1:1001,1003'], ['2:2001','2:2002','2:2003'], ['3:3001']]],
        ];
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

    wenn gelb,M     =>   [baumwolle]
    wenn rot,M      =>   [baumwolle]
    */
    /**
     * @param $ids
     * @param $expectedValues
     * @dataProvider excludedFacetGroupsProvider
     */
    public function testGetExcludedFacetGroups($ids, $expectedValues)
    {
        $product = $this->getProduct('product/product-variant-facets.json', 'facets-for-product-variant-facets.json');

        $facetGroupSet = new \AboutYou\SDK\Model\FacetGroupSet($ids);
        $groups = $product->getExcludedFacetGroups($facetGroupSet);
        $this->assertCount(count($expectedValues), $groups);
        foreach ($expectedValues as $index => $expected) {
            $this->assertEquals($expected, $groups[$index]->getUniqueKey());
        }
    }

    public function excludedFacetGroupsProvider()
    {
        // array of [<ids array>, <expected group keys array>]
        return [
            // wenn []         =>       [rot,blau,gelb], [M,L,XL], [metall,baumwolle]
            [[],                        ['0:264', '1:1001,1002,1003', '2:2001,2002,2003', '3:3001,3002']],

            // wenn XL         =>       [rot],      [metall,baumwolle]
            [['2'=>[2003]],             ['0:264', '1:1001', '3:3001,3002']],
            // wenn L          =>       [rot,blau], [baumwolle]
            [['2'=>[2002]],             ['0:264', '1:1001,1002', '3:3001']],
            // wenn M          =>       [rot,gelb], [baumwolle]
            [['2'=>[2001]],             ['0:264', '1:1001,1003', '3:3001']],

            // wenn rot        =>       [M,L,XL],   [metall,baumwolle]
            [['1'=>[1001]],             ['0:264', '2:2001,2002,2003', '3:3001,3002']],

            // wenn rot,XL     =>       [metall,baumwolle]
            [['1'=>[1001],'2'=>[2003]], ['0:264', '3:3001,3002']],
            // wenn rot,L      =>       [baumwolle]
            [['1'=>[1001],'2'=>[2002]], ['0:264', '3:3001']],
            // wenn blau,XL    =>       []
            [['1'=>[1002],'2'=>[2003]], []],

            // wenn gelb,M      =>       [baumwolle]
//            [['1'=>[1003],'2'=>[2001]], ['0:264', '3:3001']],
            // wenn rot,M      =>       [baumwolle]
            [['1'=>[1001],'2'=>[2001]], ['0:264', '3:3001']],
        ];
    }
}
