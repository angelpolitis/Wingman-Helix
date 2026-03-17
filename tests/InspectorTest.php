<?php
    /**
     * Project Name:    Wingman Helix - Inspector Tests
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests;

    use ReflectionClass;
    use ReflectionClassConstant;
    use ReflectionMethod;
    use ReflectionProperty;
    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Contract;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Exceptions\ContractViolationException;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Interfaces\InspectorInterface;
    use Wingman\Helix\Tests\Fixtures\CollidingNamesClass;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Inspector class: singleton management, reflection cache, DI seam,
     * cache key isolation, cache eviction, enforce(), and complies().
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class InspectorTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Singleton Management ─────────────────────────────────────────────────

        #[Group("Inspector")]
        #[Define(
            name: "getInstance() — Returns Same Instance",
            description: "Two consecutive calls to getInstance() return the exact same Inspector object."
        )]
        public function testGetInstanceReturnsSameInstanceEachTime () : void {
            $a = Inspector::getInstance();
            $b = Inspector::getInstance();

            $this->assertTrue($a === $b, "getInstance() must always return the same singleton.");
        }

        #[Group("Inspector")]
        #[Define(
            name: "setInstance() — Replaces Global Singleton",
            description: "After setInstance(), getInstance() returns the injected implementation."
        )]
        public function testSetInstanceReplacesGlobalSingleton () : void {
            $mock = new class extends Inspector {};

            Inspector::setInstance($mock);

            $this->assertTrue(Inspector::getInstance() === $mock, "getInstance() must return the injected instance.");
        }

        #[Group("Inspector")]
        #[Define(
            name: "setInstance() — Accepts InspectorInterface Implementation",
            description: "setInstance() accepts any object implementing InspectorInterface."
        )]
        public function testSetInstanceAcceptsAnyInspectorInterface () : void {
            $this->assertImplements(InspectorInterface::class, Inspector::getInstance(),
                "The default Inspector must implement InspectorInterface."
            );
        }

        // ─── Reflection Retrieval ─────────────────────────────────────────────────

        #[Group("Inspector")]
        #[Define(
            name: "getClassReflection() — From Class Name",
            description: "getClassReflection() returns a ReflectionClass when given a class-name string."
        )]
        public function testGetClassReflectionFromClassName () : void {
            $ref = Inspector::getInstance()->getClassReflection(SampleClass::class);

            $this->assertInstanceOf(ReflectionClass::class, $ref);
            $this->assertEquals(SampleClass::class, $ref->getName());
        }

        #[Group("Inspector")]
        #[Define(
            name: "getClassReflection() — From Object Instance",
            description: "getClassReflection() returns a ReflectionClass when given an object instance."
        )]
        public function testGetClassReflectionFromObjectInstance () : void {
            $obj = new SampleClass("test");
            $ref = Inspector::getInstance()->getClassReflection($obj);

            $this->assertInstanceOf(ReflectionClass::class, $ref);
            $this->assertEquals(SampleClass::class, $ref->getName());
        }

        #[Group("Inspector")]
        #[Define(
            name: "getMethodReflection() — Returns ReflectionMethod",
            description: "getMethodReflection() returns a valid ReflectionMethod for an existing method."
        )]
        public function testGetMethodReflectionReturnsReflectionMethod () : void {
            $ref = Inspector::getInstance()->getMethodReflection(SampleClass::class, "getName");

            $this->assertInstanceOf(ReflectionMethod::class, $ref);
            $this->assertEquals("getName", $ref->getName());
        }

        #[Group("Inspector")]
        #[Define(
            name: "getPropertyReflection() — Returns ReflectionProperty",
            description: "getPropertyReflection() returns a valid ReflectionProperty for an existing property."
        )]
        public function testGetPropertyReflectionReturnsReflectionProperty () : void {
            $ref = Inspector::getInstance()->getPropertyReflection(SampleClass::class, "name");

            $this->assertInstanceOf(ReflectionProperty::class, $ref);
            $this->assertEquals("name", $ref->getName());
        }

        #[Group("Inspector")]
        #[Define(
            name: "getConstantReflection() — Returns ReflectionClassConstant",
            description: "getConstantReflection() returns a valid ReflectionClassConstant for an existing constant."
        )]
        public function testGetConstantReflectionReturnsReflectionClassConstant () : void {
            $ref = Inspector::getInstance()->getConstantReflection(SampleClass::class, "VERSION");

            $this->assertInstanceOf(ReflectionClassConstant::class, $ref);
            $this->assertEquals("VERSION", $ref->getName());
        }

        // ─── Cache ────────────────────────────────────────────────────────────────

        #[Group("Inspector")]
        #[Define(
            name: "Cache — Returns Identical Object On Second Call (Static Target)",
            description: "A second call with the same class-name target returns the identical (===) cached object."
        )]
        public function testCacheReturnsSameObjectForStaticTarget () : void {
            $inspector = Inspector::getInstance();

            $first  = $inspector->getClassReflection(SampleClass::class);
            $second = $inspector->getClassReflection(SampleClass::class);

            $this->assertTrue($first === $second, "Cache must return the identical ReflectionClass on repeated calls.");
        }

        #[Group("Inspector")]
        #[Define(
            name: "Cache — Returns Identical Object On Second Call (Object Target)",
            description: "A second call with the same object instance returns the identical (===) cached object."
        )]
        public function testCacheReturnsSameObjectForObjectTarget () : void {
            $inspector = Inspector::getInstance();
            $obj = new SampleClass("cache-test");

            $first  = $inspector->getMethodReflection($obj, "getName");
            $second = $inspector->getMethodReflection($obj, "getName");

            $this->assertTrue($first === $second, "Cache must return the identical ReflectionMethod on repeated calls.");
        }

        #[Group("Inspector")]
        #[Define(
            name: "Cache — Key Isolation Between Member Types",
            description: "A constant, a property, and a method sharing the same name are stored under separate cache keys."
        )]
        public function testCacheKeyIsolationPreventsMemberTypeCollision () : void {
            $inspector = Inspector::getInstance();

            $constantRef = $inspector->getConstantReflection(CollidingNamesClass::class, "FOO");
            $propertyRef = $inspector->getPropertyReflection(CollidingNamesClass::class, "foo");
            $methodRef   = $inspector->getMethodReflection(CollidingNamesClass::class, "foo");

            $this->assertInstanceOf(ReflectionClassConstant::class, $constantRef,
                "The constant cache entry must remain a ReflectionClassConstant after other members are cached."
            );
            $this->assertInstanceOf(ReflectionProperty::class, $propertyRef,
                "The property cache entry must remain a ReflectionProperty."
            );
            $this->assertInstanceOf(ReflectionMethod::class, $methodRef,
                "The method cache entry must remain a ReflectionMethod."
            );
        }

        #[Group("Inspector")]
        #[Define(
            name: "clearCache() — Forces New Reflection Object",
            description: "After clearCache(), the next reflection call returns a fresh (different) object."
        )]
        public function testClearCacheForcesNewReflectionObject () : void {
            $inspector = Inspector::getInstance();

            $before = $inspector->getClassReflection(SampleClass::class);
            $inspector->clearCache();
            $after  = $inspector->getClassReflection(SampleClass::class);

            $this->assertTrue($before !== $after, "After clearCache(), a new ReflectionClass must be created.");
        }

        #[Group("Inspector")]
        #[Define(
            name: "Cache — FIFO Eviction At 500 Entries",
            description: "Inserting 501 distinct class entries evicts the first class from the static cache."
        )]
        public function testCacheFifoEvictionAt500Entries () : void {
            $inspector = new class extends Inspector {
                protected const int MAX_STATIC_CACHE_SIZE = 5;
            };

            $first = $inspector->getClassReflection(SampleClass::class);

            $classes = array_values(array_filter(
                get_declared_classes(),
                fn (string $c) : bool => $c !== SampleClass::class
            ));

            for ($i = 0; $i < 5; $i++) {
                $inspector->getClassReflection($classes[$i]);
            }

            $afterEviction = $inspector->getClassReflection(SampleClass::class);

            $this->assertTrue($first !== $afterEviction,
                "After filling the cache beyond its cap, the first class must have been evicted and re-created."
            );
        }

        // ─── enforce() ───────────────────────────────────────────────────────────

        #[Group("Inspector")]
        #[Define(
            name: "enforce() — Does Not Throw For Compliant Target",
            description: "enforce() completes silently when the target satisfies all contract terms."
        )]
        public function testEnforceDoesNotThrowForCompliantTarget () : void {
            $contract = Contract::create("enforcePass", function (Contract $c) : void {
                $c->defineMethod("getName")->expectAccessModifier(AccessModifier::Public)->require();
            });

            $this->assertNotThrows(
                ContractViolationException::class,
                fn () => Inspector::getInstance()->enforce(SampleClass::class, $contract),
                "enforce() must not throw for a compliant target."
            );
        }

        #[Group("Inspector")]
        #[Define(
            name: "enforce() — Throws ContractViolationException",
            description: "enforce() throws ContractViolationException when the target violates a term."
        )]
        public function testEnforceThrowsContractViolationException () : void {
            $contract = Contract::create("enforceFail", function (Contract $c) : void {
                $c->defineMethod("ghostMethod")->require();
            });

            $this->assertThrows(
                ContractViolationException::class,
                fn () => Inspector::getInstance()->enforce(SampleClass::class, $contract),
                "enforce() must throw ContractViolationException for a non-compliant target."
            );
        }

        #[Group("Inspector")]
        #[Define(
            name: "enforce() — allErrors Propagated To validate()",
            description: "With allErrors=true, all violation messages appear in the exception."
        )]
        public function testEnforceAllErrorsPropagated () : void {
            $contract = Contract::create("allErrors", function (Contract $c) : void {
                $c->defineMethod("missingA")->require();
                $c->defineMethod("missingB")->require();
            });

            $message = '';
            try {
                Inspector::getInstance()->enforce(SampleClass::class, $contract, true);
            }
            catch (ContractViolationException $e) {
                $message = $e->getMessage();
            }

            $this->assertStringContains("missingA", $message);
            $this->assertStringContains("missingB", $message);
        }

        // ─── complies() ──────────────────────────────────────────────────────────

        #[Group("Inspector")]
        #[Define(
            name: "complies() — Returns True For Compliant Target",
            description: "complies() returns true without throwing when all terms pass."
        )]
        public function testCompliesReturnsTrueForCompliantTarget () : void {
            $contract = Contract::create("compliesPass", function (Contract $c) : void {
                $c->defineMethod("getName")->require();
            });

            $this->assertTrue(Inspector::getInstance()->complies(SampleClass::class, $contract));
        }

        #[Group("Inspector")]
        #[Define(
            name: "complies() — Returns False For Non-Compliant Target",
            description: "complies() returns false (without throwing) when any term fails."
        )]
        public function testCompliesReturnsFalseForNonCompliantTarget () : void {
            $contract = Contract::create("compliesFail", function (Contract $c) : void {
                $c->defineMethod("ghost")->require();
            });

            $this->assertFalse(Inspector::getInstance()->complies(SampleClass::class, $contract));
        }
    }
?>
