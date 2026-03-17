# Proxy

`Wingman\Helix\Proxy`

`Proxy` is a dynamic dispatch layer that wraps any object and translates interface-style method calls to actual method names on the target. Bound closures are cached after the first call, so subsequent calls to the same method pay no additional overhead.

---

> **SECURITY NOTICE:** `Proxy` uses `Closure::bind()` to bind closures to the **target's own instance and class scope**. This deliberately bypasses PHP's visibility system — `private` and `protected` methods on the wrapped object become fully callable through the proxy. Only use this class with objects from trusted, first-party code. Do **not** proxy user-supplied or third-party objects in multi-tenant, plugin, or sandboxed environments, as doing so exposes their internal state and behaviour without restriction.

---

## Creating a Proxy

### `Proxy::from(object $target, array $map = []) : static`

Preferred factory method. Creates a proxy wrapping `$target` with an optional initial method map.

```php
$proxy = Proxy::from($legacyService, [
    "getUser" => "fetchUser",
    "saveUser" => "persistUser",
]);
```

### `new Proxy(object $target, array $map = [])`

Directly constructs a proxy. Equivalent to `Proxy::from()`.

---

## Calling Methods

### `__call(string $method, array $arguments) : mixed`

Intercepts all calls on the proxy object. The execution pipeline is:

1. Check the bound closure cache — if present, call it immediately.
2. Resolve the target method name via the map (`$map[$method] ?? $method`).
3. Validate that the method exists on the target. Throw `RuntimeException` if not.
4. Bind a closure to the target instance and class scope via `Closure::bind()`, cache it, and invoke it.

```php
$result = $proxy->getUser(42);
```

If the mapped target method does not exist, a `RuntimeException` is thrown:

```
Proxy Error: Method 'getUser' (mapped to 'fetchUser') does not exist on LegacyService.
```

---

## Method Mapping

### `map(string $interfaceMethod, string $targetMethod) : static`

Adds or updates a single mapping at runtime. Clears the cached closure for that key so the new mapping takes effect immediately.

```php
$proxy->map("delete", "softDelete");
```

The `$map` constructor argument and `map()` both accept the same format: `["proxyMethodName" => "targetMethodName"]`. When no mapping exists for a name, the method is called by its original name on the target.

---

## Introspection

### `getTarget() : object`

Returns the underlying wrapped object.

```php
$original = $proxy->getTarget();
```

### `hasMethod(string $method) : bool`

Returns `true` if the resolved target method exists on the wrapped object, taking the map into account.

```php
if ($proxy->hasMethod("getUser")) { ... }
```

### `getMethodSignature(string $method) : string`

Returns a string representation of the resolved target method's signature using `ReflectionMethod::__toString()`.

```php
echo $proxy->getMethodSignature("getUser");
```

Throws `RuntimeException` if the method does not exist.

---

## Cache Management

### `clearCache() : static`

Discards all cached bound closures. Forces re-binding on the next call to each method. Useful when the target's class hierarchy has changed (e.g. dynamic decoration) and you need to refresh scope binding.

```php
$proxy->clearCache();
```

---

## Usage with Helix Contracts

`Proxy` is independent of Helix contracts, but they pair naturally. You can enforce a contract against the proxy just like any other object:

```php
$contract = Contract::fromInterface(UserRepositoryInterface::class);
$proxy = Proxy::from($legacyRepo, ["findById" => "fetch"]);

// Contract checks what the proxy exposes, not the raw target.
$contract->validate($proxy);
```

Because `__call` is a magic method, the proxy will only satisfy interface contracts if the mapped methods actually exist on the target.
