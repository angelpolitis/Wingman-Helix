# Terms

This document describes the term system — the atomic evaluation rules that power Helix contract checks.

---

## The `ContractTerm` Interface

`Wingman\Helix\Interfaces\ContractTerm`

Every term implements this interface:

```php
interface ContractTerm {
    public function evaluate(object|string $objOrClass): bool;
    public function getErrorMessage(): string;
    public function setArgs(array $args): static;
    public function setContext(Member $member): static;
}
```

- `evaluate()` is called once per term per contract evaluation pass. It should be idempotent and side-effect-free apart from populating internal state (like `$methodDefined`) that `getErrorMessage()` reads.
- `getErrorMessage()` is only called after a failed `evaluate()` — its output is included in the `ContractViolationException` message.
- `setContext()` binds the term to its owning `Member` (`Method`, `Property`, or `Constant`).
- `setArgs()` passes runtime arguments — used by attribute terms to carry an optional expected attribute value.

You never call these methods directly. They are invoked by `Contract::isSatisfiedBy()` and `Contract::validate()`.

---

## Custom Terms

You can attach custom terms to any member using `addTerm()`:

```php
$method = $contract->defineMethod("process");

$method->addTerm(
    new class implements ContractTerm {
        private Method $method;

        public function setContext(Member $member): static { $this->method = $member; return $this; }
        public function setArgs(array $args): static { return $this; }

        public function evaluate(object|string $objOrClass): bool {
            // custom logic
            return true;
        }

        public function getErrorMessage(): string {
            return "Method '{$this->method->getName()}' failed custom check.";
        }
    }
);
```

Or by class name:

```php
$method->addTerm(MyCustomTerm::class, constructorArgs: ["arg1"], evaluatorArgs: ["eval1"]);
```

---

## Base Abstract Classes

All built-in terms extend one of three abstract bases that handle `setContext()` / `setArgs()`:

| Base class | Available context property |
|------------|---------------------------|
| `MethodContractTerm` | `protected Method $method` |
| `PropertyContractTerm` | `protected Property $property` |
| `ConstantContractTerm` | `protected Constant $constant` |

---

## Method Terms

The following terms operate on `Method` members. They are not added automatically — they are used internally by `Method` or can be added manually with `addTerm()`.

### `MethodExistsTerm`

Checks that the method exists on the target. Passes for optional methods even when absent.

**Error:** `"Method 'foo' does not exist."`

---

### `MethodMatchesSignatureTerm` *(added automatically by `Method`)*

Composite term covering: existence, access modifier, `static`, `final`, `abstract`, return type, and parameter list. This is the primary term added to every `Method` at construction time.

Only attributes that have been explicitly set on the `Method` are checked — unset attributes (`null`) are ignored.

**Errors:**
- `"Method 'foo' does not exist."` — when the method is missing and required.
- `"Method 'foo' does not match the defined signature: ..."` — attribute mismatch, with the expected signature appended.

---

### `MethodHasTypeTerm`

Checks that the method's return type declaration matches the expected string. Handles union types (`int|string`), intersection types (`A&B`), nullable shorthand (`?string`), and `self`/`static` resolution.

**Error:** `"Method 'foo' does not have the expected return type 'string'."`

---

### `MethodIsPublicTerm`

Checks that the method is `public`.

---

### `MethodIsProtectedTerm`

Checks that the method is `protected`.

---

### `MethodIsPrivateTerm`

Checks that the method is `private`.

---

### `MethodIsStaticTerm`

Checks that the method is declared `static`.

---

### `MethodIsFinalTerm`

Checks that the method is declared `final`.

---

### `MethodIsAbstractTerm`

Checks that the method is declared `abstract`.

---

### `MethodHasAttributeTerm`

Checks that the method's declaration includes a specific PHP attribute. Constructed with the attribute's FQCN.

Optionally, a value can be passed as an evaluator argument (via `setArgs([0 => $expectedValue])`). The term then checks that at least one instance of the attribute carries that expected value among its constructor arguments.

```php
$method->addTerm(new MethodHasAttributeTerm(MyAttribute::class));
$method->addTerm(new MethodHasAttributeTerm(MyAttribute::class), evaluatorArgs: ["expectedValue"]);
```

**Error:** `"Method 'foo' does not have the required attribute 'MyAttribute'."` (or with value context appended).

---

### `MethodReturnValueTerm`

Checks that the return type of the method satisfies a nested `Contract`. This term is **not** added automatically and must be attached manually.

Constructor: `__construct(?string $type = null, ?Contract $contract = null)`

- `$type` — if set, checks that the method's return type annotation matches (using `TypeComparator`).
- `$contract` — if set, takes each class/interface name from the return type and validates it against the provided contract. Useful for asserting that anything this method returns satisfies a structural contract.

```php
$loggableContract = Contract::create("Loggable", fn ($c) => $c->defineMethod("log")->require());

$method->addTerm(new MethodReturnValueTerm(
    type: "Wingman\Service\UserService",
    contract: $loggableContract,
));
```

**Error:** `"Method 'foo' return type does not satisfy the contract (Expected: SomeType)."`

---

## Property Terms

### `PropertyExistsTerm`

Checks that the property exists on the target. Passes for optional properties when absent.

---

### `PropertyMatchesSignatureTerm` *(added automatically by `Property`)*

Composite term covering: existence, access modifier, `static`, `readonly`, and type hint. Only attributes explicitly set on the `Property` are checked.

**Errors:**
- `"Property '$foo' does not exist."` — absent and required.
- `"Property '$foo' does not match the defined signature: ..."` — attribute mismatch.

---

### `PropertyHasTypeTerm`

Checks that the property has a specific type hint.

---

