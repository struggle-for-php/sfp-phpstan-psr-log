struggle-for-php/sfp-phpstan-psr-log
============================

 - Deliver stubs to let PHPStan understand psr/log (PSR-3) strictly.
    
## Example
```sh
$ ../vendor/bin/phpstan analyse --level=1 src/
Note: Using configuration file /tmp/your-project/phpstan.neon.
 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 ------ -------------------------------------------------------------
  Line   Demo.php
 ------ -------------------------------------------------------------
  17     Parameter #2 $context of method Psr\Log\LoggerInterface::alert() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
  18     Parameter #2 $context of method Psr\Log\AbstractLogger::emergency() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
 ------ -------------------------------------------------------------


 [ERROR] Found 2 error
```


## Installation

```sh
composer require --dev struggle-for-php/sfp-phpstan-psr-log
```

## Configuration

In your `phpstan.neon` configuration, add following section:

```neon
includes:
	- vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon
```

