<?php

declare(strict_types=1);

namespace PhakeTest;

if (PHP_VERSION_ID < 80100) {
    class SerializableClass implements \Serializable
    {
        public function serialize()
        {
        }

        public function unserialize($serialized)
        {
        }
    }
} else {
    class SerializableClass
    {
        public function __serialize()
        {
        }

        public function __unserialize($serialized)
        {
        }
    }
}
