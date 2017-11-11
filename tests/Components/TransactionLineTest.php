<?php

namespace Tests\SamMcDonald\LucaAccounts\Components;

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryTestCase;

use Mockery;
use Carbon\Carbon;
use SamMcDonald\LucaAccounts\Util\EntryFormatter;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Contracts\AccountInterface;
use SamMcDonald\LucaAccounts\Contracts\TransactionLineInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;


class TransactionLineTest extends MockeryTestCase 
{ 
    private $account;

    public function setUp()
    {
        $this->account = Mockery::mock('SamMcDonald\LucaAccounts\Contracts\AccountInterface');
        $this->account->shouldReceive('getAccountId')->andReturn(158);
        $this->account->shouldReceive('getAccountName')->andReturn('Cash');
        $this->account->shouldReceive('getAccountType')->andReturn('Asset');
        $this->account->shouldReceive('getAccountDescription')->andReturn('Cash at Bank');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testMockAccount()
    {
        $accountName = $this->account->getAccountName();
        $accountType = $this->account->getAccountType();

        $this->assertEquals('Cash', $accountName);
        $this->assertEquals('Asset', $accountType);
    }

    /**
     * Test the (Constructor) Valid and Invalid Terms
     */
    
    /**
     * @dataProvider debitDataProvider
     */
    public function testCheckConstrctorDebitPositiveMinEdge($debit, $credit)
    {
        $txn = new TransactionLine($this->account, $debit, $credit, ''); 
    }


    public function testCheckConstrctorValuesAtZeroException()
    {
        // Tell PHPUnit that we are expecting this exception.
        $this->expectException(DoubleEntryException::class);

        // InValid Constructor
        $txn = new TransactionLine($this->account, 0, 0, ''); 

    }

    public function testCheckConstrctorException()
    {
        // Tell PHPUnit that we are expecting this exception.
        $this->expectException(DoubleEntryException::class);

        // InValid Constructor
        $txn = new TransactionLine($this->account, 50, 70, ''); 

    }





    /**
     * Test the (Account) getAccount() method.
     */
    

    public function testCheckAccountValidity()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 0, 70, ''); 

        $this->assertEquals($this->account, $txn->getAccount());
    }



    /**
     * Test the (Float) Debit on Transaction Line
     */
    

    public function testDebitValueAsConstructed()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 50, 0, ''); 

        // Assert the default debit value as constructed initially
        // This should never change after created.
        $this->assertEquals(50, $txn->getValue());
        $this->assertEquals(50, $txn->getDebit());

        // For this to succeed we must also assert that Credit is also ZERO
        $this->assertEquals(0, $txn->getCredit());

        // Finally we must ensure that isDebit is true and isCredit is false
        $this->assertEquals(true, $txn->isDebit());
        $this->assertEquals(false, $txn->isCredit());
    }

    public function testCreditValueAsConstructed()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 0, 70, ''); 

        // Assert the default credit value as constructed initially
        // This should never change after created.
        $this->assertEquals(70, $txn->getValue());
        $this->assertEquals(70, $txn->getCredit());

        // For this to succeed we must also assert that Debit is also ZERO
        $this->assertEquals(0, $txn->getDebit());

        // Finally we must ensure that isCredit is true and isDebit is false
        $this->assertEquals(true, $txn->isCredit());
        $this->assertEquals(false, $txn->isDebit());
    }


    /**
     * Test the (String) Comment on Transaction Line
     */
    


    public function testExpectedSetComment()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 50, 0, ''); 

        // Assert the default Comment
        $this->assertEquals('', $txn->getComment());


        // Assert that the new comment equals what we passed to it
        $new_comment = 'Changed as expected';
        $txn->setComment($new_comment);
        $this->assertEquals($new_comment, $txn->getComment());
    }

    public function testLongSetComment()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 50, 0, ''); 

        // Assert very long comment
        $new_comment = 'This is a very long comment for a transaction line and one would not expect that a transaction would have such a long comment.';
        $txn->setComment($new_comment);
        $this->assertNotEquals($new_comment, $txn->getComment());
    }

    public function testNullInputOnSetComment()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->account, 50, 0, ''); 

        // Assert null as a comment
        $txn->setComment(null);
        $this->assertEquals(null, $txn->getComment());
    }


    /*
     *
     * Data Providers
     * 
     * 
     */
    



    /**
     * debitDataProvider DtaProvider for testing initial constructor values.
     * 
     * @return array
     */
    public function debitDataProvider()
    {
        // test with this values
        return [
            [0, 0.500],
            [0.500, 0],
        ];
    }
}
