# struggle-for-php/sfp-phpstan-psr-log

[![Latest Stable Version](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/v/stable)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![License](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/license)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![Psalm coverage](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log/coverage.svg)](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log)

* [PHPStan](https://phpstan.org/)
* [PSR-3: Logger Interface - PHP-FIG](https://www.php-fig.org/psr/psr-3/)

This extension provides following features:

* stubs

  * Deliver stubs to let PHPStan understand psr/log (PSR-3) strictly.
  * >  Implementors MUST still verify that the 'exception' key is actually an Exception before using it as such, as it MAY contain anything.
  * https://www.php-fig.org/psr/psr-3/#13-context

It also contains this strict specific rules:

* ContextKeyNonEmptyStringRule
  * context key should be string.
* PlaceHolderInMessageRule
  * placeholder in `$message` characters are `A-Z`, `a-z`, `0-9`, underscore `_`, and period `.`
* ContextKeyPlaceHolderRule
  * When placeholder exists in message, checks keys in `$context` exists against them.
* ContextRequireExceptionKeyRule
  * It forces `exception` key into context parameter when current scope has Throwable object.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev struggle-for-php/sfp-phpstan-psr-log
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set.

### Manual installation

If you don't want to use `phpstan/extension-installer`, include extension.neon & rules.neon in your project's PHPStan config:

```neon
includes:
    - vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon
    - vendor/struggle-for-php/sfp-phpstan-psr-log/rules.neon
```

## Examples

### stub - context 'exception' key is actually an Exception

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

### ContextRequireExceptionKeyRule

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
