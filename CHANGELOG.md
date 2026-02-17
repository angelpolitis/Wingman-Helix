# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — Unreleased

Initial release of Helix, a runtime contract enforcement library for PHP 8.1+, part of the Wingman framework.

### Added

**Core evaluation**
- `Contract` — named collection of member requirements. Supports fluent builder callbacks, cloning, member lookup by name and type, `isSatisfiedBy()` for silent checking, and `validate()` for throwing on the first violation or collecting all violations at once (`allErrors: true`).
- `Contract::create()` — static factory that accepts a name and an optional builder closure.
- `Contract::createFromInterface()` — introspects any PHP interface and creates a contract mirroring its methods (with parameter signatures and return types), constants (with values and access modifiers), and properties (with types and access modifiers).
- `TypeComparator` — utility for comparing PHP type strings with support for union types, intersection types, nullable shorthand, `mixed`, `never`, covariant return types (`self`, `static`, `parent`), and full class hierarchy traversal via `is_a()`.
- `TypeComparator::stringifyType()` — normalises a `ReflectionType` (including `ReflectionUnionType` and `ReflectionIntersectionType`) to a canonical string for storage and comparison.

**Inspection and caching**
- `Inspector` — reflection cache with instance-level WeakMap (for objects) and static array (for class strings) caches keyed per member. Exposes `getClassReflection()`, `getMethodReflection()`, `getPropertyReflection()`, and `getConstantReflection()`.
- `Inspector::getInstance()` / `Inspector::setInstance()` — singleton seam allowing a custom `InspectorInterface` implementation to be injected globally, enabling testing and decoration without global state.
- `Inspector::MAX_STATIC_CACHE_SIZE` — FIFO eviction cap (500 entries) preventing unbounded memory growth in long-running processes.
- `Inspector::enforce()` — delegates to `Contract::validate()` for consistent error formatting.
- `Inspector::complies()` — delegates to `Contract::isSatisfiedBy()`.
- `Inspector::clearCache()` — resets both caches.
- `InspectorInterface` — interface defining the public contract for all inspection and enforcement operations; enables custom inspector implementations and test doubles.

**Contract members**
- `Member` — abstract base class for all contract members. Manages a term list, contract binding, name, and whether the member is required. Provides `addTerm()`, `addTerms()`, `removeTerm()`, `clearTerms()`, `setTerms()`, `getTerms()`, `bindToContract()`, `require()`, `waive()`. Deep-clones its term list on `__clone()`, re-binding each term's context to the new instance.
- `Method` — extends `Member` with `expectAccessModifier()`, `expectReturnType()`, `expectParameter()`, `expectIsStatic()`, `expectIsAbstract()`, `expectIsFinal()`, `expectHasAttribute()`, `expectReturnValue()`. Deep-clones its parameter list on `__clone()`. `waive('parameters')` resets the parameter list to `[]`.
- `Property` — extends `Member` with `expectAccessModifier()`, `expectType()`, `expectValue()`, `expectDefaultValue()`, `expectIsStatic()`, `expectIsReadOnly()`, `expectHasAttribute()`. `waive('value')` and `waive('defaultValue')` reset to the internal `UNSET` sentinel rather than `null`, correctly skipping value checks during evaluation.
- `Constant` — extends `Member` with `expectAccessModifier()`, `expectType()`, `expectValue()`. Same UNSET sentinel behaviour for `waive('value')`.
- `Parameter` — describes a method parameter with name, type, optionality, default value, variadicity, and pass-by-reference flag. `waive('defaultValue')` uses the UNSET sentinel.
- `ContractTerm` interface — defines `evaluate(object|string $target): bool`, `getErrorMessage(): string`, and `setContext(Member $member): void` as the evaluation contract for all terms.
- `AccessModifier` enum — `Public`, `Protected`, `Private` cases used across member expectations.
- `ContractViolationException` — thrown by `Contract::validate()` and `Inspector::enforce()`; carries the violating contract and a descriptive message for each failed term.

