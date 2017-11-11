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


/**
 * Implement this interface on your
 * account class/models.
 */
interface AccountInterface
{
    /**
     * Id of the account.
     *
     * This should be the unique identifier in
     * your database. Could be incrementing 
     * integer or a hash id.
     * 
     * @return mixed Unique identifier in your database
     */
    public function getAccountId();


    /**
     * Name of the account.
     *         
     * @return String Descriptive name of the account
     */
    public function getAccountName();


    /**
     * Account Descriptiption 
     *         
     * @return String 
     */
    public function getAccountDescription();


    /**
     * Account Type refers to the nature of the account.
     * Valid types are;
     *
     * Asset
     * Liability
     * Equity/Owners Equity
     * Income/Revenue
     * Expense
     *
     * 
     * @return string Type of account
     */
    public function getAccountType();
}
