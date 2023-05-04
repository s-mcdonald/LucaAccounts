<?php

namespace Tests\SamMcDonald\LucaAccounts;

use PHPUnit\Framework\TestCase;

use Mockery;

use SamMcDonald\LucaAccounts\Account;
use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Contracts\JournalInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;


class AccountSystemTest extends TestCase
{


    public function testSimple()
    {
        $sys = $this->getSystemRegisterTrue();

        $this->assertEquals(true,
            $sys->register('transact', function(){
                return 'PASS';
            })
        );

    }


    public function testFailSimple()
    {
        $this->expectException(\Exception::class);

        $sys = $this->getSystemRegisterTrue();

        $sys->register('something', function(){
            return 'PASS';
        });
    
    }

    private function getSystemRegisterTrue()
    {
        $system = Mockery::mock('SamMcDonald\LucaAccounts\AbstractAccountSystem');
        $system->shouldReceive('register')->andReturn(true); 
        return $system;
    }

}
