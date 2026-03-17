# Inspector

`Wingman\Helix\Inspector`

`Inspector` is the reflection cache used internally by every term evaluation. It stores `ReflectionClass`, `ReflectionMethod`, `ReflectionProperty`, and `ReflectionClassConstant` objects to avoid redundant reflection calls across a single request or process.

In normal usage you never interact with `Inspector` directly — it operates transparently behind `Contract::validate()` and `Contract::isSatisfiedBy()`. It becomes relevant when:

- You want to enforce a contract imperatively from application code.
- You want to inject a mock or decorating implementation in tests.
- You want to clear the cache between test cases or in long-running processes.

---

## Getting the Instance

### `Inspector::getInstance() : static`

Returns the shared singleton. Created lazily on first call.

```php
$inspector = Inspector::getInstance();
```

### `Inspector::setInstance(InspectorInterface $inspector) : void`

Replaces the shared instance with a custom implementation. All subsequent calls to `getInstance()` return the injected object. Useful for testing or adding decorating behaviour (e.g. logging every reflection call).

```php
$mock = new class implements InspectorInterface { /* ... */ };
Inspector::setInstance($mock);

// Restore default for subsequent tests.
Inspector::setInstance(new Inspector());
```

---

## Enforcement

### `enforce(object|string $target, Contract $contract, bool $allErrors = false) : void`

Delegates to `$contract->validate($target, $allErrors)`. Throws `ContractViolationException` on failure. Provided as a convenience when consumers hold a reference to an `InspectorInterface` rather than a `Contract`.

```php
Inspector::getInstance()->enforce($service, $contract);

// Collect all violations.
Inspector::getInstance()->enforce($service, $contract, allErrors: true);
```

### `complies(object|string $target, Contract $contract) : bool`

Delegates to `$contract->isSatisfiedBy($target)`. Never throws.

```php
if (Inspector::getInstance()->complies($obj, $contract)) {
    // ...
}
```

---

## Reflection Accessors

These methods return reflection objects, pulling from cache when available and populating it otherwise.

| Method | Return type |
|--------|-------------|
| `getClassReflection(object\|string $target)` | `ReflectionClass` |
| `getMethodReflection(object\|string $target, string\|Method $method)` | `ReflectionMethod` |
| `getPropertyReflection(object\|string $target, string\|Property $property)` | `ReflectionProperty` |
| `getConstantReflection(object\|string $target, string\|Constant $constant)` | `ReflectionClassConstant` |

The second argument to the three member-accessor methods accepts either a name string or the typed member object; in both cases the member's name is used as the cache key.

Cache keys are **reflection-type-prefixed**, so a method, property, and constant sharing the same name are stored under separate entries and never collide.

---

## Cache Management

### Sizing

The static (class-string) cache is capped at **500 entries** (`MAX_STATIC_CACHE_SIZE`). When the limit is reached, the oldest entry is evicted (FIFO) before a new one is added.

The object cache uses a `WeakMap`, so entries are automatically discarded when the target object is garbage-collected. There is no size limit on the object cache.

### `clearCache() : void`

Discards all cached reflection objects from the current instance.

```php
Inspector::getInstance()->clearCache();
```

Useful in long-running processes (daemons, workers) that dynamically load and unload classes, or in test suites that need a clean slate between cases.

---

## Dependency Injection

`Inspector` implements `InspectorInterface` (`Wingman\Helix\Interfaces\InspectorInterface`), which declares all seven public methods:

```php
interface InspectorInterface {
    public function clearCache(): void;
    public function complies(object|string $target, Contract $contract): bool;
    public function enforce(object|string $target, Contract $contract, bool $allErrors = false): void;
    public function getClassReflection(object|string $target): ReflectionClass;
    public function getConstantReflection(object|string $target, string|Constant $constant): ReflectionClassConstant;
    public function getMethodReflection(object|string $target, string|Method $method): ReflectionMethod;
    public function getPropertyReflection(object|string $target, string|Property $property): ReflectionProperty;
}
```

Inject your container binding against `InspectorInterface` and call `Inspector::setInstance()` at bootstrap to make the entire term evaluation system use your implementation transparently.

### Test Double Recipe

```php
use Wingman\Helix\Inspector;
use Wingman\Helix\Interfaces\InspectorInterface;

// In setUp():
$this->originalInspector = Inspector::getInstance();
Inspector::setInstance($this->mockInspector);

// In tearDown():
Inspector::setInstance($this->originalInspector);
```
