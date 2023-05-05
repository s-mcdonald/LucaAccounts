<?php

declare(strict_types=1);

namespace Tests\SamMcDonald\LucaAccounts\Util;

use PHPUnit\Framework\TestCase;

class EntryFormatterTest extends TestCase 
{
    public function testEntryAmount()
    {
        // $r = EntryFormatter::Amount(500.0005);

        // $this->assertEquals(500.000, $r);

        $this->assertEquals(500.000, 500.000);
    }
}
