<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <m@digitalsandwich.com>
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

namespace Phake;

/**
 * A facade class providing functionality to interact with the Phake framework.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Facade
{
    /**
     * @var array<class-string>
     */
    private array $cachedClasses = [];

    private Mock\InfoRegistry $infoRegistry;

    public function __construct(Mock\InfoRegistry $infoRegistry)
    {
        $this->infoRegistry = $infoRegistry;
    }

    /**
     * Creates a new mock class than can be stubbed and verified.
     *
     * @param class-string|array<class-string> $mockedClassList
     *
     * @throws \InvalidArgumentException
     */
    public function mock(
        string|array $mockedClassList,
        ClassGenerator\MockClass $mockGenerator,
        CallRecorder\Recorder $callRecorder,
        Stubber\IAnswer $defaultAnswer,
        array $constructorArgs = null
    ): IMock {
        $mockedClassList = (array) $mockedClassList;

        foreach ($mockedClassList as $mockedClass) {
            if (!class_exists($mockedClass, true) && !interface_exists($mockedClass, true)) {
                throw new \InvalidArgumentException("The class / interface [{$mockedClass}] does not exist. Check the spelling and make sure it is loadable.");
            }
        }

        /** @var array<class-string> $mockedClassList */
        if (!isset($this->cachedClasses[implode('__', $mockedClassList)])) {
            $newClassName = $this->generateUniqueClassName($mockedClassList);
            $mockGenerator->generate($newClassName, $mockedClassList, $this->infoRegistry);

            $this->cachedClasses[implode('__', $mockedClassList)] = $newClassName;
        }

        return $mockGenerator->instantiate(
            $this->cachedClasses[implode('__', $mockedClassList)],
            $this->infoRegistry,
            $callRecorder,
            new Stubber\StubMapper(),
            $defaultAnswer,
            $constructorArgs
        );
    }

    /**
     * @param class-string|\Phake\IMock $mock
     */
    public function getInfo(\Phake\IMock|string $mock): Mock\Info
    {
        return $this->infoRegistry->getInfo($mock);
    }

    public function getInfoRegistry(): Mock\InfoRegistry
    {
        return $this->infoRegistry;
    }

    public function resetStaticInfo(): void
    {
        $this->infoRegistry->resetAll();
    }

    /**
     * Generates a unique class name based on a given name.
     *
     * The $base will be used as the prefix for the new class name.
     *
     * @param array<class-string> $bases
     *
     * @return class-string
     */
    private function generateUniqueClassName(array $bases): string
    {
        $base_class_name = [];
        foreach ($bases as $base) {
            $ns_parts        = explode('\\', $base);
            $base            = array_pop($ns_parts);
            $base_class_name[] = $base . '_PHAKE' . bin2hex(random_bytes(7));
        }

        $i = 1;

        $base_class_name = implode('__', $base_class_name);

        while (class_exists($base_class_name . $i, false)) {
            $i++;
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return $base_class_name . $i;
    }
}
