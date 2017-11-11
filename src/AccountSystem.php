<?php
/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2017 Sam-McDonald
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace SamMcDonald\LucaAccounts;

use SamMcDonald\LucaAccounts\Account;
use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Contracts\JournalInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;


abstract class AccountSystem
{

    private $accountant;


    private $valid_closure;



    public function __construct() 
    {
        $this->accountant = null;
        $this->valid_closure = false;  
    }


    /**
     * Register the links between library and user app
     * 
     * @param  string        $method     [description]
     * @param  \Closure|null $user_funct [description]
     * @return [type]                    [description]
     */
    public final function register(string $method, \Closure $user_funct = null) : bool
    {
        $this->valid_closure = false;

        switch(trim(strtolower($method)))
        {
            case 'transact':
                $this->accountant = $user_funct;
                break;
                
            default:
                throw new \Exception('Method not a valid string');
                break;
        }

        if($user_funct instanceof \Closure)
        {
            $this->valid_closure = true;
            return true;
        }
   
        return false;
    }


    /**
     * 
     * @throws DoubleEntryException.
     * 
     * @param  Transaction   $transaction [description]
     * @param  Callable|null $callback    [description]
     * @return [type]                     [description]
     */
    public final function transact(Transaction $transaction, \Closure $user_funct = null)
    {
        $result = $called = null;

        if(!$transaction->isValid()) 
        {
            throw new DoubleEntryException('The provided transaction does not balance');
        }

        if(is_object($this->accountant) && ($this->accountant instanceof \Closure))
        {
            $result = call_user_func($this->accountant, $transaction);
            $called = true;
        }

        //return details about the call
        if(is_object($user_funct) && ($user_funct instanceof \Closure))
        {
            call_user_func($user_funct, $result, $called);
        }

        return $result;
    }

    /**
     * The $account object needs to be able to get its transactionlines;
     * 
     * @param  AccountCollection $accounts [description]
     * @return [type]                      [description]
     */
    public final function balance(AccountCollection $accounts)
    {
        $debits = 0;
        $credits = 0;

        foreach($accounts as $account)
        {
            $debits += $account->totalDebits();
            $credits += $account->totalCredits();
        }

        if($debits === $credits) 
        {
            return true;
        }

        return false;
    }


}
