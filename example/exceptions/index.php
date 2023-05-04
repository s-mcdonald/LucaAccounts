<?php

declare(strict_types=1);

include '../../vendor/autoload.php';

echo PHP_EOL;

use SamMcDonald\LucaAccounts\AbstractAccountSystem;
use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Contracts\AccountInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;
use SamMcDonald\LucaAccounts\Exceptions\InvalidTransactionLineEntryException;
use SamMcDonald\LucaAccounts\Util\AccountType;

class AccountSystem extends AbstractAccountSystem
{

}

class Account implements AccountInterface
{
    public function __construct(private string $name, private int $id, private AccountType $type = AccountType::Asset)
    {
    }

    public function getAccountId()
    {
        return $this->id;
    }

    public function getAccountName(): string
    {
        return $this->name;
    }

    public function getAccountDescription(): string
    {
        return 'Accout recording all ' . $this->name;
    }

    public function getAccountType(): string
    {
        return $this->type->value;
    }
}

class Journal
{
    public static function createEntry(DateTimeInterface $dateTime, string $comment, array $debitLines, array $creditLines): void
    {
        echo '6.  !!! We are now transacting ' . $comment . ' !!! '. PHP_EOL;
        echo '6.1   DateTime: ' . $dateTime->format('Y-m-d H:m:s'). PHP_EOL;
        echo '6.2   Comment: ' . $comment. PHP_EOL;
        echo '6.3   Debits: ' . PHP_EOL;

        foreach ($debitLines as $debitLine) {
            echo '  Dr   : ' . $debitLine->getDebit() . PHP_EOL;
            echo '  Cr   : ' . $debitLine->getCredit() . PHP_EOL;
        }

        echo '6.4   Credits: ' . PHP_EOL;
        foreach ($creditLines as $creditLine) {
            echo '  Dr   : ' . $creditLine->getDebit() . PHP_EOL;
            echo '  Cr   : ' . $creditLine->getCredit() . PHP_EOL;
        }

    }
}

function nonBalancingTransactions() 
{
    echo "1. Instantiate the AccountSystem.." . PHP_EOL;
    $system = new AccountSystem();

    echo "2. Register the transact function.." . PHP_EOL;
    $system->register('transact', static function(Transaction $transaction) {
        Journal::createEntry(
            $transaction->getDate(),
            $transaction->getComment(),
            $transaction->getDebits(),
            $transaction->getCredits()              
        );  
    });

    /*
        * Load the accounts you want to use in the Transaction
        */
    echo "3. Load the Accounts from DB.." . PHP_EOL;
    $acc1 = new Account('Purchases Account', 123, AccountType::Expense);
    $acc2 = new Account('acc-rec-account', 987, AccountType::Asset);

    echo "4. Creating the transaction.." . PHP_EOL;
    $txn = new Transaction( 
        new DateTimeImmutable('now'), 
        'Purchase of inventory', 
        [
            new TransactionLine($acc1,  15.00,   0.00),
            new TransactionLine($acc2, 000.00, 150.00),              
        ]
    );

    echo "5. Perform the transaction.." . PHP_EOL;
    try {
        $system->transact(
            $txn,
            function($result){
                echo '7. This is called last' . PHP_EOL;
            }
        );
    } catch(DoubleEntryException $ex) {
        echo "Caught it!" . PHP_EOL;
    }

} 

function invalidTransactionLine() 
{
    $acc1 = new Account('Purchases Account', 123, AccountType::Expense);
    $acc2 = new Account('acc-rec-account', 987, AccountType::Asset);

    try {
        $txn = new Transaction( 
            new DateTimeImmutable('now'), 
            'Transaction description', 
            [
                'this is not avlid transaction',
                new TransactionLine($acc1,  15.00,   0.00),
                new TransactionLine($acc2, 000.00, 150.00),              
            ]
        );
    } catch(InvalidTransactionLineEntryException $ex) {
        echo "Caught it! - Now you can handle this in advance.." . PHP_EOL;
        return;
    }

    echo "5. Will never reach here.." . PHP_EOL;
} 

nonBalancingTransactions();

invalidTransactionLine();