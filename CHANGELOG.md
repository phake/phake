## What's New in 4.2.0

### New Features

**#304 - Add support for PHP8.1 new in initializers**
PHP8.1 introduced [new in initializers](https://wiki.php.net/rfc/new_in_initializers)
Phake 4.2 can now create mock of object using this feature.

**#303 - Add support for PHP8.1 intersection types and never return type**
PHP8.1 introduced [intersection types](https://wiki.php.net/rfc/pure-intersection-types]) and [never return type](https://wiki.php.net/rfc/noreturn_type).
Phake 4.2 supports those new types. When a mocked method returning `never` is called, Phake will by default throw a `Phake\Exception\NeverReturnMethodCalledException` exception. Calling `Phake::when($mock)->thenReturn($x)` will have no effect on this method result.

### Changes

**#301 - Add `#[\ReturnTypeWillChange]` on mocked internal methods on PHP8.1+**
All internal mocked method under PHP8.1+ will have `#[\ReturnTypeWillChange]` attribute to avoid any *Deprecation warnings*.
