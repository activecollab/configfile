# ConfigFile Library

[![Build Status](https://travis-ci.org/activecollab/configfile.svg?branch=master)](https://travis-ci.org/activecollab/configfile)

Simple utility for getting constants from configuration files written in PHP.

## Installation

To install it, use Composer:

```json
{
    "require": {
        "activecollab/configfile": "~1.0"
    }
}
```

## Usage

If we have a `config.example.php` file that looks like this:

```php
<?php

const ONE = 1;
define ('TWO', 2);
defined ('THREE') or define('THREE', 3);

const THIS_IS_TRUE = true;
define ("THIS_IS_FALSE", false);

const SINGLE_QUOTED_STRING = 'single';
const DOUBLE_QUOTED_STRING = 'double';

define('FLOAT', 2.25);

// Declaration in comment should be ignored  define('IGNORE_ME', true);
// Same thing about const THIS_SHOULD_BE_IGNORED = true;
```

and we parse it like this:

```php
<?php

use ActiveCollab\ConfigFile\ConfigFile;

$config_file = new ConfigFile('config.example.php');
var_dump($config_file->getOptions());
```

we'll get:

```
array(8) {
  ["ONE"]=>
  int(1)
  ["TWO"]=>
  int(2)
  ["THREE"]=>
  int(3)
  ["THIS_IS_TRUE"]=>
  bool(true)
  ["THIS_IS_FALSE"]=>
  bool(false)
  ["SINGLE_QUOTED_STRING"]=>
  string(6) "single"
  ["DOUBLE_QUOTED_STRING"]=>
  string(6) "double"
  ["FLOAT"]=>
  float(2.25)
}
```

## To Do

This library has been created in a bit of a rush, so there are still some things to do:

1. `const` should be parsed using tokenizer
2. Follow included files using `include` and `require` [?]
3. Library should be able to modify and write config files [?]
