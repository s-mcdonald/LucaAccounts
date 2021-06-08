# Luca Accounts
[![Source](https://img.shields.io/badge/source-S_McDonald-blue.svg)](https://github.com/s-mcdonald/LucaAccounts)
[![Build Status](https://travis-ci.org/s-mcdonald/LucaAccounts.svg?branch=master)](https://travis-ci.org/s-mcdonald/LucaAccounts)

Luca Accounts is a simple `Double Entry Accounting` validator that can be imlemented into your application. It validates and sorts transactions prior to comitting them to your Database. I'm currently adding some additional features for handling journals, ledgers and posting. 

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
* [Dependancies](#dependancies)
* [Quick Start](#quick-start)
* [Files](#files)
* [Name of Luca](#thename)


<a name="features"></a>
## Features

1) Double Entry based accounting.
2) Built-in validation of transactions.
3) Sorts transaction entries automatically prior to committing to the db.
4) Seperate Debit and Credit entries.
5) Quick and easy implementation.
6) Easily extend functionality by extending the AccountSystem class.
7) Does not hold you at ransom on how to implement your accounting system.


<a name="installation"></a>
## Installation

Via Composer. Run the following command from your project's root.

```
composer require s-mcdonald/luca-accounts
```



<a name="dependancies"></a>
## Dependancies

*  Carbon\Carbon
*  Php 7.0


<a name="quick-start"></a>
## Quick-Start

1)  Extend the `abstract AccountSystem` class and then Implement the `AccountInterface` to your `Account` model.

```php
      // Your\App\AccountSystem.php
      class AccountSystem extends \SamMcDonald\LucaAccounts\AccountSystem {
        ...
      }

      // Your\App\Account.php
      class Account implements \SamMcDonald\LucaAccounts\Contracts\AccountInterface {
        ...
      }
```

That's it! Now just write the transactions.

2) Initialize the AccountSystem. (Include the namespaces)

```php
        use Your\App\AccountSystem;
        use SamMcDonald\LucaAccounts\Components\Transaction;
        use SamMcDonald\LucaAccounts\Components\TransactionLine;
        
        ...

        // Instantiate the system
        $system = new AccountSystem();

        // Register the `transact` function
        $system->register('transact', function($txn) {

            Journal::createEntry(
                $txn->getDate(),
                $txn->getComment(),
                $txn->getDebits(),
                $txn->getCredits()              
            );  


        });
```

3) Load the accounts that you want to perform a transaction on.

```
        // Load some accounts you want to use in the Transaction
        $acc1 = Account::fetch('cash-account');
        $acc2 = Account::fetch('equity-account');
```

4) Prepare a Transaction

```php
        /**
         * Create a transaction
         */
        $txn = new Transaction( Carbon::now() , 'Capital Contribution', 
            [
                new TransactionLine($acc1, 100.00,  00.00),
                new TransactionLine($acc2,  00.00, 100.00),                
            ]
        );

```

5. Call the Transact Function

```php
        /**
         * Perform the transaction: This will call the
         * closure function you defined earlier to store
         * the transaction where you prefer.
         */
        $system->transact($txn);

```


## Complete Example

```php
<?php 
namespace Your\App;

use Your\App\AccountSystem;
use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Components\TransactionLine;

class AccountsTest 
{
    public static function simpleTransaction() 
    {
        // Get a new instance of the Accounting System
        $system = new AccountSystem();

        // Register the transact function
        $system->register('transact', function($txn) {

            Journal::createEntry(
                $txn->getDate(),
                $txn->getComment(),
                $txn->getDebits(),
                $txn->getCredits()              
            );  

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
        $txn = new Transaction( Carbon::now() , 'Purchase of inventory', 
            [
                new TransactionLine($acc1, 000.00,  50.00),
                new TransactionLine($acc2, 000.00, 150.00),
                new TransactionLine($acc3, 200.00,   0.00),                 
            ]
        );

        /*
         * Perform the transaction - optional callback available
         */
        $system->transact($txn,function($result){});

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
              │
              │    
              ├── Util/
              │   │
              │   ├── AccountType.php
              │   │            
              │   └── EntryFormatter.php
              │
              │
              └── AccountSystem.php

```

## Note on Development 

This package is for educational purposes only.
I will be extending the functionality over time. Feel free to fork the repo and request some features.
The package is intended to impose itself very little upon the inbound application so it can be used more as a validator and formatter, not as a fully fledged accounts system. Additional features will be available in future commits to the repo.


## License

Luca-Accounts is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).


## Name of Luca
<a name="thename"></a>
Luca-Accounts was named after Luca Pacioli (The father of Accounting). He popularized the DoubleEntry book-keeping system.
