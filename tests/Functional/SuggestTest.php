<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

class SuggestTest extends AbstractAYTest
{
    public function testSuggest()
    {
        $ay = $this->getAYWithResultFile('suggest.json');

        $suggestions = $ay->fetchSuggest('hose');
        $this->assertInternalType('array', $suggestions);
        $this->assertCount(10, $suggestions);
        $this->assertEquals('fit', $suggestions[3]);
    }
}
