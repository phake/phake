<?php

require __DIR__ . '/vendor/autoload.php';

class Foo {
    public string $foo = 'Unset' {
        get => $this->foo;
        set => $value;
    }
}


Phake::setMockLoader(new Phake\ClassGenerator\FileLoader(sys_get_temp_dir()));

$ok = Phake::mock(Foo::class);

//Phake::when($ok)->foo->get()->thenReturn('Hello Pierrick')->thenReturn('Hello Ethan')->thenReturnCallback(fn() => 'Hello Annie')->thenCallParent();
//var_dump($ok->foo);
//var_dump($ok->foo);
//var_dump($ok->foo);
//var_dump($ok->foo);
//var_dump($ok->foo);
//Phake::when($ok)->foo->get()->thenReturnCallback(fn () => 20);
//Phake::when($ok)->foo->get()->thenReturn('Hello world')->thenCallParent();
//Phake::when($ok)->foo->get()->thenThrow(new \Exception);
//var_dump($ok->foo);
//var_dump($ok->foo);

//Phake::when($ok)->foo->set('ok')->thenSetCallback(fn($x) => $x);
//Phake::when($ok)->foo->set('ok')->thenSet('toto');
Phake::when($ok)->foo->get()->thenCallParent();
Phake::when($ok)->foo->set('lol')->thenCallParent();
$ok->foo = 'foo';
var_dump($ok->foo);
$ok->foo = 'lol';
var_dump('END', $ok->foo);

//Phake::verify($ok)->foo->set('toto');
//Phake::verify($ok)->foo->get();


