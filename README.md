struggle-for-php/sfp-phpstan-psr-log
============================

 - Deliver stubs to let PHPStan understand psr/log (PSR-3) strictly.
 
## Refs.
>  Implementors MUST still verify that the 'exception' key is actually an Exception before using it as such, as it MAY contain anything.

https://www.php-fig.org/psr/psr-3/#13-context 
 
## Example

```php
<?php

use Psr\Log\LoggerInterface;

class Foo
{
    /** @var LoggerInterface */
    private $logger;

    public function anyAction()
    {
        try {
        } catch (\Exception $e) {
            $this->logger->error('error happen.', ['exception' => 'foo']);
        }
    }
}
```

```sh
$ ../vendor/bin/phpstan analyse --level=5 src/
Note: Using configuration file /tmp/your-project/phpstan.neon.
 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 ------ -------------------------------------------------------------
  Line   Demo.php
 ------ -------------------------------------------------------------
  14     Parameter #2 $context of method Psr\Log\LoggerInterface::error() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
 ------ -------------------------------------------------------------


 [ERROR] Found 1 error
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

## Unit Test

needs separated running per Test suite
```
./vendor/bin/phpunit tests/StubTest.php
./vendor/bin/phpunit tests/ThrowableStubTest.php
```

## Notes
* `Psr\Log\InvalidArgumentException` file is needed for stub.
     - see https://github.com/phpstan/phpstan/issues/3124
