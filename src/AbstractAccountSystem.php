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
namespace SamMcDonald\LucaAccounts;

use SamMcDonald\LucaAccounts\Components\Transaction;
use SamMcDonald\LucaAccounts\Exceptions\DoubleEntryException;

abstract class AbstractAccountSystem
{
    private ?\Closure $accountant;

    private bool $validClosure;

    public function __construct() 
    {
        $this->accountant = null;
        $this->validClosure = false;
    }

    /**
     * Register the links between library and user app
     * @throws \Exception
     */
    public final function register(string $method, \Closure $userFunction = null): bool
    {
        $this->validClosure = false;

        $this->accountant = match (trim(strtolower($method))) {
            'transact' => $userFunction,
            default => throw new \Exception('Method not a valid string'),
        };

        if($userFunction !== null) {
            $this->validClosure = true;
        }
   
        return $this->validClosure;
    }

    /**
     * @throws DoubleEntryException.
     */
    public final function transact(Transaction $transaction, \Closure $userFunction = null)
    {
        $result = $called = null;

        if(!$transaction->isValid()) {
            throw new DoubleEntryException(
                sprintf(
                    'The transaction on %s does not balance.',
                    $transaction->getDate()->format('Y-m-d H:m:s')
                )
            );
        }

        if($this->accountant instanceof \Closure) {
            $result = call_user_func($this->accountant, $transaction);
            $called = true;
        } else {
            // system may not be initialized correctly
        }

        if($userFunction !== null) {
            call_user_func($userFunction, $result, $called);
        }

        return $result;
    }
}
