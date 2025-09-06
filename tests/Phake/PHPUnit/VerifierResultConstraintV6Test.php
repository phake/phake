<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2025, Mike Lively <mike.lively@sellingsource.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

declare(strict_types=1);

namespace Phake\PHPUnit;

use Phake;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

class VerifierResultConstraintV6Test extends TestCase
{
    private VerifierResultConstraintV6 $constraint;

    public function setUp(): void
    {
        if (version_compare(Version::id(), '6.0.0', '<')) {
            $this->markTestSkipped('The tested class is not compatible with current version of PHPUnit.');
        }
        $this->constraint = new VerifierResultConstraintV6();
    }

    public function testExtendsPHPUnitConstraint(): void
    {
        $this->assertInstanceOf(\PHPUnit\Framework\Constraint\Constraint::class, $this->constraint);
    }

    public function testEvaluateReturnsTrueIfVerifyResultIsTrue(): void
    {
        $result = new Phake\CallRecorder\VerifierResult(true, []);
        $this->assertTrue($this->constraint->evaluate($result, '', true));
    }

    public function testEvaluateReturnsFalseWhenVerifierReturnsFalse(): void
    {
        $result = new Phake\CallRecorder\VerifierResult(false, []);
        $this->assertFalse($this->constraint->evaluate($result, '', true));
    }

    public function testEvaluateThrowsWhenArgumentIsNotAResult(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->constraint->evaluate('');
    }

    public function testToString(): void
    {
        $this->assertEquals('is called', $this->constraint->toString());
    }

    public function testCustomFailureDescriptionReturnsDescriptionFromResult(): void
    {
        $result = new Phake\CallRecorder\VerifierResult(false, [], 'The call failed!');

        try {
            $this->constraint->evaluate($result, '');
            $this->fail('expected an exception to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals('Failed asserting that The call failed!.', $e->getMessage());
        }
    }

    public function testFailThrowsWhenArgumentIsNotAResult(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->constraint->evaluate('', '');
    }
}
