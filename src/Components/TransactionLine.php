<?php

declare(strict_types=1);

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
namespace SamMcDonald\LucaAccounts\Components;

use SamMcDonald\LucaAccounts\Util\EntryFormatter;
use SamMcDonald\LucaAccounts\Contracts\AccountInterface;
use SamMcDonald\LucaAccounts\Contracts\TransactionLineInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;

/**
 * The TransactionLine class is responsible for containing 
 * the details for a single account on a journal 
 * transaction.
 * 
 * @category   Finance/Accounting
 * @package    S-Mcdonald\Accounts
 * @author     Sam McDonald <s.mcdonald@outlook.com.au>
 * @copyright  2016-2017 Sam McDonald
 * @license    https://opensource.org/licenses/MIT 
 * @version    1.0.0
 * @link       https://github.com/s-mcdonald
 * @since      Class available since Release 1.0.0
 * 
 */
class TransactionLine implements TransactionLineInterface
{
    private float $debit;

    private float $credit;

    /**
     * Account to be processed
     */
    private AccountInterface $account;

    /**
     * Comment per line basis
     */
    private string $comment;

    /**
     * Create a TransactionLine
     *
     * @param AccountInterface $account The account for the transaction line
     * @param float $debit The amount to be debited
     * @param float $credit The amount to be credited
     * @param string $comment Comment for the transaction.
     * @throws DoubleEntryException
     * @throws \Exception
     */
    public function __construct(
        AccountInterface $account, 
        float $debit = 0.00, 
        float $credit = 0.00,
        string $comment = ''
        ) 
    {
        $this->account = $account;

        $this->setComment($comment); 

        // natulize the number: make absolute
        // accounting does not have negative
        // numbers, so we must clean them.
        $this->debit = EntryFormatter::Amount($debit);  
        $this->credit = EntryFormatter::Amount($credit);  

        // Check that at least one value is ZERO
        // and at least one value is larger
        // than Zero.
        if(($this->debit > 0) && ($this->credit > 0)) {
            throw new DoubleEntryException('Either Debit or Credit value must be Zero'); 
        }

        if(($this->debit == 0 ) && ($this->credit == 0)) {
            throw new DoubleEntryException('Either Debit or Credit value must be larger then Zero'); 
        }
    }

    /**
     * Get the Account
     */
    public function getAccount(): AccountInterface
    {
        return $this->account;
    }

    /**
     * Sets the comment/memo of the TXNLine
     * @throws \Exception
     */
    public function setComment(string $comment): void
    {
        $this->comment = EntryFormatter::Description($comment);
    }

    /**
     * Get the comment on the line
     */
    public function getComment(): string 
    {
        return $this->comment;
    }

    /**
     * Just get the value, regardless of dr or cr
     */
    public function getValue(): float 
    {
        return ($this->isDebit())? $this->debit : $this->credit;
    }

    /**
     * Get the Debit value
     */
    public function getDebit(): float 
    {
        return $this->debit;
    }

    /**
     * Get the Credit value
     */
    public function getCredit(): float 
    {
        return $this->credit;
    }

    /**
     * Check whether line is a debit line.
     */
    public function isDebit(): bool 
    {
        if($this->debit > $this->credit) {
            if($this->credit == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether line is a credit line.
     */
    public function isCredit(): bool 
    {
        if($this->credit > $this->debit) {
            if($this->debit == 0) {
                return true;
            }
        }
        return false;
    }
}
