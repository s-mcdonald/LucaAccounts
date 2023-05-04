# Luca Accounts
[![Source](https://img.shields.io/badge/source-S_McDonald-blue.svg)](https://github.com/s-mcdonald/LucaAccounts)
[![Build Status](https://travis-ci.org/s-mcdonald/LucaAccounts.svg?branch=master)](https://travis-ci.org/s-mcdonald/LucaAccounts)

Luca Accounts is a `Double Entry Accounting` system that can easily be implemented into your application. 
It validates and sorts transactions prior to committing them to your Database.

```php
        //
        // Create a Transaction
        //
        $txn = new Transaction( $date , 'Capital Contribution', 
            [
                new TransactionLine($cash_account_1, 100.00,  00.00),
                new TransactionLine($eqty_account_2,  00.00, 100.00),                
            ]
        );

        //
        // Process the Transaction
        //
        $system->transact($txn);

```



## Documentation

* [Features](#features)
* [Installation](#installation)
* [Dependencies](#dependencies)
* [Quick Start](#quick-start)
* [Files](#files)
* [Name of Luca](#thename)


<a name="features"></a>
## Features

1) Follows Double Entry based accounting rules.
2) Built-in validation of transactions.
3) Sorts transaction (Dr|Cr) entries automatically prior to committing to the db.
4) Separate Debit and Credit entries.


<a name="installation"></a>
## Installation

Via Composer. Run the following command from your project's root.

```
composer require s-mcdonald/luca-accounts
```


<a name="dependencies"></a>
## Dependencies

*  Php 8.1


<a name="quick-start"></a>
## Quick-Start

1)  Extend the `abstract AccountSystem` class and then Implement the `AccountInterface` to your `Account` model.

```php
      // Your\App\AccountSystem.php
      class AccountSystem extends \SamMcDonald\LucaAccounts\AbstractAccountSystem {
        ...
      }

      // Your\App\Account.php
      class Account implements \SamMcDonald\LucaAccounts\Contracts\AccountInterface {
        ...
      }
```

That's it! Now just write the transactions.



## Example

```php
<?php 
namespace Your\App;

use Your\App\AccountSystem;
use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Components\TransactionLine;

class YourAccountingProgram
{
    public static function simpleTransaction() 
    {
        // Get a new instance of the Accounting System
        $system = new AccountSystem();

        // Register the transact function
        $system->register('transact', static function(Transaction $txn) {
             // Your logic to commit the transaction to DB
        });

        /*
         * Load the accounts you want to use in the Transaction
         */
        $acc1 = Account::fetch('cash-account'); 
        $acc2 = Account::fetch('acc-rec-account'); 
        $acc3 = Account::fetch('inventory-account'); 

        /*
         * Make a purchase of stock request
         */
        $txn = new Transaction( new DateTimeImmutable('now') , 'Purchase of inventory', 
            [
                new TransactionLine($acc1, 000.00,  50.00),
                new TransactionLine($acc2, 000.00, 150.00),
                new TransactionLine($acc3, 200.00,   0.00),                 
            ]
        );

        /*
         * Perform the transaction
         */
        $system->transact($txn);

    } 
}

```




<a name="files"></a>
## Files

```
s-mcdonald/luca-accounts/
            │    
            └ src/
              │    
              ├── Components/
              │   │
              │   ├── Transaction.php
              │   │            
              │   └── TransactionLine.php
              │            
              │            
              ├── Contracts/
              │   │
              │   ├── AccountInterface.php
              │   │            
              │   ├── TransactionInterface.php
              │   │
              │   └── TransactionLineInterface.php
              │            
              │  
              ├── Exceptions/
              │   │
              │   └── DoubleEntryException.php
              │   │
              │   └── InvalidTransactionLineEntryException.php
              │
              │        
              ├── Util/
              │   │
              │   ├── AccountType.php
              │   │            
              │   └── EntryFormatter.php
              │
              │
              └── AbstractAccountSystem.php

```

## License

Luca-Accounts is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).


## Name of Luca
<a name="thename"></a>
Luca-Accounts was named after Luca Pacioli (The father of Accounting). He popularized the DoubleEntry book-keeping system.
