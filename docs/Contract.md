# Contract

`Wingman\Helix\Contract`

`Contract` is the central object in Helix. It holds a set of member requirements — methods, properties, and constants — and evaluates them against an object or class name. Contracts are reusable, cloneable, and composable.

---

## Creating a Contract

### `new Contract(string $name, array $members = [])`

Creates a contract directly. Rarely needed outside of advanced usage — prefer `create()` or `fromInterface()`.

```php
$contract = new Contract("HasId");
```

### `Contract::create(string $name, callable $callback) : static`

Creates a contract via a builder callback. The callback receives the contract instance and is expected to call `defineMethod()`, `defineProperty()`, or `defineConstant()` on it.

```php
$contract = Contract::create("Cacheable", function (Contract $c) {
    $c->defineMethod("getCacheKey")->expectReturnType("string")->require();
    $c->defineMethod("getTtl")->expectReturnType("int")->require();
});
```

### `Contract::fromInterface(string $interface) : static`

Introspects an existing PHP interface and produces a contract that mirrors its shape. All constants, methods, and properties are captured with their types and access modifiers.

```php
$contract = Contract::fromInterface(Stringable::class);
```

If the interface does not exist, an `InvalidArgumentException` is thrown.

**What is captured:**

| Member type | Captured attributes |
|-------------|---------------------|
| Constants | Name, value, access modifier |
| Methods | Name, access modifier (`public`), return type, parameters (names, types, optional/variadic/reference flags) |
| Properties | Name, type, access modifier |

> PHP interfaces only allow `public` methods and, since PHP 8.4, `public abstract` properties. Constants may be `public`, `protected`, or `private` (PHP 8.1+). `fromInterface()` reflects all of these faithfully.

---

## Defining Members

These methods return the member instance for fluent chaining. Calling the same member name multiple times creates separate entries — each is evaluated independently.

### `defineMethod(string|Method $nameOrObj) : Method`

Adds a method requirement. Returns the `Method` instance to chain expectations on.

```php
$c->defineMethod("save")
    ->expectAccessModifier(AccessModifier::Public)
    ->expectReturnType("bool")
    ->require();
```

Passing an existing `Method` object clones it and registers the clone.

### `defineProperty(string|Property $nameOrObj) : Property`

Adds a property requirement.

```php
$c->defineProperty("id")
    ->expectType("int")
    ->expectAccessModifier(AccessModifier::Protected)
    ->require();
```

### `defineConstant(string|Constant $nameOrObj) : Constant`

Adds a constant requirement.

```php
$c->defineConstant("VERSION")
    ->expectValue("2.0")
    ->expectAccessModifier(AccessModifier::Public)
    ->require();
```

---

## Evaluating Targets

### `isSatisfiedBy(object|string $objOrClass) : bool`

Returns `true` if every member term passes for the given target. Never throws.

```php
if ($contract->isSatisfiedBy($service)) {
    // safe to proceed
}
```

### `validate(object|string $objOrClass, bool $allErrors = false) : void`

Enforces the contract and throws `ContractViolationException` on failure.

- When `$allErrors = false` (default), throws on the **first** failing term.
- When `$allErrors = true`, collects **all** failures and includes them in the exception message, one per line.

```php
// Fail fast.
$contract->validate($obj);

// Collect everything.
try {
    $contract->validate($obj, allErrors: true);
} catch (ContractViolationException $e) {
    echo $e->getMessage();
    $failedContract = $e->getContract();
}
```

The error message formats the target name as:
- FQCN for class strings.
- FQCN for named object instances.
- `anonymous-class@<file>:<line>` for anonymous class instances.

---

## Introspection

| Method | Return type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | The name passed at construction. |
| `getMembers()` | `Member[]` | All registered member objects. |
| `getTerms()` | `ContractTerm[]` | All terms flattened from all members. |
| `__toString()` | `string` | Human-readable summary, e.g. `Contract 'Foo' with 3 members`. |

---

## Exceptions

### `ContractViolationException`

`Wingman\Helix\Exceptions\ContractViolationException`

Extends `RuntimeException`. Thrown by `validate()` and `Inspector::enforce()`.

| Method | Return type | Description |
|--------|-------------|-------------|
| `getContract()` | `Contract` | The contract that was being enforced when the exception was raised. |
| `getMessage()` | `string` | Human-readable description of all (or the first) violation(s). |
