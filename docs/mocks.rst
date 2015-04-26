Creating Mocks
==============

The ``Phake::mock()`` method is how you create new test doubles in Phake. You pass in the class name of what you would
like to mock.

.. code-block:: php

    $mock = Phake::mock('ClassToMock');

The ``$mock`` variable is now an instance of a generated class that inherits from ``ClassToMock`` with hooks that allow
you to force functions to return known values. By default, all methods on a mock object will return null. This behavior
can be overridden on a per method and even per parameter basis. This will be covered in depth in
:ref:`method-stubbing-section`.

The mock  will also record all calls made to this class so that you can later verify that specific methods were called
with the proper parameters. This will be covered in depth in :ref:`method-verification-section`.

Partial Mocks
-------------

When testing legacy code, you may find that a better default behavior for the methods is to actually call the original
method. This can be accomplished by stubbing each of the methods to return ``thenCallParent()``. You can learn more
about this in :ref:`then-call-parent`.

While this is certainly possible, you may find it easier to just use a partial mock in Phake. Phake partial mocks also
allow you to call the actual constructor of the class being mocked. They are created using ``Phake::partialMock()``.
Like ``Phake::mock()``, the first parameter is the name of the class that you are mocking. However, you can pass
additional parameters that will then be passed as the respective parameters to that class’ constructor. The other
notable feature of a partial mock in Phake is that its default answer is to pass the call through to the parent as if
you were using ``thenCallParent()``.

Consider the following class that has a method that simply returns the value passed into the constructor.

.. code-block:: php

    class MyClass
    {
        private $value;

        public __construct($value)
        {
            $this->value = $value;
        }

        public function foo()
        {
            return $this->value;
        }
    }

Using ``Phake::partialMock()`` you can instantiate a mock object that will allow this object to function
as designed while still allowing verification as well as selective stubbing of certain calls.
Below is an example that shows the usage of ``Phake::partialMock()``.

.. code-block:: php

    class MyClassTest extends PHPUnit_Framework_TestCase
    {
        public function testCallingParent()
        {
            $mock = Phake::partialMock('MyClass', 42);

            $this->assertEquals(42, $mock->foo());
        }
    }

Again, partial mocks should not be used when you are testing new code. If you find yourself using them be sure to
inspect your design to make sure that the class you are creating a partial mock for is not doing too much.

Calling Private and Protected Methods on Mocks
----------------------------------------------
Beginning in Phake 2.1 it is possible to invoke protected and private methods on your mocks using Phake. When you mock
a class, the mocked version will retain the same visibility on each of its functions as you would have had on your
original class. However, using ``Phake::makeVisible()`` and ``Phake::makeStaticsVisible()`` you can allow direct
invocation of instance methods and static methods accordingly. Both of these methods accept a mock object as its only
parameter and returns a proxy class that you can invoke the methods on.

.. code-block:: php

    class MyClass
    {
        private function foo()
        {
        }

        private static function bar()
        {
        }
    }

Given the class above, you can invoke both private methods with the code below.

.. code-block:: php

    $mock = Phake::mock('MyClass');

    Phake::makeVisible($mock)->foo();

    Phake::makeStaticVisible($mock)->bar();

    //Both calls below will STILL fail
    $mock->foo();
    $mock::bar();

As you can see above when using the static variant you still call the method as though it were an instance method. The
other thing to take note of is that there is no modification done on $mock itself. If you use ``Phake::makeVisible()``
you will only be able to make those private and protected calls off of the return of that method itself.