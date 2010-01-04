<?php
namespace DigitalSandwich\Phake\Generator;

class ClassGenerator
{
	/**
	 * @var ClassTemplates\MockClass 
	 */
	private $defaultMethodImplementation;

	public function __construct(ClassTemplates\IClassTemplate $methodImplementor)
	{
		$this->defaultMethodImplementation = $methodImplementor;
	}

	public function generate(ClassInfo $class)
	{
		if (class_exists($class->getClassName()))
		{
			throw new \Exception("The requested class name [{$class->getClassName()}] already exists.");
		}

		$parent = $class->getMockedClassName();
		if ($parent !== NULL && !class_exists($parent, TRUE) && !interface_exists($parent, TRUE))
		{
			throw new \Exception("Parent class / interface [{$parent}] does not exist.");
		}

		eval($this->getClassPhp($class));
	}

	protected function getClassPhp(ClassInfo $class)
	{
		$parentRflc = NULL;
		if (class_exists($class->getMockedClassName(), TRUE) || interface_exists($class->getMockedClassName(), TRUE))
		{
			$parentRflc = new \ReflectionClass($class->getMockedClassName());
		}

		$parent = NULL;
		$interface = NULL;
		var_dump($class->getMockedClassName(), class_exists($class->getMockedClassName()));
		if (!empty($parentRflc) && $parentRflc->isInterface())
		{
			$interface = $class->getMockedClassName();
		}
		elseif (!empty($parentRflc))
		{
			$parent = $class->getMockedClassName();
		}

		return "
{$this->defaultMethodImplementation->getClassHeader($class->getClassName(), $parent, $interface)}
{
{$this->getClassBody($class)}
}";
	}

	protected function getClassBody(ClassInfo $class)
	{
		$body = $this->getConstructor();
		$overrides = array();
		if ($class->getMockedClassName() !== NULL)
		{
			$reflectionClass = new \ReflectionClass($class->getMockedClassName());
			$body .= $this->getClassMethodImplementations($reflectionClass->getMethods(), $overrides);
		}

		$body .= $this->getInterceptorApiMethods($overrides)
			. $this->getVerifierApiMethods($overrides);

		return $body;
	}

	protected function getClassMethodImplementations(array $methods, array &$overrides)
	{
		$classBody = '';
		foreach ($methods as $method)
		{
			$overrides[] = $method->name;
			if ($method->name == '__construct' || stripos($method->name, '__phake__') !== FALSE)
			{
				continue;
			}

			/* @var $method ReflectionMethod */
			$classBody .= $this->getMethodImplementation($method);
		}

		return $classBody;
	}

	protected function getMethodImplementation(\ReflectionMethod $method)
	{
		if ($method->isFinal())
		{
			return '';
		}

		$modifiers = implode(' ', \Reflection::getModifierNames($method->getModifiers() & ~\ReflectionMethod::IS_ABSTRACT));

		$encodedMethodName = $this->getEncodedValue($method->getName());
		return "
		{$modifiers} function {$method->getName()}({$this->getMethodParameterString($method)})
		{
			{$this->getMethodStandardImplementation($encodedMethodName, TRUE)}
			{$this->defaultMethodImplementation->getImplementation($method)}
		}
";
	}

	protected function getMethodStandardImplementation($encodedMethodName, $isOverride)
	{
		return $isOverride ? "\$this->__PHAKE__inInternalCall = TRUE;
			\$this->__PHAKE__recordCall({$encodedMethodName}, func_get_args());
			\$__PHAKE__plan = \$this->__PHAKE__getMatchingPlan({$encodedMethodName}, func_get_args());
			if (\$__PHAKE__plan)
			{
				return \$__PHAKE__plan->getAnswer();
			}
			\$this->__PHAKE__inInternalCall = FALSE;
" : "";
	}

	protected function getMethodParameterString(\ReflectionMethod $method)
	{
		$parameterStrings = array();
		foreach ($method->getParameters() as $parameter)
		{
			$parameterStrings[] = $this->getParameterString($parameter);
		}

		return implode(', ', $parameterStrings);
	}

