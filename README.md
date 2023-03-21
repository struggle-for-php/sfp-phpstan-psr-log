struggle-for-php/sfp-phpstan-psr-log
============================

## Installation

```sh
composer require --dev struggle-for-php/sfp-phpstan-psr-log
```

## Configuration

In your `phpstan.neon` configuration, add following section:

```neon
includes:
	- vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon
	- vendor/struggle-for-php/sfp-phpstan-psr-log/rules.neon
```

## Stubs
- Deliver stubs to let PHPStan understand psr/log (PSR-3) strictly.

>  Implementors MUST still verify that the 'exception' key is actually an Exception before using it as such, as it MAY contain anything.

https://www.php-fig.org/psr/psr-3/#13-context

### Example

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
            // 
        } catch (\Exception $e) {
            $this->logger->error('error happen.', ['exception' => 'foo']);
        }
    }
}
```

```sh
$ ../vendor/bin/phpstan analyse
Note: Using configuration file /tmp/your-project/phpstan.neon.
 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 ------ -------------------------------------------------------------
  Line   Demo.php
 ------ -------------------------------------------------------------
  15     Parameter #2 $context of method Psr\Log\LoggerInterface::error() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
 ------ -------------------------------------------------------------


 [ERROR] Found 1 error
```

## ContextRequireExceptionKeyRule

- Require set exception into context parameter when current scope has Throwable object.

### Example

```php
<?php
/** @var \Psr\Log\LoggerInterface $logger */
try {
    // 
} catch (LogicException $exception) {
    $logger->warning("foo");
}
```

```sh
$ ../vendor/bin/phpstan analyse
Note: Using configuration file /tmp/your-project/phpstan.neon.
 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 ------ -------------------------------------------------------------
  Line   Demo.php
 ------ -------------------------------------------------------------
  6      Parameter $context of logger method Psr\Log\LoggerInterface::warning() requires \'exception\' key. Current scope has Throwable variable - $exception
 ------ -------------------------------------------------------------


 [ERROR] Found 1 error
```
