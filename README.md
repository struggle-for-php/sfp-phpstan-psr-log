# psr/log(PSR-3) extensions for PHPStan

[![Latest Stable Version](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/v/stable)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![License](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/license)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![Psalm coverage](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log/coverage.svg)](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log)

> [!IMPORTANT]
> The next version `0.25.0` will have a BC break. Please refer `Stubs` section.

`struggle-for-php/sfp-phpstan-psr-log` is extra strict and opinionated psr/log (psr-3) rules for PHPStan.

* [PHPStan](https://phpstan.org/)
* [PSR-3: Logger Interface - PHP-FIG](https://www.php-fig.org/psr/psr-3/)
* [PSR-3 Meta Document](https://www.php-fig.org/psr/psr-3/meta/)

## Recommendation Settings

Write these parameters to your project's `phpstan.neon`.

```neon
parameters:
    sfpPsrLog:
        enableMessageStaticStringRule: false # default:true
        enableContextRequireExceptionKeyRule: true
        reportContextExceptionLogLevel: 'info'
        contextKeyOriginalPattern: '#\A[A-Za-z0-9-_]+\z#'
```

## Stubs

> [!IMPORTANT]
> include psr/log stub be planned to dropped in next release.

To try out the changes in the next version,

DELETE `vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon` line from your `phpstan.neon`

```neon
includes:
    - vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon
```

and, set parameters `enableLogLevelMethodRule` and `enableContextTypeRule`

```neon
parameters:
    sfpPsrLog:
        enableLogLevelMethodRule: true # default:false
        enableContextTypeRule: true # default:false
```

### About stub

Currently, this extension depends on our psr/log stub to serve strictness.

* eg.
    * `@param LogLevel::*  $level` at `log()` method
    * `@param array{exception?: \Throwable} $context`

See [psr/log stub](https://github.com/struggle-for-php/sfp-stubs-psr-log) repository page to get more detail.

## Rules

This package provides the following rules.

### PlaceholderCharactersRule

> Placeholder names SHOULD be composed only of the characters A-Z, a-z, 0-9, underscore _, and period .

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.placeholderCharactersInvalidChar |

* reports when placeholder in `$message` characters are **not**, `A-Z`, `a-z`, `0-9`, underscore `_`, and period `.`

```php
// bad
$logger->info('message are {foo-hyphen}');
```

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.placeholderCharactersDoubleBraces |

* reports when double braces pair `{{` `}}` are used.

```php
// bad
$logger->info('message are {{foo}}');
```

### PlaceholderCorrespondToKeysRule

> Placeholder names MUST correspond to keys in the context array.

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.placeholderCorrespondToKeysMissedContext |

* reports when placeholder exists in message, but `$context` parameter is missed.

```php
// bad
$logger->info('message has {nonContext} .');
```

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.placeholderCorrespondToKeysMissedKey |

* reports when placeholder exists in message, but key in `$context` does not exist against them.

```php
// bad
$logger->info('user {user_id} gets an error {error} .', ['user_id' => $user_id]);
```

### ContextKeyRule

> [!NOTE]
> PSR-3 has no provisions for array keys, but this is useful in many cases.

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.contextKeyNonEmptyString |

* reports when context key is not **non-empty-string**.

```php
// bad
[123 => 'foo']`, `['' => 'bar']`, `['baz']
```

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.contextKeyOriginalPattern |

* reports when context key is not matched you defined pattern.
    * if `contextKeyOriginalPattern` parameter is not set, this check would be ignored.

#### Configuration

* You can set specific key pattern by regex.(`preg_match()`)

```neon
parameters:
    sfpPsrLog:
        contextKeyOriginalPattern: '#\A[A-Za-z0-9-]+\z#'
```

### ContextRequireExceptionKeyRule

> [!NOTE]
> This is not a rule for along with PSR-3 specification, but provides best practices.

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.contextRequireExceptionKey |

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

#### Configuration

* You can set the minimum required level to report. (default level is `debug`)

```neon
parameters:
    sfpPsrLog:
        reportContextExceptionLogLevel: 'warning'
```

Then, `debug`| `info` | `notice` LogLevel  is ignored for report.

```php
} catch (\Exception $e) {
  $logger->info('more info'); // allow
  $logger->warning($e->getMessage(), ['exception' => $e]);
}
```

* If you want to enable this rule, please add `enableContextRequireExceptionKeyRule` as true.

```neon
parameters:
    sfpPsrLog:
        enableContextRequireExceptionKeyRule: true
```

### MessageStaticStringRule

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.messageNotStaticString |

* reports when $message is not static string value.

```php
// bad
$logger->info(sprintf('Message contains %s variable', $var));
```

#### Configuration

* If you want to disable this rule, please add `enableMessageStaticStringRule` as false.

```neon
parameters:
    sfpPsrLog:
        enableMessageStaticStringRule: false
```

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev struggle-for-php/sfp-phpstan-psr-log
```

### Manual installation

include extension.neon & rules.neon in your project's PHPStan config:

```neon
includes:
    - vendor/struggle-for-php/sfp-phpstan-psr-log/extension.neon
    - vendor/struggle-for-php/sfp-phpstan-psr-log/rules.neon
```
