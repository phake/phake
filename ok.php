<?php

namespace Foo;

require __DIR__ . '/vendor/autoload.php';

use Phake;

class Foo {
    public string $foo = 'Unset' {
        set => $value;
    }
}

Phake::setMockLoader(new Phake\ClassGenerator\FileLoader(sys_get_temp_dir()));

$mock = Phake::mock(Foo::class);

Phake::when($mock)->foo->get()->thenReturn('Hello Pierrick')->thenReturn('Hello Ethan')->thenReturnCallback(fn() => 'Hello Annie')->thenCallParent();
var_dump($mock->foo);
var_dump($mock->foo);
var_dump($mock->foo);

Phake::when($mock)->foo->get()->thenCallParent();
Phake::when($mock)->foo->set('lol')->thenCallParent();
$mock->foo = 'ARG';
var_dump($mock->foo);
Phake::verifyNoFurtherInteraction($mock);
var_dump($mock->foo);
//$mock->foo = 'lol';

//Phake::verify($ok)->foo->set(Phake::capture($arg)->when("lol"));
//var_dump($arg);
