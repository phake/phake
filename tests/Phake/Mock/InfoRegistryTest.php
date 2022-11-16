<?php

declare(strict_types=1);

namespace Phake\Mock;

/**
 * Created by PhpStorm.
 * User: mlively
 * Date: 3/30/14
 * Time: 3:29 PM
 */

use Phake;
use PHPUnit\Framework\TestCase;

class InfoRegistryTest extends TestCase
{
    private InfoRegistry $registry;

    /**
     * @Mock
     */
    private Phake\Mock\Info $info1;

    /**
     * @Mock
     */
    private Phake\Mock\Info $info2;

    /**
     * @Mock
     */
    private Phake\Mock\Info $info3;

    public function setUp(): void
    {
        Phake::initAnnotations($this);
        $this->registry = new Phake\Mock\InfoRegistry();
        $this->registry->addInfo('foo', $this->info1);
        $this->registry->addInfo('bar', $this->info2);
        $this->registry->addInfo('baz', $this->info3);
    }

    public function testReset(): void
    {
        $this->registry->resetAll();

        Phake::verify($this->info1)->resetInfo();
        Phake::verify($this->info2)->resetInfo();
        Phake::verify($this->info3)->resetInfo();
    }
}
