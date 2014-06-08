.. _answers:

*******
Answers
*******

In all of the examples so far, the ``thenReturn()`` answer is being used. There are other answers that are remarkably
useful writing your tests.

Throwing Exceptions
===================

Exception handling is a common aspect of most object oriented systems that should be tested. The key to being able to
test your exception handling is to be able to control the throwing of your exceptions. Phake allows this using the
``thenThrow()`` answer. This answer allows you to throw a specific exception from any mocked method. Below is an
example of a piece of code that catches an exception from the method foo() and then logs a message with the exception
message.

.. code-block:: php

    class MyClass
    {
        private $logger;

        public function __construct(LOGGER $logger)
        {
            $this->logger = $logger;
        }

        public function processSomeData(MyDataProcessor $processor, MyData $data)
        {
            try
            {
                $processor->process($data);
            }
            catch (Exception $e)
            {
                $this->logger->log($e->getMessage());
            }
        }
    }

In order to test this we must mock ``foo()`` so that it throws an exception when it is called. Then we can verify that
``log()`` is called with the appropriate message.

.. code-block:: php

    class MyClassTest extends PHPUnit_Framework_TestCase
    {
        public function testProcessSomeDataLogsExceptions()
        {
            $logger = Phake::mock('LOGGER');
            $data = Phake::mock('MyData');
            $processor = Phake::mock('MyDataProcessor');

            Phake::when($processor)->process($data)->thenThrow(new Exception('My error message!'));

            $sut = new MyClass($logger);
            $sut->processSomeData($processor, $data);

            //This comes from the exception we created above
            Phake::verify($logger)->log('My error message!');
        }
    }


Calling the Parent
==================

Phake provides the ability to allow calling the actual method of an object on a method by method
basis by using the ``thenCallParent()`` answer. This will result in the actual method being called.
Consider the following class.

.. code-block:: php

    class MyClass
    {
        public function foo()
        {
            return '42';
        }
    }

The ``thenCallParent()`` answer can be used here to ensure that the actual method in the class is
called resulting in the value 42 being returned from calls to that mocked method.

.. code-block:: php

    class MyClassTest extends PHPUnit_Framework_TestCase
    {
        public function testCallingParent()
        {
            $mock = Phake::mock('MyClass');
            Phake::when($mock)->foo()->thenCallParent();

            $this->assertEquals(42, $mock->foo());
        }
    }

Please avoid using this answer as much as possible especially when testing newly written code. If you find yourself
requiring a class to be only partially mocked then that is a code smell for a class that is likely doing too much. An
example of when this is being done is why you are testing a class that has a singular method that has a lot of side
effects that you want to mock while you allow the other methods to be called as normal. In this case that method that
you are desiring to mock should belong to a completely separate class. It is obvious by the very fact that you are able
to mock it without needing to mock other messages that it performs a different function.

Even though partial mocking should be avoided with new code, it is often very necessary to allow creating tests while
refactoring legacy code, tests involving 3rd party code that can’t be changed, or new tests of already written code
that cannot yet be changed. This is precisely the reason why this answer exists and is also why it is not the default
answer in Phake.

Capturing a Return Value
========================

Another tool in Phake for testing legacy code is the ``captureReturnTo()`` answer. This performs a function similar to
argument capturing, however it instead captures what the actual method of a mock object returns to the variable passed
as its parameter. Again, this should never be needed if you are testing newly written code. However I have ran across
cases several times where legacy code calls protected factory methods and the result of the method call is never
exposed. This answer gives you a way to access that variable to ensure that the factory was called and is operating
correctly in the context of your method that is being tested.

Custom Answers
==============

While the answers provided in Phake should be able to cover most of the scenarios you will run into when using mocks in
your unit tests there may occasionally be times when you need more control over what is returned from your mock
methods. When this is the case, you can use a custom answer. The easiest way to create a custom answer is by extending
``Phake_Matchers_SingleArgumentMatcher``. This class was created to allow for easy extension of matchers that will need
to match a single argument in a method call.

When extending ``Phake_Matchers_SingleArgumentMatcher`` there are two methods that must be implemented:
 ``__toString()`` will define how your matcher is reported on in failed matches and ``matches(&$argument)`` will be
 used to determine whether or not your matcher was successful. So, we could create a custom matcher that determined if
 a value is greater than another using the following class:

 .. code-block:: php

    class IsGreaterThan extends Phake_Matchers_SingleArgumentMatcher
    {
        private $value;

        public function __construct($value)
        {
            $this->value = $value;
        }

        public function __toString()
        {
            return '<greater than ' . $this->value . '>';
        }

        protected function matches(&$argument)
        {
            return $argument > $this->value;
        }
    }

You can then use this custom matcher in your ``Phake::when()`` or ``Phake::verify()`` calls:

.. code-block:: php

    $mock->foo(20);
    Phake::verify($mock)->foo(new IsGreaterThan(10));
