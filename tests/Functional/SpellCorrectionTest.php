<?php

namespace AboutYou\SDK\Test\Functional;

use \AY;

class SpellCorrectionTest extends AbstractAYTest
{
    /**
     *
     */
    public function testSpellCorrection()
    {
        $ay = $this->getAYWithResultFile(
            'result/spellcorrection-shop.json'
        );
        
        $spellCorrection = $ay->fetchSpellCorrection('gex');
        $this->assertCount(1, $spellCorrection);
        $this->assertEquals('gel', $spellCorrection[0]);
    }
}
