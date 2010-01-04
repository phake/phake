<?php

$base_dir = __DIR__ . '/..';
set_include_path(get_include_path() . \PATH_SEPARATOR
		. $base_dir . '/tests' . \PATH_SEPARATOR
		. $base_dir . '/src');

require_once('hamcrest.php');
?>