### `PropertyIsPublicTerm`

Checks that the property is `public`.

---

### `PropertyIsProtectedTerm`

Checks that the property is `protected`.

---

### `PropertyIsPrivateTerm`

Checks that the property is `private`.

---

### `PropertyIsStaticTerm`

Checks that the property is `static`.

---

### `PropertyIsReadOnlyTerm`

Checks that the property is `readonly` (PHP 8.1+).

---

### `PropertyHasAttributeTerm`

Checks that a specific PHP attribute is present on the property. Supports optional value matching, identical to `MethodHasAttributeTerm`.

```php
$property->addTerm(new PropertyHasAttributeTerm(Column::class));
$property->addTerm(new PropertyHasAttributeTerm(Column::class), evaluatorArgs: ["users"]);
```

---

### `PropertyValueTerm` *(added automatically by `Property`)*

Checks the runtime value of a property. Only active when `expectValue()` has been called on the `Property`.

| Target | Property | Behaviour |
|--------|----------|-----------|
| Object | Any | Reads live value via reflection. Fails if uninitialised. |
| Class string | Static | Reads default static value. |
| Class string | Non-static | Always fails — requires an object instance. |

**Error messages:**
- `"Property '$foo' requires an object instance to verify its value; a class name was given."`
- `"Property '$foo' does not match the required value: <value>."`

---

## Constant Terms

### `ConstantExistsTerm`

Checks that the constant exists on the target. Uses reflection `hasConstant()`.

---

### `ConstantMatchesSignatureTerm` *(added automatically by `Constant`)*

Composite term covering: existence, access modifier, typed constant support (PHP 8.3+), and value. Only attributes set on the `Constant` are checked.

**Errors:**
- `"Constant 'FOO' does not exist."` — absent and required.
- `"Constant 'FOO' does not match the defined signature: ..."` — attribute mismatch.

---

### `ConstantHasTypeTerm`

Checks that the constant has a specific type declaration (PHP 8.3+).

---

### `ConstantIsPublicTerm`

Checks that the constant is `public`.

---

### `ConstantIsProtectedTerm`

Checks that the constant is `protected`.

---

### `ConstantIsPrivateTerm`

Checks that the constant is `private`.

---

### `ConstantHasAttributeTerm`

Checks that a specific PHP attribute is present on the constant. Supports optional value matching.

```php
$constant->addTerm(new ConstantHasAttributeTerm(Deprecated::class));
```

---

## Term Reference Table

| Term | Family | What it checks |
|------|--------|----------------|
| `MethodExistsTerm` | Method | Method exists |
| `MethodMatchesSignatureTerm` | Method | Full method signature (auto-added) |
| `MethodHasTypeTerm` | Method | Return type hint |
| `MethodIsPublicTerm` | Method | Access == public |
| `MethodIsProtectedTerm` | Method | Access == protected |
| `MethodIsPrivateTerm` | Method | Access == private |
| `MethodIsStaticTerm` | Method | `static` keyword |
| `MethodIsFinalTerm` | Method | `final` keyword |
| `MethodIsAbstractTerm` | Method | `abstract` keyword |
| `MethodHasAttributeTerm` | Method | PHP attribute present (+ optional value) |
| `MethodReturnValueTerm` | Method | Return type + optional nested contract |
| `PropertyExistsTerm` | Property | Property exists |
| `PropertyMatchesSignatureTerm` | Property | Full property signature (auto-added) |
| `PropertyHasTypeTerm` | Property | Type hint |
| `PropertyIsPublicTerm` | Property | Access == public |
| `PropertyIsProtectedTerm` | Property | Access == protected |
| `PropertyIsPrivateTerm` | Property | Access == private |
| `PropertyIsStaticTerm` | Property | `static` keyword |
| `PropertyIsReadOnlyTerm` | Property | `readonly` keyword |
| `PropertyHasAttributeTerm` | Property | PHP attribute present (+ optional value) |
| `PropertyValueTerm` | Property | Live / static default value (auto-added) |
| `ConstantExistsTerm` | Constant | Constant exists |
| `ConstantMatchesSignatureTerm` | Constant | Full constant signature (auto-added) |
| `ConstantHasTypeTerm` | Constant | Typed constant type (PHP 8.3+) |
| `ConstantIsPublicTerm` | Constant | Access == public |
| `ConstantIsProtectedTerm` | Constant | Access == protected |
| `ConstantIsPrivateTerm` | Constant | Access == private |
| `ConstantHasAttributeTerm` | Constant | PHP attribute present (+ optional value) |

---

## TypeComparator Utility

`Wingman\Helix\TypeComparator`

`TypeComparator` is the normalisation and comparison layer used internally by all type-checking terms. You will need it when writing custom terms that compare types.

### `TypeComparator::stringifyType(ReflectionType $type) : string`

Converts any `ReflectionType` (including `ReflectionUnionType`, `ReflectionIntersectionType`, and `ReflectionNamedType`) into a canonical string representation.

```php
use Wingman\Helix\TypeComparator;

$reflection = new ReflectionMethod(MyClass::class, "find");
$type = TypeComparator::stringifyType($reflection->getReturnType()); // e.g. "static|null"
```

### `TypeComparator::matchType(string $expected, string $actual, object|string $context) : bool`

Compares an expected type string against an actual type string, resolving `self` and `static` relative to `$context`. Handles union types, intersection types, nullable shorthand (`?T`), and `mixed`.

```php
use Wingman\Helix\TypeComparator;

// In a custom term's evaluate():
$actual = TypeComparator::stringifyType($reflection->getReturnType());
return TypeComparator::matchType($this->method->getType(), $actual, $objOrClass);
```

Both methods are `public static`, so no instance is needed.
