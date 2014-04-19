<?php
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010-2012, Mike Lively <m@digitalsandwich.com>
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
 * A matcher to validate that an argument equals a particular value.
 *
 * This matcher utilizes the same functionality as non-strict equality in php, in other words '=='
 */
class Phake_Matchers_EqualsMatcher extends Phake_Matchers_SingleArgumentMatcher
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Pass in the value that the upcoming arguments is expected to equal.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns whether or not the passed argument matches the matcher.
     */
    protected  function matches(&$argument)
    {
        return $this->compareValues($this->value, $argument);
    }

    private function compareValues($val1, $val2, &$tested = array())
    {
        if (is_object($val1) && is_object($val2)) {
            return $this->compareObjects($val1, $val2, $tested);
        } elseif (is_array($val1) && is_array($val2)) {
            return $this->compareArrays($val1, $val2, $tested);
        } else {
            return $val1 == $val2;
        }
    }

    private function compareObjects($obj1, $obj2, &$tested = array())
    {
        if (get_class($obj1) != get_class($obj2)) {
            return false;
        }

        if ($obj1 === $obj2) {
            return true;
        }

        if (in_array(array($obj1, $obj2), $tested, true)) {
            return true;
        }

        $tested[] = array($obj1, $obj2);
        $tested[] = array($obj2, $obj1);

        return $this->compareArrays((array)$obj1, (array)$obj2, $tested);
    }

    private function compareArrays(array $arr1, array $arr2, array &$tested)
    {
        if (count($arr1) != count($arr2)) {
            return false;
        }

        foreach ($arr1 as $key => $value) {
            if (!array_key_exists($key, $arr2)) {
                return false;
            }

            if (!$this->compareValues($value, $arr2[$key], $tested)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $converter = new Phake_String_Converter();
        return "equal to {$converter->convertToString($this->value)}";
    }
}
