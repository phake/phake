**********************
Mocking Static Methods
**********************

Phake can be used to verify as well as stub polymorphic calls to static methods. It is important to note that you
cannot verify or stub all static calls. In order for Phake to record or stub a method call, it needs to intercept the
call so that it can record it. Consider the following class

.. code-block:: php

    class StaticCaller
    {
        public function callStaticMethod()
        {
            Foo::staticMethod();
        }
    }

You will not be able to stub or verify the call to Foo::staticMethod() because the call was made directly on the class.
This prevents Phake from seeing that the call was made. However, say you have an abstract class that has an abstract
static method.

.. code-block:: php

    abstract class StaticFactory
    {
        abstract protected static function factory();

        public static function getInstance()
        {
            return static::factory();
        }
    }

In this case, because the ``static::`` keyword will cause the called class to be determined at runtime, you will be able
to verify and stub calls to StaticFactory::factory(). It is important to note that if self::factory() was called then
stubs and verifications would not work, because again the class is determined at compile time with the self:: keyword.
The key thing to remember with testing statics using Phake is that you can only test statics that leverage Late Static
Binding: http://www.php.net/manual/en/language.oop5.late-static-bindings.php

Phake has alternative methods to handle interacting with static methods on your mock class. ``Phake::mock()`` is still
used to create the mock class, but the remaining interactions with static methods use more specialized methods. The
table below shows the Phake methods that have a separate counterpart for interacting with static calls.

+-----------------------------------+-----------------------------------------+
| Instance Method                   | Static Method                           |
+===================================+=========================================+
| ``Phake::when()``                 | ``Phake::whenStatic()``                 |
+-----------------------------------+-----------------------------------------+
| ``Phake::verify()``               | ``Phake::verifyStatic()``               |
+-----------------------------------+-----------------------------------------+
| ``Phake::verifyCallMethodWith()`` | ``Phake::verifyStaticCallMethodWith()`` |
+-----------------------------------+-----------------------------------------+
| ``Phake::whenCallMethodWith()``   | ``Phake::whenStaticCallMethodWith()``   |
+-----------------------------------+-----------------------------------------+
| ``Phake::reset()``                | ``Phake::resetStatic()``                |
+-----------------------------------+-----------------------------------------+

If you are using Phake to stub or verify static methods then you should call ``Phake::resetStaticInfo()`` in the
the ``tearDown()`` method. This is necessary to reset the stubs and call recorder for the static calls in the event
that the mock class gets re-used.
