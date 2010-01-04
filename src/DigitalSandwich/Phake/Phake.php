<?php
namespace DigitalSandwich\Phake;

require_once('DigitalSandwich/Phake/Generator/ClassGenerator.php');
require_once('DigitalSandwich/Phake/Generator/ClassTemplates/MockClass.php');
require_once('DigitalSandwich/Phake/Generator/ClassInfo.php');
require_once('DigitalSandwich/Phake/Verifier/Verifier.php');
require_once('DigitalSandwich/Phake/Verifier/CallRecorder.php');
require_once('DigitalSandwich/Phake/CallInterceptor/Interceptor.php');
require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlanContainer.php');
require_once('DigitalSandwich/Phake/Matcher/AtLeastInvocations.php');

//TODO: add spy
class Phake
{
	const ANY_PARAMETERS = 'ANY_PARAMETERS';

	//TODO: Add mock annotation
	public static function mock($className)
	{
		$generator = static::getClassGenerator();
		$classParts = explode('\\', $className);

		$newClass = array_pop($classParts) . '_' . uniqid();

		$classInfo = new Generator\ClassInfo($newClass, $className);

		$generator->generate($classInfo);

		return new $newClass(static::getMethodPlanContainer(), static::getCallRecorder());
	}

	//TODO: Add Argument Capturing
	//TODO: Add Counts of Invocations
	//TODO: Add Call Order Verification
	//TODO: Add verification for not interaction at all
	public static function verify(Verifier\IVerifiable $class, Matcher\IInvocationMatcher $invocationMatcher = NULL)
	{
		if ($invocationMatcher === NULL)
		{
			$invocationMatcher = new Matcher\AtLeastInvocations(1);
		}
		return new Verifier\Verifier($class, $invocationMatcher);
	}

	//TODO: Stubbing only listens to the most recent stub (match in reverse)
	//TODO: Stub consecutive calls
	//TODO: Stub callbacks
	public static function when(CallInterceptor\IMethodPlanContainer $class)
	{
		return new CallInterceptor\Interceptor($class);
	}

	public static function getCallRecord($class)
	{
		return $class->__PHAKE__getCallRecord();
	}

	protected static function getClassGenerator()
	{
		return new Generator\ClassGenerator(new Generator\ClassTemplates\MockClass());
	}

	protected static function getMethodPlanContainer()
	{
		return new CallInterceptor\MethodPlanContainer();
	}

	protected static function getCallRecorder()
	{
		return new Verifier\CallRecorder();
	}
}
?>
