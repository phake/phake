# Phake 5.0.0

Phake 5.0.0 introduces significant updates and improvements, including removals of deprecated features and enhanced functionality. **This version requires PHP 8.0 or higher**.

## Removed Support
- **PHP 7**: Phake now supports PHP 8.0 and later.
- **PHPUnit 6 & 7**: Ensure your tests are running on PHPUnit 8 or newer.

## Deprecated Features Removed
- `Phake::partMock()` has been removed. Use `Phake::partialMock()` instead.
- `AnswerProxyInterface::thenGetReturnByLambda()` has been removed.

## Notable Changes
- **Private Methods**: Phake no longer mocks private methods, as mocking them offers no practical value.
- **Readonly Classes**: Mocking of readonly classes is now fully supported.
- **Strict Equals Matchers by Default**: Phake now uses strict equality (`===`) for argument matchers.  
  - To revert to the previous behavior globally, call:
    ```php
    Phake::setStrictDefaultMatchers(false);
    ```
  - For case-by-case usage, update your method calls:
    ```php
    // Old behavior:
    Phake::when($mock)->myMethod(new stdClass);
    
    // New behavior:
    Phake::when($mock)->myMethod(Phake::equalTo(new stdClass));
    ```
- `Phake::verifyNoOtherInteractions()` now accepts variadic arguments, allowing for more flexible usage.

