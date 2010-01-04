<?php
namespace DigitalSandwich\Phake\Generator\ClassTemplates;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('DigitalSandwich/Phake/Generator/ClassTemplates/MockClass.php');

class MockClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGetImplementation()
	{
		$implementor = new MockClass();
		$this->assertEquals('return null;', strtolower(trim($implementor->getImplementation(new \ReflectionMethod(__NAMESPACE__ . '\MockClass', 'getImplementation')))));
	}
}

?>
