<?php
namespace DigitalSandwich\Phake\Generator;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ClassInfo
{
	private $name;
	
	private $mockedClassName;
	
	public function __construct($className, $mockedClassName = NULL)
	{
		$this->name = $className;
		$this->mockedClassName = $mockedClassName;
	}
	
	public function getClassName()
	{
		return $this->name;
	}
	
	public function getMockedClassName()
	{
		return $this->mockedClassName;
	}
}

?>
