<?php

declare(strict_types=1);

include_once 'classes/system.php';
include_once '../vendor/autoload.php';

use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Components\TransactionLine;
use SamMcDonald\LucaAccounts\Enums\AccountType;

function simpleTransaction(): void
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
    $acc1 = new Account('Purchases Account', 123, AccountType::Liability);
    $acc2 = new Account('acc-rec-account', 987, AccountType::Asset);


    echo "4. Creating the transaction.." . PHP_EOL;
    $txn = new Transaction(
        new DateTimeImmutable('now'),
        'Purchase of inventory',
        [
            new TransactionLine($acc1, 150.00,   0.00),
            new TransactionLine($acc2, 000.00, 150.00),
        ]
    );

    /*
     * Perform the transaction - optional callback available
     */
    echo "5. Perform the transaction.." . PHP_EOL;
    $system->transact(
        $txn,
        function($result){
            echo '7. This is called last' . PHP_EOL;
        }
    );

}

simpleTransaction();
