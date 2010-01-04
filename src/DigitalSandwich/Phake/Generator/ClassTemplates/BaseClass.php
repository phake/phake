<?php
namespace DigitalSandwich\Phake\Generator\ClassTemplates;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('DigitalSandwich/Phake/Generator/ClassTemplates/IClassTemplate.php');

abstract class BaseClass implements IClassTemplate
{
	public function getClassHeader($className, $parentClass, $implementedInterface)
	{
		$phpstr = "class {$className}";

		if (!empty($parentClass))
		{
			$phpstr .= " extends {$parentClass}";
		}

		$interfaces = $this->getApiInterfaces();
		if (!empty($implementedInterface) && !in_array($implementedInterface, $interfaces))
		{
			$interfaces[] = $implementedInterface;
		}

		$phpstr .= ' implements ' . implode(', ', $interfaces);

		return $phpstr;
	}

	protected function getApiInterfaces()
	{
		return array(
			'DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer',
			'DigitalSandwich\Phake\Verifier\IVerifiable'
		);
	}
}

?>
