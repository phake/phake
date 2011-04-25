Phake 1.0
===========

Phake 1.0.0 (alpha6)
-------------

* Added #22 - Fixed a bug that caused tests to fail when E_STRICT or E_NOTICE error reporting was one

* Added #23 - Fixed a bug that caused recoverable fatal errors when a test failed on an equality check with a mock that had a default stub for toString

Phake 1.0.0 (alpha5)
-------------

* Added #21 - Fixed a bug that caused issues when stubbing default parameters

Phake 1.0.0 (alpha4)
-------------

* Added #20 - Added support for mocking consecutive calls to a stub. (http://phake.digitalsandwich.com/docs/html/ch03s04.html)

* Fixed a small issue with PHP 5.2 compatibility in the Lambda Answer functionality

* Added #19 - Added support for stubbing results to reference parameters (http://phake.digitalsandwich.com/docs/html/ch03s10.html)

Phake 1.0.0 (alpha3)
-------------

* Fix #15 - Forgot to add some additional files to package.xml

* Fix #17 - Add Lambda Callbacks

* Fix #14 - Fixed issue with Equals matcher returning a fatal error.

Phake 1.0.0 (alpha2)
-------------

* Fix #9 and #10 - Issues with hamcrest tests and includes

    There was a small side commit in #9 to rename Phake::partMock() to 
    Phake::partialMock(). Phake::partMock() support will be removed before the 
    final release.

* Fix #11 - Allowed more seemless mocking and verification on __call methods.

    Also introduced an alternative syntax for globally mocking and verifying __call

        Phake::whenCallMethodWith(Phake::anyParameters())->isCalledOn($mock)->thenReturn(42)
        Phake::verifyCallMethodWith(Phake::anyParameters())->isCalledOn($mock)

* Fix #12 - Phake no longer attempts to autoload the generated mock class

Phake 1.0.0 (alpha)
-------------

* Intial Release
