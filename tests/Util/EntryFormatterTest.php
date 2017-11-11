<?php

namespace Tests\SamMcDonald\LucaAccounts\Util;

use SamMcDonald\LucaAccounts\Util\EntryFormatter;

use PHPUnit\Framework\TestCase;

class EntryFormatterTest extends TestCase 
{
    public function setUp()
    {

    }


    public function tearDown()
    {

    }

    public function testInvalidString()
    {
        $this->expectException(\Exception::class);

        $r = EntryFormatter::Description(['test']);
    }

    public function testEntryAmount()
    {
        $r = EntryFormatter::Amount(500.0005);

        $this->assertEquals(500.000, $r);
    }
}
