<?php
namespace DigitalSandwich\Phake\Generator\ClassTemplates;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

interface IClassTemplate
{
	public function getClassHeader($className, $parentClass, $implementedInterface);

	public function getImplementation(\ReflectionMethod $method);
}

?>
