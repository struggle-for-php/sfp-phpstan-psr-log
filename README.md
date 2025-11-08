# psr/log(PSR-3) extensions for PHPStan

[![Latest Stable Version](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/v/stable)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![License](https://poser.pugx.org/struggle-for-php/sfp-phpstan-psr-log/license)](https://packagist.org/packages/struggle-for-php/sfp-phpstan-psr-log)
[![Psalm coverage](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log/coverage.svg)](https://shepherd.dev/github/struggle-for-php/sfp-phpstan-psr-log)

`struggle-for-php/sfp-phpstan-psr-log` is extra strict and opinionated psr/log (psr-3) rules for PHPStan.

* [PHPStan](https://phpstan.org/)
* [PSR-3: Logger Interface - PHP-FIG](https://www.php-fig.org/psr/psr-3/)
* [PSR-3 Meta Document](https://www.php-fig.org/psr/psr-3/meta/)

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev struggle-for-php/sfp-phpstan-psr-log
```

### Manual installation

include extension.neon & rules.neon in your project's PHPStan config:

```neon
includes:
    - vendor/struggle-for-php/sfp-phpstan-psr-log/rules.neon
```

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

### ContextTypeRule

> [!NOTE]
> This rule validates that the `$context` parameter has the correct type according to PSR-3 specification.

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.contextType |

* reports when `$context` parameter type does not match the expected type.
    * The default expected type is `array{exception?: Throwable}` according to PSR-3.

```php
// bad
$logger->info('message', ['exception' => 'string value']); // exception must be Throwable
```

#### Configuration

* If you want to disable this rule, please add `enableContextTypeRule` as false.

```neon
parameters:
    sfpPsrLog:
        enableContextTypeRule: false
```

#### Advanced: Custom Context Type Provider

You can enforce stricter context types by implementing `ContextTypeProviderInterface` and injecting it via dependency injection.

* Example: Restricting to specific context keys

```php
<?php
// src/YourCustomContextTypeProvider.php

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Sfp\PHPStan\Psr\Log\TypeProvider\ContextTypeProviderInterface;

final class YourCustomContextTypeProvider implements ContextTypeProviderInterface
{
    public function getType(): Type
    {
        $builder = ConstantArrayTypeBuilder::createEmpty();

        // Define allowed context keys with their types
        $builder->setOffsetValueType(
            new ConstantStringType('user_id'),
            new StringType(),
            true // optional
        );
        $builder->setOffsetValueType(
            new ConstantStringType('exception'),
            new ObjectType(\Throwable::class),
            true // optional
        );

        return $builder->getArray();
    }
}
```

**Configure in phpstan.neon:**

```neon
services:
    yourCustomContextTypeProvider:
        class: YourCustomContextTypeProvider

    contextTypeProviderResolver:
        class: Sfp\PHPStan\Psr\Log\TypeProviderResolver\AnyScopeContextTypeProviderResolver
        arguments:
            contextTypeProvider: @yourCustomContextTypeProvider

    -
        class: Sfp\PHPStan\Psr\Log\Rules\ContextTypeRule
        arguments:
            contextTypeProviderResolver: @contextTypeProviderResolver
        tags:
            - phpstan.rules.rule
```

This allows you to enforce project-specific context types, such as structured logging schemas (e.g., BigQuery) or custom application requirements.

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

### LogMethodLevelRule

> Implementors MUST implement the following interface, which describes the eight methods to write logs to the eight RFC 5424 levels (debug, info, notice, warning, error, critical, alert, emergency).
> — [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)

| :pushpin: _error identifier_ |
| --- |
| sfpPsrLog.logMethodLevel |

* reports when the `$level` parameter of `log()` method is not a valid PSR-3 log level.
    * Valid log levels: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`

```php
// bad
$logger->log('panic', 'message'); // 'panic' is not a valid PSR-3 log level
$logger->log(100, 'message'); // level must be a string
```

#### Configuration

* If you want to disable this rule, please add `enableLogMethodLevelRule` as false.

```neon
parameters:
    sfpPsrLog:
        enableLogMethodLevelRule: false
```
