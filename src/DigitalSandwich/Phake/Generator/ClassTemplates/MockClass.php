<?php
namespace DigitalSandwich\Phake\Generator\ClassTemplates;
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('DigitalSandwich/Phake/Generator/ClassTemplates/BaseClass.php');

class MockClass extends BaseClass
{
	public function getImplementation(\ReflectionMethod $method)
	{
		return 'return NULL;';
	}
}

?>
