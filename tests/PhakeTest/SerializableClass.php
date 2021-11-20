<?php

if (version_compare(PHP_VERSION, '8.1.0', '>=')) {

    class PhakeTest_SerializableClass implements \Serializable
    {
        public function serialize()
        {

        }

        public function unserialize($serialized)
        {

        }
    }

} else {

    class PhakeTest_SerializableClass
    {
        public function __serialize()
        {

        }

        public function __unserialize($serialized)
        {

        }
    }
}
