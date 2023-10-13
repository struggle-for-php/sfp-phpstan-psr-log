# struggle-for-php/sfp-phpstan-psr-log

[![Latest Stable Version](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/v/stable)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![License](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/license)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![Psalm coverage](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log/coverage.svg)](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log)

* [PHPStan](https://phpstan.org/)
* [PSR-3: Logger Interface - PHP-FIG](https://www.php-fig.org/psr/psr-3/)

## Stubs

This extension depends on our psr/log stub to serve strictness.

* eg.
  * `@param LogLevel::*  $level` at `log()` method
  * `@param array{exception?: \Throwable} $context`

See [psr/log stub](https://github.com/struggle-for-php/sfp-stubs-psr-log) repository page to get more detail.

## Rules

This package provides the following rules:

### ContextKeyNonEmptyStringRule

* This rule reports an error when context key is not **non-empty-string**.
  * _error identifier:_ `sfp-psr-log.contextKeyNonEmptyString`
  * :x: `[123 => 'foo']`, `['' => 'bar']`, `['baz']`

### PlaceHolderInMessageRule

* This rule reports an error when placeholder in `$message` characters are **not**, `A-Z`, `a-z`, `0-9`, underscore `_`, and period `.`
  * _error identifier:_ `sfp-psr-log.placeHolderInMessageInvalidChar`
* This rules also reports an error when double braces pair `{{` `}}` are used.
  * _error identifier:_ `sfp-psr-log.placeHolderInMessageDoubleBraches`

### ContextKeyPlaceHolderRule

* This rule reports an error when placeholder exists in message, but `$context` parameter is missed.
  * _error identifier:_ `sfp-psr-log.contextKeyPlaceHolder-missedContext`
* This rule reports an error when placeholder exists in message, but key in `$context` does not exist against them.
  * _error identifier:_ `sfp-psr-log.contextKeyPlaceHolderMissedKey`
  * :x: `$logger->info(''user {user_id} gets an error {error} .', ['user_id' => $user_id]);`

### ContextRequireExceptionKeyRule

* It forces `exception` key into context parameter when current scope has `\Throwable` object.
  * _error identifier:_ `sfp-psr-log.contextRequireExceptionKey`

#### Example

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
