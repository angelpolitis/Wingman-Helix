# Bridge — Argus

`Wingman\Helix\Bridge\Argus\Traits\Asserter`

The Argus bridge provides a trait that adds contract- and interface-based assertion methods to any [Wingman Argus](https://github.com/angelpolitis/Wingman-Argus) test class. It translates Helix contract evaluation results into Argus-recorded assertion outcomes.

---

## Requirements

- `wingman/argus` (the consuming test class must implement `recordAssertion()`)

---

## Setup

Add the trait to your Argus test class:

```php
use Wingman\Helix\Bridge\Argus\Traits\Asserter;

class MyTest extends Argus\TestCase {
    use Asserter;
}
```

The trait requires one abstract method from the consuming class:

```php
abstract protected function recordAssertion(
    bool $status,
    mixed $expected,
    mixed $actual,
    string $message
): void;
```

Argus `TestCase` provides this, so no additional implementation is needed in normal usage.

---

## Assertion Methods

### `assertSatisfiesContract(Contract|callable $contract, mixed $actual, string $message = "") : void`

Asserts that `$actual` satisfies every term of the given contract. Uses `$allErrors = true` internally, so the failure message includes all violations.

`$contract` may be:

- A `Contract` instance.
- A `callable` that accepts a `Contract` and calls `defineMethod()` / `defineProperty()` / `defineConstant()` on it — a temporary `"DynamicContract"` instance is created automatically.

```php
$this->assertSatisfiesContract($repositoryContract, $myService);

// With a callable shorthand:
$this->assertSatisfiesContract(function (Contract $c) {
    $c->defineMethod("save")->expectReturnType("bool")->require();
}, $myService);
```

### `assertNotSatisfiesContract(Contract|callable $contract, mixed $actual, string $message = "") : void`

Asserts that `$actual` **fails** at least one term of the contract.

```php
$this->assertNotSatisfiesContract($strictContract, $partialImplementation);
```

### `assertSatisfiesInterface(string $interface, object $actual, string $message = "") : void`

Builds a contract from the given interface via `Contract::fromInterface()` and asserts that `$actual` satisfies it.

```php
$this->assertSatisfiesInterface(Serializable::class, $myObject);
```

### `assertNotSatisfiesInterface(string $interface, object $actual, string $message = "") : void`

Asserts that `$actual` does **not** satisfy the derived interface contract.

```php
$this->assertNotSatisfiesInterface(StrictInterface::class, $partialObject);
```

---

## How Failures Are Reported

All four methods delegate to `recordAssertion()` with:

| Parameter | Value |
|-----------|-------|
| `$status` | `true` if the assertion passed, `false` otherwise |
| `$expected` | A human-readable description of what was expected (e.g. `"Satisfies Contract: Serialisable"`) |
| `$actual` | `"Satisfied"` on pass; the full `ContractViolationException` message on failure |
| `$message` | The custom message passed to the assertion method, or a default |

Full violation details — including all term failures when `$allErrors = true` — are surfaced in the `$actual` field of the recorded assertion, making it easy to diagnose exactly which terms failed and why.

---

## Example Test

```php
use Wingman\Helix\Bridge\Argus\Traits\Asserter;
use Wingman\Helix\Contract;
use Wingman\Helix\Enums\AccessModifier;

class ServiceContractTest extends Argus\TestCase {
    use Asserter;

    private Contract $contract;

    public function setUp(): void {
        $this->contract = Contract::create("CacheableService", function (Contract $c) {
            $c->defineMethod("getCacheKey")
                ->expectAccessModifier(AccessModifier::Public)
                ->expectReturnType("string")
                ->require();

            $c->defineProperty("ttl")
                ->expectType("int")
                ->require();
        });
    }

    public function testServiceSatisfiesContract(): void {
        $this->assertSatisfiesContract($this->contract, new MyService());
    }

    public function testLegacyServiceDoesNotSatisfyContract(): void {
        $this->assertNotSatisfiesContract($this->contract, new LegacyService());
    }

    public function testImplementsStringable(): void {
        $this->assertSatisfiesInterface(Stringable::class, new MyService());
    }
}
```
