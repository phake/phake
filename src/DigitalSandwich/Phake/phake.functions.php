<?php
namespace DigitalSandwich\Phake;

require_once('DigitalSandwich/Phake/Phake.php');

function mock($className)
{
	return Phake::mock($className);
}

function verify(IVerifiable $class)
{
	return Phake::verify($class);
}

function when(IMethodPlanContainer $class)
{
	return Phake::when($class);
}

const ANY_PARAMETERS = Phake::ANY_PARAMETERS;

?>
