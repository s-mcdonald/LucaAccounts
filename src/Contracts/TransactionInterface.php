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
namespace SamMcDonald\LucaAccounts\Contracts;

use SamMcDonald\LucaAccounts\Components\TransactionLine;

interface TransactionInterface
{

    /**
     * Prepares and adds the JournalLine to the txn
     * 
     * @param TransactionLine $line 
     */
    public function addTransactionLine(TransactionLine $line);


    /**
     * Removes a Transactionline from the Transaction.
     * This should only occur before comitting
     * to database or posting.
     * 
     * @param  mixed $account_id The account-id Can be string or integer
     * @return void
     */
    public function removeTransactionLine($account_id);


    /**
     * Date of Transaction
     * 
     * @return Carbon\Carbon Date of transaction
     */
    public function getDate();


    /**
     * Retrieves ALL Debits and Credits
     * This function will merge both 
     * into a single array and
     * order by values.
     *     
     * @return array Merged set of TransactionLines
     */
    public function getAccountlineEntries();


    /**
     * Gets the Transaction Comment
     * 
     * @return string Transaction Comment
     */
    public function getComment();


    /**
     * Retrieves the debits for the Txn
     * 
     * @return array :array of debits sorted fom 
     *                greatest value to least
     */
    public function getDebits();



    /**
     * Retrieves the credits for the Txn
     * 
     * @return array :array of credits sorted fom 
     *                greatest value to least
     */
    public function getCredits();



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
    public function isValid();

}
