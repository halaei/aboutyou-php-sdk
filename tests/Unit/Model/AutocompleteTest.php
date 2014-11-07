<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;

use AboutYou\SDK\Model\Autocomplete;

class AutocompleteTest extends AbstractModelTest
{
    public function testCreateFromJson()
    {
        $factory = $this->getModelFactory();
        $jsonObject = json_decode('{}');
        $autocomplete = Autocomplete::createFromJson($jsonObject, $factory);
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getCategories());
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getProducts());
    }
}
