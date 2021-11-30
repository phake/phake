Phake
=======
[![Documentation Status](https://readthedocs.org/projects/phake/badge/?version=latest)](https://readthedocs.org/projects/phake/?badge=latest)
[![Latest Stable Version](https://poser.pugx.org/phake/phake/v/stable.png)](https://packagist.org/packages/phake/phake) [![Total Downloads](https://poser.pugx.org/phake/phake/downloads.png)](https://packagist.org/packages/phake/phake) [![Latest Unstable Version](https://poser.pugx.org/phake/phake/v/unstable.png)](https://packagist.org/packages/phake/phake) [![License](https://poser.pugx.org/phake/phake/license.png)](https://packagist.org/packages/phake/phake)

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

```
composer require --dev phake/phake
```

Installation - Source
---------------------

You can also of course install it from source by downloading it from our github repository: https://github.com/phake/phake

Links
-------------

There are a few links that have information on how you can utilize Phake.

* [Phake Documentation](http://phake.readthedocs.org/en/latest/)
* [Initial Phake Announcement](https://web.archive.org/web/20101230174850/http://digitalsandwich.com/archives/84-introducing-phake-mocking-framework.html)

If you have an article or tutorial that you would like to share, feel free to open an [issue](https://github.com/phake/phake/issues) on github and I will add it to this list
