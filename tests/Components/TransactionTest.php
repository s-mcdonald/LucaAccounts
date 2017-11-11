<?php

namespace Tests\SamMcDonald\LucaAccounts\Components;

use PHPUnit\Framework\TestCase;

use Mockery;
use Carbon\Carbon;
use SamMcDonald\LucaAccounts\Util\EntryFormatter;
use SamMcDonald\LucaAccounts\Contracts\TransactionInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Components\Transaction;

class TransactionTest extends TestCase 
{
    

    public function setUp()
    {
        $this->account1 = Mockery::mock('SamMcDonald\LucaAccounts\Contracts\AccountInterface');
        $this->account1->shouldReceive('getAccountId')->andReturn(158);
        $this->account1->shouldReceive('getAccountName')->andReturn('Cash');
        $this->account1->shouldReceive('getAccountType')->andReturn('Asset');
        $this->account1->shouldReceive('getAccountDescription')->andReturn('Cash at Bank');

        $this->account2 = Mockery::mock('SamMcDonald\LucaAccounts\Contracts\AccountInterface');
        $this->account2->shouldReceive('getAccountId')->andReturn(550);
        $this->account2->shouldReceive('getAccountName')->andReturn('Phone Bill');
        $this->account2->shouldReceive('getAccountType')->andReturn('Expense');
        $this->account2->shouldReceive('getAccountDescription')->andReturn('Phone Bill Expense Account');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testFailsConstructor()
    {
        $this->expectException(DoubleEntryException::class);

        $date = Carbon::now();

        $txn = new Transaction($date, null, ['string_value']);

    }

    public function testGetDate()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, '');
        $line2 = new TransactionLine($this->account2, 50, 0, '');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertEquals($date, $txn->getDate());

    }

    public function testGetComment()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 50, 0, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertEquals('Valid Txn', $txn->getComment());

    }

    public function testAddTransactionLineError()
    {

        $this->expectException(DoubleEntryException::class);

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account1, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1, $line2]);

    }

    public function testAddTransactionLineDebit()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line2]);

        $txn->addTransactionLine($line1);

        $this->assertEquals([$line1], $txn->getDebits());

    }

    public function testAddTransactionLineCredit()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1]);

        $txn->addTransactionLine($line2);

        $this->assertEquals([$line2], $txn->getCredits());

    }

    public function testTXNValidity1()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 50, 0, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertFalse($txn->isValid());

    }

    public function testTXNValidity2()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertTrue($txn->isValid());

    }

    public function testRemoveTransactionLineError()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1, $line2]);

        $txn->removeTransactionLine(158);


        $this->assertEquals([$line2], $txn->getAccountlineEntries());

        

    }

    public function testCMPTesting()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 80, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 50, 0, 'Account 2 Comment');

        $txn = new Transaction($date, 'InValid Txn', [$line1, $line2]);

        $this->assertEquals([$line2, $line1], $txn->getDebits());

    }

    public function testGetCredits()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertEquals([$line2], $txn->getCredits());

    }

    public function testGetDedits()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertEquals([$line1], $txn->getDebits());

    }

    public function testGetAccountlineEntries()
    {

        $date = Carbon::now();

        $line1 = new TransactionLine($this->account1, 50, 0, 'Account Comment');
        $line2 = new TransactionLine($this->account2, 0, 50, 'Account 2 Comment');

        $txn = new Transaction($date, 'Valid Txn', [$line1,$line2]);

        $this->assertEquals([$line1,$line2], $txn->getAccountlineEntries());

    } 
}
