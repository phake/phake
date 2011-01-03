Phake 1.0
===========

Phake 1.0.1 (dev)
-------------

* Fix #9 and #10 - Issues with hamcrest tests and includes
** There was a small side commit in #9 to rename Phake::partMock() to Phake::partialMock(). Phake::partMock() support will be removed before the final release.
* Fix #11 - Allowed more seemless mocking and verification on __call methods.
** Also introduced an alternative syntax for globally mocking and verifying __call: Phake::whenCallMethodWith(Phake::anyParameters())->isCalledOn($mock)->thenReturn(42) and Phake::verifyCallMethodWith(Phake::anyParameters())->isCalledOn($mock)
* Fix #12 - Phake no longer attempts to autoload the generated mock class

Phake 1.0.0 (alpha)
-------------

* Intial Release