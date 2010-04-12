<?php
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010, Mike Lively <mike.lively@sellingsource.com>
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

require_once 'Phake/Stubber/AnswerBinder.php';
require_once 'Phake/Proxies/AnswerBinderProxy.php';
require_once 'Phake/Matchers/MethodMatcher.php';
require_once 'Phake/Matchers/PHPUnitConstraintAdapter.php';
require_once 'Phake/Matchers/HamcrestMatcherAdapter.php';
require_once 'Phake/Matchers/EqualsMatcher.php';

/**
 * A proxy to handle stubbing a method on a mock object.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_Proxies_StubberProxy
{
	/**
	 * @var Phake_Stubber_IStubbable
	 */
	private $obj;

	/**
	 * @param Phake_Stubber_IStubbable $obj
	 */
	public function __construct(Phake_Stubber_IStubbable $obj)
	{
		$this->obj = $obj;
	}

	/**
	 * A magic call to instantiate an Answer Binder Proxy.
	 * @param string $method
	 * @param array $arguments
	 * @return Phake_Proxies_AnswerBinderProxy
	 */
	public function __call($method, array $arguments)
	{
		$matcher = new Phake_Matchers_MethodMatcher($method, $this->translateArguments($arguments));
		$binder = new Phake_Stubber_AnswerBinder($this->obj, $matcher);
		return new Phake_Proxies_AnswerBinderProxy($binder);
	}

	/**
	 * Takes an array of arguments and creates an array of matchers representing those arguments
	 * @param array $arguments
	 */
	private function translateArguments(array $arguments)
	{
		$matchers = array();

		foreach ($arguments as $argument)
		{
			if ($argument instanceof Phake_Matchers_IArgumentMatcher)
			{
				$matchers[] = $argument;
			}
			elseif (class_exists('PHPUnit_Framework_Constraint')
							&& $argument instanceof PHPUnit_Framework_Constraint)
			{
				$matchers[] = new Phake_Matchers_PHPUnitConstraintAdapter($argument);
			}
			elseif (interface_exists('Hamcrest_Matcher')
							&& $argument instanceof Hamcrest_Matcher)
			{
				$matchers[] = new Phake_Matchers_HamcrestMatcherAdapter($argument);
			}
			else
			{
				$matchers[] = new Phake_Matchers_EqualsMatcher($argument);
			}
		}

		return $matchers;
	}
}
?>
