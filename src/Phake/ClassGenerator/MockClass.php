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
 * Creates and executes the code necessary to create a mock class.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_ClassGenerator_MockClass
{
    /**
     * @var \Phake_ClassGenerator_ILoader
     */
    private $loader;

    private $reservedWords = array(
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'do',
        'else',
        'elseif',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'interface',
        'instanceof',
        'namespace',
        'new',
        'or',
        'private',
        'protected',
        'public',
        'static',
        'switch',
        'throw',
        'try',
        'use',
        'var',
        'while',
        'xor',
        'die',
        'echo',
        'empty',
        'exit',
        'eval',
        'include',
        'include_once',
        'isset',
        'list',
        'require',
        'require_once',
        'return',
        'print',
        'unset',
        '__halt_compiler'
    );

    /**
     * @param Phake_ClassGenerator_ILoader $loader
     */
    public function __construct(Phake_ClassGenerator_ILoader $loader = null)
    {
        if (empty($loader)) {
            $loader = new Phake_ClassGenerator_EvalLoader();
        }

        $this->loader = $loader;
    }

    /**
     * Generates a new class with the given class name
     *
     * @param string $newClassName    - The name of the new class
     * @param string $mockedClassName - The name of the class being mocked
     *
     * @return NULL
     */
    public function generate($newClassName, $mockedClassName)
    {
        $extends    = '';
        $implements = '';
        $interfaces = array();

        $mockedClass = new ReflectionClass($mockedClassName);

        if (!$mockedClass->isInterface()) {
            $extends = "extends {$mockedClassName}";
        } elseif ($mockedClassName != 'Phake_IMock') {
            $implements = ", $mockedClassName";

            if ($mockedClass->implementsInterface('Traversable') &&
                !$mockedClass->implementsInterface('Iterator') &&
                !$mockedClass->implementsInterface('IteratorAggregate')
            ) {
                if ($mockedClass->getName() == 'Traversable') {
                    $implements = ', Iterator';
                } else {
                    $implements = ', Iterator'.$implements;
                }
                $interfaces = array('Iterator');
            }
        }

        $classDef = "
class {$newClassName} {$extends}
	implements Phake_IMock {$implements}
{
	public \$__PHAKE_callRecorder;

	public \$__PHAKE_stubMapper;

	public \$__PHAKE_defaultAnswer;

	public \$__PHAKE_isFrozen;
	
	public \$__PHAKE_name;
	
	private \$__PHAKE_handlerChain;

	public function __construct(Phake_CallRecorder_Recorder \$callRecorder, Phake_Stubber_StubMapper \$stubMapper, Phake_Stubber_IAnswer \$defaultAnswer, array \$constructorArgs = null)
	{
		\$this->__PHAKE_callRecorder = \$callRecorder;
		\$this->__PHAKE_stubMapper = \$stubMapper;
		\$this->__PHAKE_defaultAnswer = \$defaultAnswer;
		\$this->__PHAKE_isFrozen = FALSE;
		\$this->__PHAKE_name = '{$mockedClassName}';
		\$this->__PHAKE_handlerChain = new Phake_ClassGenerator_InvocationHandler_Composite(array(
			new Phake_ClassGenerator_InvocationHandler_FrozenObjectCheck(new Phake_MockReader()),
			new Phake_ClassGenerator_InvocationHandler_CallRecorder(new Phake_MockReader()),
			new Phake_ClassGenerator_InvocationHandler_MagicCallRecorder(new Phake_MockReader()),
			new Phake_ClassGenerator_InvocationHandler_StubCaller(new Phake_MockReader()),
		));

		
		\$this->__PHAKE_stubMapper->mapStubToMatcher(
			new Phake_Stubber_AnswerCollection(new Phake_Stubber_Answers_StaticAnswer('Mock for {$mockedClassName}')), 
			new Phake_Matchers_MethodMatcher('__toString', array())
		);

		\$this->__PHAKE_stubMapper->mapStubToMatcher(
			new Phake_Stubber_AnswerCollection(new Phake_Stubber_Answers_StaticAnswer(NULL)),
			new Phake_Matchers_AbstractMethodMatcher(new ReflectionClass('{$mockedClassName}'))
		);
			
		{$this->getConstructorChaining($mockedClass)}
	}
	
	public function __destruct() {}

	{$this->generateMockedMethods($mockedClass, $interfaces)}
}
";

        $this->loader->loadClassByString($newClassName, $classDef);
    }

    /**
     * Instantiates a new instance of the given mocked class.
     *
     * @param string                      $newClassName
     * @param Phake_CallRecorder_Recorder $recorder
     * @param Phake_Stubber_StubMapper    $mapper
     * @param Phake_Stubber_IAnswer       $defaultAnswer
     * @param array                       $constructorArgs
     *
     * @return Phake_IMock of type $newClassName
     */
    public function instantiate(
        $newClassName,
        Phake_CallRecorder_Recorder $recorder,
        Phake_Stubber_StubMapper $mapper,
        Phake_Stubber_IAnswer $defaultAnswer,
        array $constructorArgs = null
    ) {
        return new $newClassName($recorder, $mapper, $defaultAnswer, $constructorArgs);
    }

    /**
     * Generate mock implementations of all public and protected methods in the mocked class.
     *
     * @param ReflectionClass   $mockedClass
     * @param ReflectionClass[] $mockedInterfaces
     *
     * @return string
     */
    protected function generateMockedMethods(ReflectionClass $mockedClass, array $mockedInterfaces = array())
    {
        $methodDefs = '';
        $filter     = ReflectionMethod::IS_ABSTRACT | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PUBLIC | ~ReflectionMethod::IS_FINAL;

        $implementedMethods = $this->reservedWords;
        foreach ($mockedClass->getMethods($filter) as $method) {
            if (!$method->isConstructor() && !$method->isDestructor() && !$method->isFinal() && !$method->isStatic(
            ) && !in_array($method->getName(), $implementedMethods)
            ) {
                $implementedMethods[] = $method->getName();
                $methodDefs .= $this->implementMethod($method) . "\n";
            }
        }

        foreach ($mockedInterfaces as $interface) {
            $methodDefs .= $this->generateMockedMethods(new ReflectionClass($interface));
        }

        return $methodDefs;
    }

    /**
     * Creates the constructor implementation
     *
     * @param ReflectionClass $originalClass
     * @return string
     */
    protected function getConstructorChaining(ReflectionClass $originalClass)
    {
        return $originalClass->hasMethod('__construct') ? "

		if (is_array(\$constructorArgs))
		{
			call_user_func_array(array(\$this, 'parent::__construct'), \$constructorArgs);
		}
		" : "";
    }

    /**
     * Creates the implementation of a single method
     *
     * @param ReflectionMethod $method
     *
     * @return string
     */
    protected function implementMethod(ReflectionMethod $method)
    {
        $modifiers = implode(
            ' ',
            Reflection::getModifierNames($method->getModifiers() & ~ReflectionMethod::IS_ABSTRACT)
        );

        $reference = $method->returnsReference() ? '&' : '';

        $methodDef = "
	{$modifiers} function {$reference}{$method->getName()}({$this->generateMethodParameters($method)})
	{
		\$args = array();
		{$this->copyMethodParameters($method)}
		
		\$funcArgs = func_get_args();
		\$answer = \$this->__PHAKE_handlerChain->invoke(\$this, '{$method->getName()}', \$funcArgs, \$args);
		
		if (\$answer instanceof Phake_Stubber_Answers_IDelegator)
		{
			\$delegate = \$answer->getAnswer();
			\$callback = \$delegate->getCallBack(\$this, '{$method->getName()}', \$args);
			\$arguments = \$delegate->getArguments('{$method->getName()}', \$args);

			\$realAnswer = call_user_func_array(\$callback, \$arguments);
			\$answer->processAnswer(\$realAnswer);
			return \$realAnswer;
		}
		else
		{
			\$returnAnswer = \$answer->getAnswer();
			return \$returnAnswer;
		}
	}
";

        return $methodDef;
    }

    /**
     * Generates the code for all the parameters of a given method.
     *
     * @param ReflectionMethod $method
     *
     * @return string
     */
    protected function generateMethodParameters(ReflectionMethod $method)
    {
        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->implementParameter($parameter);
        }

        return implode(', ', $parameters);
    }

    /**
     * Generates the code for all the parameters of a given method.
     *
     * @param ReflectionMethod $method
     *
     * @return string
     */
    protected function copyMethodParameters(ReflectionMethod $method)
    {
        $copies = "\$numArgs = count(func_get_args());\n\t\t";
        foreach ($method->getParameters() as $parameter) {
            $pos = $parameter->getPosition();
            $copies .= "if ({$pos} < \$numArgs) \$args[] =& \$parm{$pos};\n\t\t";
        }

        $copies .= "for (\$i = " . count(
            $method->getParameters()
        ) . "; \$i < \$numArgs; \$i++) \$args[] = func_get_arg(\$i);\n\t\t";

        return $copies;
    }

    /**
     * Generates the code for an individual method parameter.
     *
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    protected function implementParameter(ReflectionParameter $parameter)
    {
        $default = '';
        $type    = '';

        if ($parameter->isArray()) {
            $type = 'array ';
        } elseif ($parameter->getClass() !== null) {
            $type = $parameter->getClass()->getName() . ' ';
        }

        if ($parameter->isDefaultValueAvailable()) {
            $default = ' = ' . var_export($parameter->getDefaultValue(), true);
        } elseif ($parameter->isOptional()) {
            $default = ' = null';
        }

        return $type . ($parameter->isPassedByReference() ? '&' : '') . '$parm' . $parameter->getPosition() . $default;
    }
}
