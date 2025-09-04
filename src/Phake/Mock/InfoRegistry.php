<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <mike.lively@sellingsource.com>
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

namespace Phake\Mock;

use WeakMap;

/**
 * Stores all Info instances for static classes.
 */
class InfoRegistry
{
    /**
     * @var array<Info>
     */
    private array $staticRegistry = [];

    /**
     * @var array<string, ReflectionProperty>
     */
    private array $reflectionCache = [];

    private function injectInfo(\Phake\IMock $mock, Info $info): void
    {
        $class = get_class($mock);
        if (!isset($this->reflectionCache[$class])) {
            $rc = new \ReflectionClass($mock);
            $rp = $rc->getProperty('__PHAKE_info');
            $rp->setAccessible(true);
            $this->reflectionCache[$class] = $rp;
        }
        $this->reflectionCache[$class]->setValue($mock, $info);
    }

    /**
     * @param \Phake\IMock|class-string $mock
     */
    public function addInfo(\Phake\IMock|string $mock, Info $info): void
    {
        if ($mock instanceof \Phake\IMock) {
            $this->injectInfo($mock, $info);
            return;
        }

        $this->staticRegistry[$mock] = $info;
    }

    /**
     * @param \Phake\IMock|class-string $mock
     */
    public function getInfo(\Phake\IMock|string $mock): Info
    {
        if ($mock instanceof \Phake\IMock) {
            return $mock->__PHAKE_info;
        }

        return $this->staticRegistry[$mock];
    }

    public function resetAll(): void
    {
        foreach ($this->staticRegistry as $info) {
            assert($info instanceof Info);
            $info->resetInfo();
        }
    }
}