	protected function getParameterString(\ReflectionParameter $parameter)
	{
		$parameterStr = $this->getParameterTypeHint($parameter);

		if ($parameter->isPassedByReference())
		{
			$parameterStr .= '&';
		}

		$parameterStr .= "\${$parameter->getName()}";

		if ($parameter->isOptional())
		{
			$parameterStr .= ' = ' . $this->getEncodedValue(
				$parameter->isDefaultValueAvailable()
				? $parameter->getDefaultValue()
				: ''
			);
		}

		return $parameterStr;
	}

	protected function getParameterTypeHint(\ReflectionParameter $parameter)
	{
		$class = $parameter->getClass();

		if ($class)
		{
			return "{$class->getName()} ";
		}
		elseif ($parameter->isArray())
		{
			return 'array ';
		}
		else
		{
			return '';
		}
	}

	protected function getEncodedValue($value)
	{
		ob_start();
		var_export($value);
		return ob_get_clean();
	}

	protected function getConstructor()
	{
		return '
	private $__PHAKE__planContainer;
	private $__PHAKE__callRecorder;
	private $__PHAKE__inInternalCall = false;

	public function __construct(
		DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer $planContainer,
		DigitalSandwich\Phake\Verifier\CallRecorder $callRecorder
	)
	{
		$this->__PHAKE__planContainer = $planContainer;
		$this->__PHAKE__callRecorder = $callRecorder;
	}
';
	}

	protected function getInterceptorApiMethods(array $overrides)
	{
		return "
	public function __PHAKE__addMethodPlan(DigitalSandwich\Phake\CallInterceptor\MethodPlan \$plan, DigitalSandwich\Phake\Matcher\MethodMatcher \$matcher)
	{
		if (!\$this->__PHAKE__inInternalCall)
		{
			{$this->getMethodStandardImplementation("'__PHAKE__addMethodPlan'", $this->isMethodOverridden('__PHAKE__addMethodPlan', $overrides))}
		}
		\$this->__PHAKE__planContainer->__PHAKE__addMethodPlan(\$plan, \$matcher);
	}

	public function __PHAKE__getMatchingPlan(\$method, \$arguments)
	{
		if (!\$this->__PHAKE__inInternalCall)
		{
			{$this->getMethodStandardImplementation("'__PHAKE__getMatchingPlan'", $this->isMethodOverridden('__PHAKE__getMatchingPlan', $overrides))}
		}
		return \$this->__PHAKE__planContainer->__PHAKE__getMatchingPlan(\$method, \$arguments);
	}
";
	}

	protected function getVerifierApiMethods(array $overrides)
	{
		return "
	public function __PHAKE__verifyMethodCall(DigitalSandwich\Phake\Matcher\MethodMatcher \$matcher)
	{
		if (!\$this->__PHAKE__inInternalCall)
		{
			{$this->getMethodStandardImplementation("'__PHAKE__verifyMethodCall'", $this->isMethodOverridden('__PHAKE__verifyMethodCall', $overrides))}
		}
		return \$this->__PHAKE__callRecorder->__PHAKE__verifyMethodCall(\$matcher);
	}

	protected function __PHAKE__recordCall(\$method, array \$arguments)
	{
		if (!\$this->__PHAKE__inInternalCall)
		{
			{$this->getMethodStandardImplementation("'__PHAKE__recordCall'", $this->isMethodOverridden('__PHAKE__recordCall', $overrides))}
		}
		return \$this->__PHAKE__callRecorder->recordCall(\$method, \$arguments);
	}

	public function __PHAKE__getCallRecord()
	{
		if (!\$this->__PHAKE__inInternalCall)
		{
			{$this->getMethodStandardImplementation("'__PHAKE__getCallRecord'", $this->isMethodOverridden('__PHAKE__getCallRecord', $overrides))}
		}
		return \$this->__PHAKE__callRecorder->getCallRecord();
	}
";
	}
	
	public function isMethodOverridden($method, array $overrides)
	{
		return in_array($method, $overrides);
	}
}

?>
