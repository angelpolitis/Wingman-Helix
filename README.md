# Wingman — Helix

A runtime contract enforcement library for PHP 8.1+. Part of the [Wingman](https://github.com/angelpolitis/wingman) framework, but usable as a standalone library.

Helix lets you define structural contracts — expected methods, properties, and constants — and enforce them against any object or class name at runtime. Contracts are composable, cloneable, and evaluated through a rich term system that handles access modifiers, types, parameter signatures, static context, read-only flags, PHP attributes, and more.

---

## Requirements

- PHP 8.1 or higher
- No external runtime dependencies (the Argus bridge is optional)

---

## Installation

```bash
composer require wingman/helix
```

---

## Quick Start

```php
use Wingman\Helix\Contract;
use Wingman\Helix\Enums\AccessModifier;
use Wingman\Helix\Exceptions\ContractViolationException;

// Define a contract.
$contract = Contract::create("Serialisable", function (Contract $c) {
    $c->defineMethod("serialise")
        ->expectAccessModifier(AccessModifier::Public)
        ->expectReturnType("string")
        ->require();

    $c->defineMethod("deserialise")
        ->expectAccessModifier(AccessModifier::Public)
        ->expectReturnType("static")
        ->expectParameter("data", "string")
        ->require();
});

// Check silently.
if ($contract->isSatisfiedBy($myObject)) {
    // ...
}

// Or enforce (throws on first violation).
try {
    $contract->validate($myObject);
} catch (ContractViolationException $e) {
    echo $e->getMessage();
}

// Collect all violations at once.
$contract->validate($myObject, allErrors: true);
```

---

## Core Concepts

| Concept | Description |
| --------- | ------------- |
| **Contract** | A named collection of member requirements (methods, properties, constants) |
| **Member** | A single requirement — `Method`, `Property`, or `Constant` |
| **Parameter** | Describes an argument expected by a method |
| **Term** | An atomic evaluation rule attached to a member (e.g. "must be public", "must return string") |
| **Inspector** | Reflection cache used internally by all terms; injectable for testing |
| **Proxy** | A dynamic proxy that maps interface calls to target object methods via `Closure::bind()` |

---

## Building Contracts

### From a builder callback

```php
$contract = Contract::create("HasLogger", function (Contract $c) {
    $c->defineProperty("logger")->expectType("Psr\Log\LoggerInterface")->require();
});
```

### From an existing PHP interface

```php
$contract = Contract::fromInterface(JsonSerializable::class);
```

`fromInterface()` captures each method's parameter signatures, return types, and access modifiers. Constants are captured with their values and access modifiers.

### Inline

```php
$contract = new Contract("HasVersion");
$contract->defineConstant("VERSION")->expectValue("1.0")->require();
```

---

## Evaluation

```php
// Boolean — never throws.
$contract->isSatisfiedBy($target);

// Throws ContractViolationException on first failure.
$contract->validate($target);

// Throws with a list of all failures.
$contract->validate($target, allErrors: true);
```

Pass either an **object instance** or a **class name string** as `$target`. When a class string is passed, instance-level checks (live property values, non-static properties) behave accordingly — see [docs/Members.md](docs/Members.md) for details.

---

## Documentation

| Topic | File |
| ------- | ------ |
| Contract API | [docs/Contract.md](docs/Contract.md) |
| Method, Property, Constant, Parameter | [docs/Members.md](docs/Members.md) |
| Inspector & caching | [docs/Inspector.md](docs/Inspector.md) |
| Term system | [docs/Terms.md](docs/Terms.md) |
| Proxy | [docs/Proxy.md](docs/Proxy.md) |
| Argus bridge (test assertions) | [docs/Bridge.md](docs/Bridge.md) |

---

## Licence

This project is licensed under the **Mozilla Public License 2.0 (MPL 2.0)**.

Wingman Helix is part of the **Wingman Framework**, Copyright (c) 2026-2026 Angel Politis.

For the full licence text, please see the [LICENSE](LICENSE) file.
