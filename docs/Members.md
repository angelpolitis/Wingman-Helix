# Members

This document covers the four building blocks used to define contract requirements: `Method`, `Property`, `Constant`, and `Parameter`. Each is returned by the corresponding `defineMethod()`, `defineProperty()`, or `defineConstant()` call on a `Contract`, and exposes a fluent API for expressing expectations.

---

## Common Behaviour

All three member types (`Method`, `Property`, `Constant`) share the following:

- **Optional vs required.** Every member defaults to optional. Call `->require()` to make it mandatory. When optional, a passing evaluation is assumed if the member is absent on the target.
- **`waive(string ...$names)`** removes a previously set expectation, resetting that attribute to "don't care". Only attributes listed in `$waivableProperties` for each class can be waived.
- **Cloning.** Passing a member instance to `defineMethod()` / `defineProperty()` / `defineConstant()` deep-clones it, so the original is never mutated.

---

## Term Extension API

Every member type exposes a full term management API for attaching custom or additional built-in terms.

### `addTerm(ContractTerm|string $term, mixed $constructorArgs = [], mixed $evaluatorArgs = []) : static`

Attaches a single term to the member. Accepts:

- A `ContractTerm` instance — used directly.
- A class name string — instantiated with `$constructorArgs` (spread as positional arguments), then evaluated with `$evaluatorArgs` passed to `setArgs()`.

```php
// Pass an instance.
$method->addTerm(new MethodHasAttributeTerm(MyAttribute::class));

// Pass a class name with construction args.
$method->addTerm(MethodHasAttributeTerm::class, constructorArgs: [MyAttribute::class]);

// Pass a class name with both construction and evaluation args.
$method->addTerm(MethodHasAttributeTerm::class,
    constructorArgs: [MyAttribute::class],
    evaluatorArgs: ["expectedValue"]
);
```

### `addTerms(array $terms) : static`

Attaches multiple terms at once. Each element may be a `ContractTerm` instance or a `[$termClassOrInstance, $constructorArgs, $evaluatorArgs]` tuple.

### `removeTerm(ContractTerm $term) : static`

Removes a specific term instance from the list (strict identity comparison).

### `clearTerms() : static`

Removes all terms from the member, including any auto-added ones.

### `setTerms(array $terms) : static`

Replaces the entire term list in one call. Equivalent to `clearTerms()` followed by `addTerms()`.

### `getTerms() : ContractTerm[]`

Returns all currently attached terms.

### `bindToContract(Contract $contract) : static`

Binds the member to a specific contract. Called internally by `Contract::defineMethod()` / `defineProperty()` / `defineConstant()`. Cloning a member resets this binding to `null`.

---

## Method

`Wingman\Helix\Method`

Describes a method expected on the target. The underlying term (`MethodMatchesSignatureTerm`) checks: existence, access modifier, `static`/`final`/`abstract` flags, return type, and the full parameter list.

### Constructor

```php
new Method(
    string $name,
    ?string $type = null,
    AccessModifier|string|null $accessModifier = null,
    ?bool $static = null,
    ?bool $final = null,
    ?bool $abstract = null,
    bool $optional = false,
    ?array $parameters = null,
)
```

All attributes default to "don't care" (`null`) unless explicitly set, meaning the term only checks what you specify.

### Fluent Setters

