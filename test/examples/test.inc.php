<?php

const ONE = 1;
define('TWO', 2);
defined('THREE') or define('THREE', 3);

const THIS_IS_TRUE = true;
define("THIS_IS_FALSE", false);

const SINGLE_QUOTED_STRING = 'single';
const DOUBLE_QUOTED_STRING = 'double';

define('FLOAT', 2.25);

// Declaration in comment should be ignored  define('IGNORE_ME', true);
// Same thing about const THIS_SHOULD_BE_IGNORED = true;