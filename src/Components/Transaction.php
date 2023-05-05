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

use DateTimeImmutable;
use SamMcDonald\LucaAccounts\Exceptions\InvalidTransactionLineEntryException;
use SamMcDonald\LucaAccounts\Util\EntryFormatter;
use SamMcDonald\LucaAccounts\Contracts\TransactionInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;

/**
 * The Transaction class is responsible for containing 
 * the TransactionLines and balancing the records 
 * before they are committed to the designated 
 * journal/database.
 * 
 * @category   Finance/Accounting
 * @package    S-Mcdonald\Accounts
 * @author     Sam McDonald <s.mcdonald@outlook.com.au>
 * @copyright  2016-2023 Sam McDonald
 * @license    https://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @link       https://github.com/s-mcdonald
 * @since      1.0.0
 * 
 */
class Transaction implements TransactionInterface
{
    /**
     * Date of transaction
     */
    protected DateTimeImmutable $date;

    /**
     * Debits : Array of TransactionLines
     */
    protected array $debits;

    /**
     * Credits : Array of TransactionLines
     */
    protected array $credits;

    /**
     * Comment for the transaction
     */
    protected string $comment;

    /**
     * Transaction validity flag
     */
    private bool $isValid;

    /**
     * Listing of accounts used
     * in transaction
     */
    private array $accountsUsed;

    /**
     *  new Transaction(
     *      $date,
     *      'purchase of stock',
     *      [
     *          new TransactionLine($stockAccount,'purchase of stock', 15.00,  0.00),
     *          new TransactionLine($cashAccount, 'purchase of stock', 0.00,  10.00),
     *          new TransactionLine($otherAccount, 'purchase of stock', 0.00,  05.00),
     *      ]
     *  );
     *
     * @throws InvalidTransactionLineEntryException
     * @throws \Exception
     */
    public function __construct(DateTimeImmutable $date, string $comment, array $entries = [])
    {
        $this->debits = [];
        $this->credits = [];
                
        $this->date = $date;
        $this->isValid = false;
        $this->accountsUsed = [];
        $this->comment = EntryFormatter::Description($comment);

        foreach($entries as $entry) 
        {
            if($entry instanceof TransactionLine)
            {
                $this->addTransactionLine($entry);
            }
            else
            {
                throw new InvalidTransactionLineEntryException('One or more of the JournalLine[s] are not valid');
            }
        }       
    }

    public function addTransactionLine(TransactionLine $line): void
    {
        if($line->getComment() == null) {
            $line->setComment($this->comment);
        }

        if(in_array(
            $line->getAccount()->getAccountName(),
            $this->accountsUsed
        )) {
            throw new DoubleEntryException(
                "The Account `".$line->getAccount()->getAccountName().
                "` has been used more than once.".
                " in this transaction.."
            );
        }

        $this->accountsUsed[$line->getAccount()->getAccountId()] = $line->getAccount()->getAccountName();

        if($line->isDebit()) {
            $this->debits[] = $line;
        } else {
            $this->credits[] = $line;
        }

        $this->validate();
    }

    public function removeTransactionLine(mixed $account_id): void
    {
        unset($this->accountsUsed[$account_id]);

        foreach ($this->debits as $key => $line) {
            if($line->getAccount()->getAccountId() === $account_id) {
                unset($this->debits[$key]);
            }
        }

        foreach ($this->credits as $key => $line) {
            if($line->getAccount()->getAccountId() === $account_id) {
                unset($this->credits[$key]);
            }
        }

        $this->validate();
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getAccountlineEntries(): array
    {
        return array_merge($this->getDebits(), $this->getCredits());
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getDebits(): array
    {
        usort($this->debits, [$this, "cmp"]);
        return $this->debits;
    }

    public function getDebitsUnsorted(): array
    {
        return $this->debits;
    }

    public function getCredits(): array
    {
        usort($this->credits, [$this, "cmp"]);
        return $this->credits;
    }

    public function getCreditsUnsorted(): array
    {
        return $this->credits;
    }

    public function isValid() : bool
    {
        return $this->validate();
    }

    private function validate(): bool
    {
        $this->isValid = false;

        foreach($this->debits as $dr) 
        {
            if(!($dr instanceof TransactionLine)) {
                // an be an exception could be thrown here
                return false;
            }
        } 

        foreach($this->credits as $cr) 
        {
            if(!($cr instanceof TransactionLine)) {
                return false;
            }
        } 

        // we check that both 
        // cr and dr balance
        $drTotal = $crTotal = 0;

        foreach($this->debits as $k => $dr) {
           $drTotal += $dr->getValue();
        }

        foreach($this->credits as $k => $cr) {
           $crTotal += $cr->getValue();
        }

        if(($drTotal === $crTotal) && (0 > $drTotal)) {
            $this->isValid = true;
        }

        return $this->isValid;
    }
        
    /**
     * Sort the txn lines by larger amounts in 
     * to be displayed first. makes ready 
     * ledgers and records much cleaner.
     */
    private function cmp($a, $b): mixed
    {
        return max($a->getValue(), $b->getValue());
    }
}
