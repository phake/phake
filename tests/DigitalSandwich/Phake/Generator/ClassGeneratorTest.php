<?php
namespace DigitalSandwich\Phake\Generator;

use DigitalSandwich\Phake\Phake;

require_once('DigitalSandwich/Phake/Generator/ClassInfo.php');
require_once('DigitalSandwich/Phake/Generator/ClassGenerator.php');
require_once('DigitalSandwich/Phake/Generator/ClassTemplates/MockClass.php');
require_once('DigitalSandwich/Phake/Generator/ClassTemplates/SpyClass.php');
require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlan.php');
require_once('DigitalSandwich/Phake/CallInterceptor/IMethodPlanContainer.php');
require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');
require_once('DigitalSandwich/Phake/Verifier/CallRecorder.php');

interface TestInterface1
{

}

interface TestInterface2
{
	public function foo();

	public function foo2($parm1, $parm2, $parm3);

	public function foo3(\stdClass $parm1, array $parm2);

	public function foo4($parm1 = 'test', $parm2 = 23, $parm3 = array(), \stdClass $parm4 = NULL);

	public function foo5($parm1, &$parm2);
}

abstract class AbstractTestClass1 implements TestInterface2
{
	abstract public function foo6();
}

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
	public function testBasicGenerate()
	{
		$this->generateClass('TestClass_1');
		$this->assertTrue(class_exists('TestClass_1', false), "Class still does not exist.");
		
		$class = new \ReflectionClass('TestClass_1');
		$this->assertFalse($class->getParentClass(), "The class is inheriting something");
		$this->assertEquals(array(
			'DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer'
				=> new \ReflectionClass('DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer'),
			'DigitalSandwich\Phake\Verifier\IVerifiable'
				=> new \ReflectionClass('DigitalSandwich\Phake\Verifier\IVerifiable'),
			), $class->getInterfaces(), "The class is implementing something");
	}

	/**
	 * @expectedException Exception
	 */
	public function testGenerateThrowsWhenClassExists()
	{
		$this->generateClass('stdClass');
	}

	/**
	 * @expectedException Exception
	 */
	public function testGenerateThrowsWhenParentClassDoesNotExist()
	{
		$this->generateClass('NotGoingToBeCreated', 'NonExistantClass');
	}

	public function testGenerateExtendsClass()
	{
		$this->generateClass('TestClass_2', 'Exception');

		$class = new \ReflectionClass('TestClass_2');
		$this->assertTrue($class->isSubclassOf('Exception'), "Generated class does not inherit exception.");
	}

	public function testGenerateImplementsInterface()
	{
		$this->generateClass('TestClass_3', __NAMESPACE__ . '\TestInterface1');

		$class = new \ReflectionClass('TestClass_3');
		$this->assertTrue($class->implementsInterface(__NAMESPACE__ . '\TestInterface1'), "Generated class does not implement interface.");
	}

	public function testGenerateImplementsInterfaceMethods()
	{
		$this->generateClass('TestClass_4', __NAMESPACE__ . '\TestInterface2');

		$class = new \ReflectionClass('TestClass_4');
		$this->assertTrue($class->hasMethod('foo'), "Generated class does not implement foo method.");
	}

	public function testGenerateImplementsInterfaceMethodsWithParameters()
	{
		$this->generateClass('TestClass_5', __NAMESPACE__ . '\TestInterface2');

		$method = new \ReflectionMethod('TestClass_5', 'foo2');
		$this->assertEquals(3, $method->getNumberOfParameters());
	}

	public function testGenerateImplementsInterfaceMethodsWithTypeHintedParameters()
	{
		$this->generateClass('TestClass_6', __NAMESPACE__ . '\TestInterface2');

		$method = new \ReflectionMethod('TestClass_6', 'foo3');
		$parameters = $method->getParameters();

		$this->assertEquals('stdClass', $parameters[0]->getClass()->getName());
	}

	public function testGenerateImplementsInterfaceMethodsWithDefaultValuedParameters()
	{
		$this->generateClass('TestClass_7', __NAMESPACE__ . '\TestInterface2');

		$method = new \ReflectionMethod('TestClass_7', 'foo4');
		$parameters = $method->getParameters();

		$this->assertSame('test', $parameters[0]->getDefaultValue());
		$this->assertSame(23, $parameters[1]->getDefaultValue());
		$this->assertSame(array(), $parameters[2]->getDefaultValue());
		$this->assertSame(NULL, $parameters[3]->getDefaultValue());
	}

	public function testGenerateImplementsInterfaceMethodsWithReferenceParameters()
	{
		$this->generateClass('TestClass_8', __NAMESPACE__ . '\TestInterface2');

		$method = new \ReflectionMethod('TestClass_8', 'foo5');
		$parameters = $method->getParameters();

		$this->isTrue($parameters[1]->isPassedByReference());
	}

	public function testGenerateImplementsExtendedClassInterfaces()
	{
		$this->generateClass('TestClass_9', __NAMESPACE__ . '\AbstractTestClass1');

		$this->assertTrue(method_exists('TestClass_9', 'foo5'));
		$this->assertTrue(method_exists('TestClass_9', 'foo6'));
	}

	public function testGenerateImplementsInterceptableInterface()
	{
		$this->generateClass('TestClass_10', __NAMESPACE__ . '\AbstractTestClass1');

		$class = new \ReflectionClass('TestClass_10');
		$this->assertTrue($class->implementsInterface('DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer'));
	}

	public function testGenerateSetsAggregateMethodPlanContainer()
	{
		$this->generateClass('TestClass_11', __NAMESPACE__ . '\AbstractTestClass1');

		$class = $this->instantiateClass('\TestClass_11', $methodPlanContainer, $methodPlan, $matcher, $junk = NULL);

		$class->__PHAKE__addMethodPlan($methodPlan, $matcher);

		Phake::verify($methodPlanContainer)->__PHAKE__addMethodPlan($methodPlan, $matcher);
	}

	public function testGeneratedMethodSearchesMethodPlanContainer()
	{
		$this->generateClass('TestClass_12', __NAMESPACE__ . '\AbstractTestClass1');

		$class = $this->instantiateClass('\TestClass_12', $methodPlanContainer, $methodPlan, $matcher, $junk = NULL);

		Phake::when($methodPlan)->getAnswer()->thenReturn(42);

		Phake::when($methodPlanContainer)
			->__PHAKE__getMatchingPlan('foo6', array('bar', 123))
			->thenReturn($methodPlan);

		$this->assertEquals(42, $class->foo6('bar', 123));

		Phake::verify($methodPlanContainer)->__PHAKE__getMatchingPlan('foo6', array('bar', 123));
	}

	public function testGenerateImplementsVerifiableInterface()
	{
		$this->generateClass('TestClass_13', __NAMESPACE__ . '\AbstractTestClass1');

		$class = new \ReflectionClass('TestClass_13');
		$this->assertTrue($class->implementsInterface('DigitalSandwich\Phake\Verifier\IVerifiable'));
	}

	public function testGeneratedClassCallsCallRecorderToVerify()
	{
		$this->generateClass('TestClass_14', __NAMESPACE__ . '\AbstractTestClass1');

		$class = $this->instantiateClass('\TestClass_14', $methodPlanContainer, $methodPlan, $matcher, $callRecorder);

		$class->__PHAKE__verifyMethodCall($matcher);

		Phake::verify($callRecorder)->__PHAKE__verifyMethodCall($matcher);
	}

	public function testClassCallsAreRecorded()
	{
		$this->generateClass('TestClass_15', __NAMESPACE__ . '\AbstractTestClass1');

		$class = $this->instantiateClass('\TestClass_15', $methodPlanContainer, $methodPlan, $matcher, $callRecorder);

		$class->foo6('bar');

		Phake::verify($callRecorder)->recordCall('foo6', array('bar'));
	}

	public function testCallRecordsAccessible()
	{
		$this->generateClass('TestClass_16', __NAMESPACE__ . '\AbstractTestClass1');

		$class = $this->instantiateClass('\TestClass_16', $methodPlanContainer, $methodPlan, $matcher, $callRecorder);

		$expectedCallRecord = array(array('method' => 'foo', 'arguments' => array()));

		Phake::when($callRecorder)->getCallRecord()->thenReturn($expectedCallRecord);

		$this->assertEquals($expectedCallRecord, $class->__PHAKE__getCallRecord());
	}

	public function testSpyStrategyCallsParentClass()
	{
		$this->generateClass('TestClass_17', __NAMESPACE__ . '\AbstractTestClass1');
		$this->generateClass('TestClass_18', __NAMESPACE__ . '\AbstractTestClass1', FALSE);

		$observed = $this->instantiateClass('\TestClass_17', $methodPlanContainer, $methodPlan, $matcher, $callRecorder);
		$spy = $this->instantiateClass('\TestClass_18', $methodPlanContainer, $methodPlan, $matcher, $callRecorder, $observed);

		$spy->foo6();

		Phake::verify($observed)->foo6();
	}

	protected function generateClass($className, $parentClass = NULL, $useMock = TRUE)
	{
		$classInfo = new ClassInfo($className, $parentClass);
		$generator = new ClassGenerator($useMock ? new ClassTemplates\MockClass() : new ClassTemplates\SpyClass());

		$generator->generate($classInfo);

		return $generator;
	}

	protected function instantiateClass($className, &$methodPlanContainer, &$methodPlan, &$matcher, &$callRecorder, $observedObject = NULL)
	{
		$methodPlan = Phake::mock('DigitalSandwich\Phake\CallInterceptor\MethodPlan');
		$matcher = Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher');

		$methodPlanContainer = Phake::mock('DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer');
		$callRecorder = Phake::mock('DigitalSandwich\Phake\Verifier\CallRecorder');

		if ($observedObject === NULL)
		{
			return new $className($methodPlanContainer, $callRecorder);
		}
		else
		{
			return new $className($methodPlanContainer, $callRecorder, $observedObject);
		}
	}
}

?>
