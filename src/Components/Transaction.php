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
namespace SamMcDonald\LucaAccounts\Components;

use Carbon\Carbon;
use SamMcDonald\LucaAccounts\Util\EntryFormatter;
use SamMcDonald\LucaAccounts\Contracts\TransactionInterface;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;
use SamMcDonald\LucaAccounts\Components\TransactionLine;

/**
 * The Transaction class is responsible for containing 
 * the TransactionLines and balancing the records 
 * before they are committed to the designated 
 * journal/database.
 * 
 * @category   Finance/Accounting
 * @package    S-Mcdonald\Accounts
 * @author     Sam McDonald <s.mcdonald@outlook.com.au>
 * @copyright  2016-2017 Sam McDonald
 * @license    https://opensource.org/licenses/MIT  MIT
 * @version    1.0.0
 * @link       <link to github>
 * @since      Class available since Release 1.0.0
 * 
 */
class Transaction implements TransactionInterface
{
    /**
     * Date of transaction
     * 
     * @var Carbon\Carbon
     */
    protected $date;


    /**
     * Debits : Array of TransactionLines
     * 
     * @var array
     */
    protected $debits;

    /**
     * Credits : Array of TransactionLines
     * 
     * @var array
     */
    protected $credits;

    /**
     * Comment for the transaction
     * 
     * @var string
     */
    protected $comment;


    /**
     * Transaction validity flag
     * 
     * @var boolean
     */
    private $is_valid;



    /**
     * Listying of accounts used 
     * in transaction
     * 
     * @var array
     */
    private $accounts_used;



    /**
     *  new Transaction(
     *      $date,
     *      'purchase of stock',
     *      [
     *          new TransactionLine($stockAccount,'purchase of stock', 15.00,  0.00),
     *          new TransactionLine($cashAccount, 'purchase of stock', 0.00,  10.00),
     *          new TransactionLine($acpyAccount, 'purchase of stock', 0.00,  05.00),
     *      ]
     *  );
     *  
     * 
     * @param Carbon $date              [description]
     * @param [type] $description       [description]
     * @param [type] $entries           [description]
     */
    public function __construct(Carbon $date, $comment = null, array $entries = []) 
    {
        $this->debits = [];
        $this->credits = [];
                
        $this->date = $date;
        $this->is_valid = false;
        $this->accounts_used = [];
        $this->comment = EntryFormatter::Description($comment);

        foreach($entries as $entry) 
        {
            if($entry instanceof TransactionLine)
            {
                $this->addTransactionLine($entry);
            }
            else
            {
                throw new DoubleEntryException('One or more of the JournalLine[s] are not valid');
            }
        }       
    }

    /**
     * Prepares and adds the JournalLine to the txn
     * 
     * @param TransactionLine $line 
     */
    public function addTransactionLine(TransactionLine $line) 
    {
        if($line->getComment() == null)
            $line->setComment($this->comment);

        if(in_array($line->getAccount()->getAccountName(),$this->accounts_used))
        {
            throw new DoubleEntryException(
                "Account `".$line->getAccount()->getAccountName().
                "` has been used more than once.".
                " in this transaction.."
            );
        }

        /** $array[account-id] = 'account-name'  */
        $this->accounts_used[$line->getAccount()->getAccountId()] 
            = $line->getAccount()->getAccountName();

        if($line->isDebit()) 
            $this->debits[] = $line;
        else 
            $this->credits[] = $line;

        $this->validate();
    }


    /**
     * Removes a Transactionline from the Transaction.
     * This should only occur before comitting
     * to database or posting.
     * 
     * @param  mixed $account_id The account-id Can be string or integer
     * @return void
     */
    public function removeTransactionLine($account_id) 
    {

        unset($this->accounts_used[$account_id]);

        foreach ($this->debits as $key => $line) {
            if($line->getAccount()->getAccountId()==$account_id)
                unset($this->debits[$key]);
        }

        foreach ($this->credits as $key => $line) {
            if($line->getAccount()->getAccountId()==$account_id)
                unset($this->credits[$key]);
        }

        $this->validate();
    }

    /**
     * Date of Transaction
     * 
     * @return Carbon\Carbon Date of transaction
     */
    public function getDate() 
    {
        return $this->date;
    }


    /**
     * Retrieves ALL Debits and Credits
     * This function will merge both 
     * into a single array and
     * order by values.
     *     
     * @return array Merged set of TransactionLines
     */
    public function getAccountlineEntries() 
    {
        return array_merge($this->getDebits(), $this->getCredits());
    }

    /**
     * Gets the Transaction Comment
     * 
     * @return string Transaction Comment
     */
    public function getComment() 
    {
        return $this->comment;
    }

    /**
     * Retrieves the debits for the Txn
     * 
     * @return array :array of debits sorted fom 
     *                greatest value to least
     */
    public function getDebits() 
    {
        usort($this->debits, [$this, "cmp"]);
        return $this->debits;
    }

    /**
     * Retrieves the credits for the Txn
     * 
     * @return array :array of credits sorted fom 
     *                greatest value to least
     */
    public function getCredits() 
    {
        usort($this->credits, [$this, "cmp"]);
        return $this->credits;
    }

    /**
     * Gets the validity of the Transaction if Valid is false,
     * this does not mean the object should be disposed.
     * It just meansthere is 1 or more factors causing 
     * the object to not be allowed to be stored. 
     * This should be investigated.
     *
     * @todo  Create an internal message so developer
     *        can get a code/reason why its not valid
     * 
     * @return boolean [description]
     */
    public function isValid() : bool
    {
        return $this->validate();
    }

    /**
     * Validates the transaction
     * 
     * @return bool Weather or not the TXN is valid
     */
    private function validate() : bool 
    {

        $this->is_valid = false;

        foreach($this->debits as $dr) 
        {
            if(!($dr instanceof TransactionLine))
                return false;
        } 

        foreach($this->credits as $cr) 
        {
            if(!($cr instanceof TransactionLine))
                return false;
        } 

        // we check that both 
        // cr and dr balance
        $drtotal = 0;
        $crtotal = 0;

        foreach($this->debits as $k => $dr) 
        {
           $drtotal += $dr->getValue();
        }

        foreach($this->credits as $k => $cr) 
        {
           $crtotal += $cr->getValue();
        }

        // both dr and cr must 
        // be larger than 0
        if(($drtotal > 0) && ($drtotal === $crtotal))
        {
            $this->is_valid = true;
            return true;
        }

        return false;
    }
        
    /**
     * Sort the txn lines by larger amounts in 
     * to be displayed first. makes ready 
     * ledgers and records much cleaner.
     * 
     * @param  [type] $a [description]
     * @param  [type] $b [description]
     * @return [type]    [description]
     */
    private function cmp($a, $b)
    {
        return max($a->getValue(), $b->getValue());
    }

}
