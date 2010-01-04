<?php
namespace DigitalSandwich\Phake\Generator\ClassTemplates;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('DigitalSandwich/Phake/Generator/ClassTemplates/BaseClass.php');
require_once('DigitalSandwich/Phake/Generator/ClassTemplates/MockClass.php');

class SpyClass extends BaseClass
{
	public function getImplementation(\ReflectionMethod $method)
	{
		if ($method->isAbstract())
		{
			$MockClass = new MockClass();
			return $MockClass->getImplementation($method);
		}
		else
		{
			return 'call_user_func_array(array(\'parent\', __FUNCTION__), func_get_args())';
		}
	}
}

?>
