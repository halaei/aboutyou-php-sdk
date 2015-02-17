<?php

namespace AboutYou\SDK\Test\Live;


use AboutYou\SDK\Constants;
use AboutYou\SDK\Model\SpellCorrection;

/**
 * @group live
 */
class SpellCorrectionTest extends \AboutYou\SDK\Test\Live\AbstractAYLiveTest
{
    public function testSpellCorrection()
    {
        $ay = $this->getAY();

        $spellCorrection = $ay->fetchSpellCorrection('gex');
        $this->assertCount(1, $spellCorrection);
        $this->assertEquals('gel', $spellCorrection[0]);

        $spellCorrection = $ay->fetchSpellCorrection('gex', 1);
        $this->assertCount(0, $spellCorrection);
    }
}