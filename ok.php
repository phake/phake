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

$ok = Phake::mock(Foo::class);

Phake::when($ok)->foo->get()->thenReturn('Hello Pierrick')->thenReturn('Hello Ethan')->thenReturnCallback(fn() => 'Hello Annie')->thenCallParent();
var_dump($ok->foo);
var_dump($ok->foo);
var_dump($ok->foo);

Phake::when($ok)->foo->get()->thenCallParent();
Phake::when($ok)->foo->set('lol')->thenCallParent();
$ok->foo = 'ARG';
var_dump($ok->foo);
$ok->foo = 'lol';

Phake::verify($ok)->foo->set(Phake::capture($arg)->when("lol"));
var_dump($arg);
