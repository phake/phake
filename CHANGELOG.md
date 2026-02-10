## Phake 4.7.0

### New Feature

**#334 Add support for Comparator 8 / PHPUnit 13**

### Deprecation

**Shorthand Stub and Verify notation are deprecated**
Replace `Phake::when($mock)->foo by Phake::when($mock)->foo(Phake::anyParameters())` and `Phake::verify($mock)->foo` by `Phake::verify($mock)->foo(Phake::anyParameters())`.

## Phake 4.6.0

### New Feature

**#326 Add support for Comparator 7 / PHPUnit 12**

## Phake 4.5.0

### New Feature

**#320 Add support for Comparator 6 / PHPUnit 11**

## Phake 4.4.0

### New Feature

**#312 Add support for Comparator 5 / PHPUnit 10**

## Phake 4.3.0

### New Features

**Add support for PHP8.2 null and false stand-alone types**
PHP8.2 introduced [null and false stand-alone types](https://wiki.php.net/rfc/null-false-standalone-types).
It's new possible to mock an object using those types.

**Add support for PHP8.2 DNF Types**
PHP8.2 introduced [Disjunctive Normal Form Types](https://wiki.php.net/rfc/dnf_types).
It's now possible to mock an object using DNF types.

### Changes

**#308 - Unable to use PHP8 named arguments on static method**

**#307 - Phake\Matchers\IArgumentMatcher doesn't work as expected**

## Phake 4.2.0

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

