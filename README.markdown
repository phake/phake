Phake
=======

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

Installation
------------

Phake should be installed using PEAR via the Digital Sandwich PEAR channel

This pear channel can be added to PEAR with the following command:

    pear channel-discover pear.digitalsandwich.com

You should only need to do that once. After this you can install Phake with the following command

    pear install channel://pear.digitalsandwich.com/Phake-1.0.0alpha3

After the installation you can find the Phake source files inside your local PEAR directory

You can also of course install it from source by downloading it from our github repository: https://github.com/mlively/Phake

Links
-------------

There are a few links that have information on how you can utilize Phake. I am in the process of
creating more official documentation.

* [Phake Wiki](https://github.com/mlively/Phake/wiki)
* [Initial Phake Announcement](http://digitalsandwich.com/archives/84-introducing-phake-mocking-framework.html)

If you have an article or tutorial that you would like to share, feel free to open an [issue](https://github.com/mlively/Phake/issues) on github and I will add it to this list