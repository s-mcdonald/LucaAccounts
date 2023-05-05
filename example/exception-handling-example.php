<?php

declare(strict_types=1);

include_once 'classes/system.php';
include_once '../vendor/autoload.php';

echo PHP_EOL;

use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Enums\AccountType;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;
use SamMcDonald\LucaAccounts\Exceptions\InvalidTransactionLineEntryException;


function nonBalancingTransactions(): void
{
    echo "1. Instantiate the AccountSystem..." . PHP_EOL;
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

function invalidTransactionLine(): void
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