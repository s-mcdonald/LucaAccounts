<?php

declare(strict_types=1);

namespace Tests\SamMcDonald\LucaAccounts\Components;

use Mockery;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Contracts\AccountInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;
use PHPUnit\Framework\TestCase;

class TransactionLineTest extends TestCase 
{
    /**
     * @throws DoubleEntryException
     * @throws \Exception
     */
    public function testSetComment()
    {
        $comment = 'foo';
        $transactionLine = $this->createValidTransactionLine();

        // prior to setting comment
        $this->assertEquals('', $transactionLine->getComment());
        $transactionLine->setComment($comment);

        $this->assertEquals($comment, $transactionLine->getComment());
    }

    /**
     * @dataProvider provideDataForTestDoesNotThrowException
     * @throws DoubleEntryException
     */
    public function testDoesNotThrowException($debit, $credit)
    {
        $flag = false;
        try {
            // @todo - Write a ExceptionAsserter
            $txn = new TransactionLine(
                $this->createAccount(),
                $debit,
                $credit,
                'foo'
            );
        } catch (\Exception $e) {
            $flag = true;
        }

        self::assertFalse($flag);
    }

    public static function provideDataForTestDoesNotThrowException(): array
    {
        return [
            [0, 0.500],
            [0.500, 0],
        ];
    }

    /**
     * @dataProvider provideDataForTestExceptions
     * @throws DoubleEntryException
     */
    public function testExceptions($debit, $credit)
    {
        $this->expectException(DoubleEntryException::class);

        $txn = new TransactionLine(
            $this->createAccount(),
            $debit,
            $credit,
            'foo'
        );
    }

    public static function provideDataForTestExceptions(): array
    {
        return [
            [0, 0],
            [0.00, 0.00],
            [50.00, 70.00],
        ];
    }

    /**
     * @throws DoubleEntryException
     */
    public function testCheckAccountValidity()
    {
        $account = $this->createAccount();
        $txn = new TransactionLine($account, 0, 70, '');
        $this->assertSame($account, $txn->getAccount());
    }

    /**
     * @throws DoubleEntryException
     */
    public function testDebitValueAsConstructed()
    {
        $debitAmount = 50;
        $creditAmount = 0;

        // Create The TXN Line
        $txn = new TransactionLine($this->createAccount(), $debitAmount, $creditAmount, '');

        // Assert the default debit value as constructed initially
        // This should never change after created.
        $this->assertEquals($debitAmount, $txn->getValue());
        $this->assertEquals($debitAmount, $txn->getDebit());

        // For this to succeed we must also assert that Credit is also ZERO
        $this->assertEquals($creditAmount, $txn->getCredit());

        // Finally we must ensure that isDebit is true and isCredit is false
        $this->assertEquals(true, $txn->isDebit());
        $this->assertEquals(false, $txn->isCredit());
    }

    public function testCreditValueAsConstructed()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->createAccount(), 0, 70, '');

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
     * @throws DoubleEntryException
     */
    public function testLongSetComment()
    {
        // Create The TXN Line
        $txn = new TransactionLine($this->createAccount(), 50, 0, '');

        $new_comment = <<<STR
This is a very long comment for a transaction line and one would not expect that a transaction would have such a long comment.
STR;

        $txn->setComment($new_comment);
        $this->assertEquals("This is a very long comment for a transaction line...", $txn->getComment());
    }

    /**
     * @throws DoubleEntryException
     */
    public function createValidTransactionLine(): TransactionLine
    {
        $account = Mockery::mock(AccountInterface::class);
        return new TransactionLine(
            $account,
            00.00,
            10.00
        );
    }

    public function createAccount(): Mockery|AccountInterface
    {
        return Mockery::mock(AccountInterface::class);
    }
}