| Method | Description |
|--------|-------------|
| `expectReturnType(?string $type)` | Alias of `expectType()`. Checks the return type. |
| `expectType(?string $type)` | Same as above; preferred for symmetry with other members. |
| `expectAccessModifier(AccessModifier\|string $modifier)` | Checks access visibility. Accepts enum values or strings (`"public"`, `"protected"`, `"private"`). |
| `expectStatic(bool $isStatic = true)` | Checks `static` keyword. |
| `expectFinal(bool $isFinal = true)` | Checks `final` keyword. |
| `expectAbstract(bool $isAbstract = true)` | Checks `abstract` keyword. |
| `expectParameter(string\|Parameter $nameOrObj, ?string $type = null, bool $optional = false, mixed $defaultValue = null, bool $passedByReference = false, bool $variadic = false)` | Appends a parameter expectation. Accepts a `Parameter` instance directly, or a name string with optional inline attributes. See [Parameter](#parameter) below. |
| `require(bool $required = true)` | Marks as required (`true`) or optional (`false`). |
| `waive(string ...$names)` | Resets one or more attributes. Waivable: `"type"`, `"accessModifier"`, `"static"`, `"final"`, `"abstract"`, `"parameters"`. |
| `expect(...)` | Bulk setter accepting all of the above as named arguments. |

### Example

```php
$c->defineMethod("findById")
    ->expectAccessModifier(AccessModifier::Public)
    ->expectStatic()
    ->expectReturnType("static|null")
    ->expectParameter("id", "int")
    ->require();
```

### Getters

| Method | Return type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | Method name. |
| `getType()` | `?string` | Return type string, or `null` if unset. |
| `getReturnType()` | `?string` | Alias of `getType()`. |
| `getAccessModifier()` | `?AccessModifier` | Expected access modifier, or `null`. |
| `getParameters()` | `Parameter[]` | Expected parameter list. |
| `isStatic()` | `?bool` | Expected static flag, or `null` if unset. |
| `isFinal()` | `?bool` | Expected final flag, or `null` if unset. |
| `isAbstract()` | `?bool` | Expected abstract flag, or `null` if unset. |
| `isOptional()` | `bool` | Whether the method is optional. |
| `getSignature()` | `string` | Human-readable representation, e.g. `public function save(): bool;` |
| `exists(object\|string $target, string $name)` | `bool` | Static helper — wraps `method_exists()`. |

---

## Property

`Wingman\Helix\Property`

Describes a property expected on the target. Two terms are evaluated: `PropertyMatchesSignatureTerm` (existence, access modifier, `static`, `readonly`, type) and `PropertyValueTerm` (live value — only when `expectValue()` has been called).

### Constructor

```php
new Property(
    string $name,
    ?string $type = null,
    mixed $value = UNSET,
    mixed $defaultValue = UNSET,
    AccessModifier|string|null $accessModifier = null,
    ?bool $static = null,
    ?bool $readOnly = null,
    bool $optional = false,
)
```

### Fluent Setters

| Method | Description |
|--------|-------------|
| `expectType(string $type)` | Checks that the property has a specific type hint. |
| `expectAccessModifier(AccessModifier $modifier)` | Checks access visibility. |
| `expectStatic(bool $isStatic = true)` | Checks `static` keyword. |
| `expectReadOnly(bool $isReadOnly = true)` | Checks `readonly` keyword (PHP 8.1+). |
| `expectValue(mixed $value)` | Checks the **live runtime value** of the property on the evaluated object, or the static default when evaluating a class string. |
| `expectDefaultValue(mixed $value)` | Checks the declared default value as seen by reflection (not the live runtime value). |
| `require(bool $required = true)` | Marks as required or optional. |
| `waive(string ...$names)` | Resets attributes. Waivable: `"type"`, `"value"`, `"defaultValue"`, `"accessModifier"`, `"static"`, `"readOnly"`. |
| `expect(...)` | Bulk setter. |

### Value Checking Behaviour

`expectValue()` sets a **live value** check. The evaluation behaviour depends on the target:

| Target | Property | Behaviour |
|--------|----------|-----------|
| Object instance | Any | Reads `$reflection->getValue($obj)` and compares with `===`. Fails if the property is uninitialised. |
| Class string | Static | Reads `$reflection->getDefaultValue()` and compares. |
| Class string | Non-static | Always fails with a clear error message — an instance is required to read a live value. |

### Example

```php
$c->defineProperty("status")
    ->expectType("string")
    ->expectAccessModifier(AccessModifier::Protected)
    ->expectReadOnly()
    ->require();
```

### Getters

| Method | Return type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | Property name. |
| `getType()` | `?string` | Expected type, or `null`. |
| `getAccessModifier()` | `?AccessModifier` | Expected modifier, or `null`. |
| `getValue()` | `mixed` | Expected value (only meaningful after `expectValue()`). |
| `hasValue()` | `bool` | Whether a value expectation has been set. |
| `getDefaultValue()` | `mixed` | Expected default value. |
| `hasDefaultValue()` | `bool` | Whether a default value expectation has been set. |
| `isStatic()` | `?bool` | Expected static flag, or `null`. |
| `isReadOnly()` | `?bool` | Expected readonly flag, or `null`. |
| `isOptional()` | `bool` | Whether the property is optional. |
| `getSignature()` | `string` | Human-readable representation, e.g. `protected readonly string $status;` |
| `exists(object\|string $target, string $name)` | `bool` | Static helper — wraps `property_exists()`. |

---

## Constant

`Wingman\Helix\Constant`

Describes a class constant expected on the target. The underlying term (`ConstantMatchesSignatureTerm`) checks: existence, access modifier, typed constant support (PHP 8.3+), and value.

### Constructor

```php
new Constant(
    string $name,
    AccessModifier|string|null $accessModifier = null,
    ?string $type = null,
    mixed $value = UNSET,
    bool $optional = false,
)
```

### Fluent Setters

| Method | Description |
|--------|-------------|
| `expectAccessModifier(AccessModifier $modifier)` | Checks access visibility (`public`, `protected`, `private`). |
| `expectType(string $type)` | Checks the typed constant declaration (PHP 8.3+). |
| `expectValue(mixed $value)` | Checks the constant's value with `===`. |
| `require(bool $required = true)` | Marks as required or optional. |
| `waive(string ...$names)` | Resets attributes. Waivable: `"type"`, `"value"`, `"accessModifier"`. |
| `expect(...)` | Bulk setter. |

### Example

```php
$c->defineConstant("MAX_RETRIES")
    ->expectAccessModifier(AccessModifier::Public)
    ->expectValue(3)
    ->require();
```

### Getters

| Method | Return type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | Constant name. |
| `getType()` | `?string` | Expected typed constant type, or `null`. |
| `getAccessModifier()` | `?AccessModifier` | Expected modifier, or `null`. |
| `getValue()` | `mixed` | Expected value. |
| `hasValue()` | `bool` | Whether a value expectation has been set. |
| `isOptional()` | `bool` | Whether the constant is optional. |
| `getSignature()` | `string` | Human-readable representation, e.g. `public const int MAX_RETRIES = 3;` |
| `exists(object\|string $target, string $name)` | `bool` | Static helper — uses reflection `hasConstant()`. |

---

## Parameter

`Wingman\Helix\Parameter`

Describes a single method parameter. Parameters are added to a `Method` via `expectParameter()` and are evaluated positionally, not by name (unless `requireExactName()` is called).

### Constructor

```php
new Parameter(
    string $name,
    ?string $type = null,
    bool $optional = false,
    mixed $defaultValue = UNSET,
    ?bool $passedByReference = null,
    ?bool $variadic = null,
    bool $exactNameRequired = false,
)
```

### Fluent Setters

| Method | Description |
|--------|-------------|
| `expectType(string $type)` | Checks the parameter's type hint. Supports union and intersection types. |
| `expectOptional(bool $optional = true)` | Checks that the parameter is optional. |
| `expectDefaultValue(mixed $value)` | Checks the parameter's default value with `===`. |
| `expectPassedByReference(bool $byRef = true)` | Checks the `&` reference flag. |
| `expectVariadic(bool $variadic = true)` | Checks the `...` variadic flag. |
| `require(bool $optional = true)` | Alias of `expectOptional()`. |
| `requireExactName(bool $required = true)` | When `true`, the target's parameter must have the exact same name. By default, only position matters. |
| `waive(string ...$names)` | Resets attributes. Waivable: `"type"`, `"optional"`, `"defaultValue"`, `"passedByReference"`, `"variadic"`, `"exactNameRequired"`. |
| `expect(...)` | Bulk setter. |

### Evaluation Rules (within `MethodMatchesSignatureTerm`)

1. The target method must have **at least** as many parameters as the contract specifies.
2. Parameters are matched positionally — `expectedParams[0]` against `actualParams[0]`, and so on.
3. All unspecified attributes (`null`) are ignored — they do not constrain the actual parameter.
4. If the contract marks a parameter as optional, the actual parameter must also be optional.
5. If a default value is set on the contract parameter, the actual parameter must declare the same default value.

### Example

```php
$c->defineMethod("paginate")
    ->expectParameter("page", "int")
    ->expectParameter(
        new Parameter("perPage", "int", optional: true, defaultValue: 15)
    )
    ->require();
```

### Getters

| Method | Return type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | Parameter name. |
| `getType()` | `?string` | Expected type hint, or `null`. |
| `getDefaultValue()` | `mixed` | Expected default value. |
| `hasDefaultValue()` | `bool` | Whether a default value expectation is set. |
| `isOptional()` | `bool` | Whether the parameter is expected to be optional. |
| `isPassedByReference()` | `?bool` | Expected reference flag, or `null`. |
| `isVariadic()` | `?bool` | Expected variadic flag, or `null`. |
| `isExactNameRequired()` | `bool` | Whether exact name matching is enforced. |
| `getSignature()` | `string` | Human-readable, e.g. `int &$id = 0`. |

---

## Access Modifier Enum

`Wingman\Helix\Enums\AccessModifier`

A backed string enum used across `Method`, `Property`, and `Constant`.

| Case | Value |
|------|-------|
| `AccessModifier::Public` | `"public"` |
| `AccessModifier::Protected` | `"protected"` |
| `AccessModifier::Private` | `"private"` |

### `AccessModifier::resolve(self|string $modifier) : static`

Resolves a string (`"public"`, `"protected"`, `"private"`) or existing enum case to an `AccessModifier`. Throws `ValueError` on unrecognised input.
