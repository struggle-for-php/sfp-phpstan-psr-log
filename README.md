# struggle-for-php/sfp-phpstan-psr-log

[![Latest Stable Version](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/v/stable)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![License](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/license)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![Psalm coverage](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log/coverage.svg)](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log)

* [PHPStan](https://phpstan.org/)
* [PSR-3: Logger Interface - PHP-FIG](https://www.php-fig.org/psr/psr-3/)
* [PSR-3 Meta Document](https://www.php-fig.org/psr/psr-3/meta/)

## Stubs

This extension depends on our psr/log stub to serve strictness.

* eg.
  * `@param LogLevel::*  $level` at `log()` method
  * `@param array{exception?: \Throwable} $context`

See [psr/log stub](https://github.com/struggle-for-php/sfp-stubs-psr-log) repository page to get more detail.

## Rules

This package provides the following rules.

### PlaceHolderInMessageRule

> Placeholder names SHOULD be composed only of the characters A-Z, a-z, 0-9, underscore _, and period .

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.placeHolderInMessageInvalidChar |

* reports when placeholder in `$message` characters are **not**, `A-Z`, `a-z`, `0-9`, underscore `_`, and period `.`

```php
// bad
$logger->info('message are {foo-hyphen}');
```

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.placeHolderInMessageDoubleBraches |

* reports when double braces pair `{{` `}}` are used.

```php
// bad
$logger->info('message are {{foo}}');
```

### ContextKeyPlaceHolderRule

> Placeholder names MUST correspond to keys in the context array.

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.contextKeyPlaceHolderMissedContext |

* reports when placeholder exists in message, but `$context` parameter is missed.

```php
// bad
$logger->info('message has {nonContext} .');
```

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.contextKeyPlaceHolderMissedKey |

* reports when placeholder exists in message, but key in `$context` does not exist against them.

```php
// bad
$logger->info('user {user_id} gets an error {error} .', ['user_id' => $user_id]);
```

### ContextKeyNonEmptyStringRule

> [!NOTE]
> PSR-3 has no provisions for array keys, but this is useful in many cases.

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.contextKeyNonEmptyString |

* reports when context key is not **non-empty-string**.

```php
// bad
[123 => 'foo']`, `['' => 'bar']`, `['baz']
```

### ContextRequireExceptionKeyRule

> [!NOTE]
> This is not a rule for along with PSR-3 specification, but provides best practices.

| :pushpin: _error identifier_ |
| --- |
| sfp-psr-log.contextRequireExceptionKey |

* It forces `exception` key into context parameter when current scope has `\Throwable` object.

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
