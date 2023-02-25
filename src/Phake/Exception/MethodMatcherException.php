<?php

declare(strict_types=1);

namespace Phake\Exception;

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <m@digitalsandwich.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

/**
 * Thrown when a method call doesn't match an expection
 */
class MethodMatcherException extends \Exception
{
    /**
     * @var int
     */
    private int $argument;

    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct(string $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->argument = 0;
    }

    /**
     * Updates the argument position (used in the argument chain)
     * @return void
     */
    public function incrementArgumentPosition(): void
    {
        $this->argument++;
    }

    /**
     * Returns the argument's position (0 indexed)
     * @return int
     */
    public function getArgumentPosition(): int
    {
        return $this->argument;
    }

    /**
     * Get the message, but include the comparison diff.
     *
     * @internal This is so we can lazy generate the comparison message.
     * @return string
     */
    public function getMessageWithComparisonDiff(): string
    {
        $previous = $this;

        while ($previous = $previous->getPrevious()) {
            if ($previous instanceof \SebastianBergmann\Comparator\ComparisonFailure) {
                return trim($this->getMessage() . "\n" . $previous->getDiff());
            }
        }

        return $this->getMessage();
    }
}
