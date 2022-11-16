## Phake 5.0.0

- Remove support for PHP7
- Remove support for PHPUnit6 and PHPUnit7
- Remove deprecated `Phake::partMock()`
- Remove deprecated `AnswerProxyInterface::thenGetReturnByLambda()`
- `Phake::verifyNoOtherInteractions()` is now variadic
- Private methods will not be mocked anymore (there is no point to it).
- Readonly classes are now mockable
