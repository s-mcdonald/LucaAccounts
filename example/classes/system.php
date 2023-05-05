<?php

/**
 * The classes in this file represent your own classes within your application.
 *  * The `AccountSystem` class needs to extend the packages Abstract `AbstractAccountSystem`
 *  * The `Account` represents your model or entity class.
 *  * The `Journal` is a class that you use to persist the transactions.
 */
declare(strict_types=1);

include_once './vendor/autoload.php';

use SamMcDonald\LucaAccounts\AbstractAccountSystem;
use SamMcDonald\LucaAccounts\Contracts\AccountInterface;
use SamMcDonald\LucaAccounts\Enums\AccountType;

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
        return 'Account recording all ' . $this->name;
    }

    public function getAccountType(): AccountType
    {
        return $this->type;
    }
}

class Journal
{
    public static function createEntry(DateTimeInterface $dateTime, string $comment, array $debitLines, array $creditLines)
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
