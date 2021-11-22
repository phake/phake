<?php

if (PHP_VERSION_ID < 80100) {

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
