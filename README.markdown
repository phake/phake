Phake
=======
[![Build Status](https://secure.travis-ci.org/mlively/Phake.png)](http://travis-ci.org/mlively/Phake)

Phake is a framework for PHP that aims to provide mock objects, test doubles
and method stubs.

Phake was inspired by a lack of flexibility and ease of use in the current
mocking frameworks combined with a recent experience with Mockito for Java.

A key conceptual difference in mocking between Phake and most of php mocking
frameworks (ie: mock functionality in PHPUnit, PHPMock, and mock functionality
in SimpleTest) is that Phake (like Mockito) employs a verification strategy to
ensure that calls get made. That is to say, you call your code as normal and
then after you have finished the code being tested you can verify whether or
not expected methods were called. This is very different from the
aforementioned products for php which use an expectation strategy where you
lay out your expectations prior to any calls being made.

Installation - Composer
-----------------------

Phake can be installed using [Composer](https://github.com/composer/composer).

1. Add Phake as a dependency.

``` json
"require": {
	"phake/phake": "dev-master"
}
```

2. Run Composer: `php composer.phar install` or `php composer.phar update`

Installation - PEAR
-------------------

Phake can be installed using PEAR via the Digital Sandwich PEAR channel

This pear channel can be added to PEAR with the following command:

    pear channel-discover pear.digitalsandwich.com

You should only need to do that once. After this you can install Phake with the following command

    pear install digitalsandwich/Phake

After the installation you can find the Phake source files inside your local PEAR directory

Installation - Source
---------------------

You can also of course install it from source by downloading it from our github repository: https://github.com/mlively/Phake

Links
-------------

There are a few links that have information on how you can utilize Phake.

* [Phake Documentation](http://phake.digitalsandwich.com/docs/html/)
* [Phake Google Group](http://groups.google.com/group/phake-users)
* [Initial Phake Announcement](http://digitalsandwich.com/archives/84-introducing-phake-mocking-framework.html)

If you have an article or tutorial that you would like to share, feel free to open an [issue](https://github.com/mlively/Phake/issues) on github and I will add it to this list