**Term system**

*Constant terms*
- `ConstantExistsTerm` — asserts the constant is declared on the target.
- `ConstantHasTypeTerm` — asserts the constant has a specific type (or any type if `""` is passed).
- `ConstantHasAttributeTerm` — asserts the constant carries a PHP attribute, with optional argument value matching.
- `ConstantIsPublicTerm` / `ConstantIsProtectedTerm` / `ConstantIsPrivateTerm` — access-modifier visibility checks.
- `ConstantMatchesSignatureTerm` — composite term evaluating access modifier, type, and value from a `Constant` blueprint.

*Method terms*
- `MethodExistsTerm` — asserts the method is declared on the target.
- `MethodHasTypeTerm` — asserts the return type (or presence of any return type).
- `MethodHasAttributeTerm` — asserts the method carries a PHP attribute, with optional argument value matching.
- `MethodIsPublicTerm` / `MethodIsProtectedTerm` / `MethodIsPrivateTerm` — visibility checks.
- `MethodIsStaticTerm` / `MethodIsAbstractTerm` / `MethodIsFinalTerm` — modifier checks.
- `MethodReturnValueTerm` — calls the method on the target and asserts the return value matches an expected value or satisfies a nested contract.
- `MethodMatchesSignatureTerm` — composite term evaluating access modifier, return type, static, abstract, final flags, and each parameter's name, type, optionality, default value, and variadicity.

*Property terms*
- `PropertyExistsTerm` — asserts the property is declared on the target.
- `PropertyHasTypeTerm` — asserts the property has a specific type (or any type).
- `PropertyHasAttributeTerm` — asserts the property carries a PHP attribute, with optional argument value matching.
- `PropertyIsPublicTerm` / `PropertyIsProtectedTerm` / `PropertyIsPrivateTerm` — visibility checks.
- `PropertyIsStaticTerm` / `PropertyIsReadOnlyTerm` — modifier checks.
- `PropertyValueTerm` — asserts the live value of a property on an object instance, or the default value of a static property when a class name is given; returns a descriptive error when an instance is required but a class string is passed.
- `PropertyMatchesSignatureTerm` — composite term evaluating access modifier, type, static, read-only flags, and live or default value from a `Property` blueprint.

**Proxy**
- `Proxy` — wraps any object as a typed proxy implementing any interface via `Closure::bind()`, forwarding interface-defined method calls to the target without requiring the target to implement the interface.
- `Proxy::createFrom()` — static factory accepting a target object and an interface name; validates the target satisfies the corresponding contract before binding.

**Argus bridge**
- `Bridge\Argus\Traits\Asserter` trait — integrates Helix contract assertions into Argus test suites, exposing `assertSatisfiesContract()` and `assertViolatesContract()` with descriptive failure messages.

**Test suite**
- Full test coverage across 13 test classes: `ContractTest`, `InspectorTest`, `MemberTest`, `MethodTest`, `PropertyTest`, `ConstantTest`, `ParameterTest`, `ProxyTest`, `TypeComparatorTest`, `MethodTermsTest`, `PropertyTermsTest`, `ConstantTermsTest`.
- `Fixtures.php` — shared fixture classes, interfaces, and PHP attributes used across all test files.
- `tests/run.php` — entry point bootstrapping the Argus test runner.

**Documentation**
- `README.md` — overview, installation, quick-start examples, and core concept table.
- `docs/Contract.md` — full `Contract` API reference.
- `docs/Members.md` — `Method`, `Property`, `Constant`, `Parameter`, and `Member` API reference including term extension API and `waive()` behaviour.
- `docs/Inspector.md` — `Inspector` and `InspectorInterface` API reference, caching internals, and DI seam.
- `docs/Terms.md` — all term classes grouped by member type, with `TypeComparator` utility reference.
- `docs/Proxy.md` — `Proxy` API reference with usage examples.
- `docs/Bridge.md` — Argus bridge integration guide.
