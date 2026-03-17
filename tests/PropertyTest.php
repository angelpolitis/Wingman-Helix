<?php
    /**
     * Project Name:    Wingman Helix - Property Tests
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests;

    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Property;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Property class: construction, expect helpers, sentinel value
     * behaviour, waiving, signature generation, and static existence check.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Constructor & Defaults ───────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "Constructor — Default Values",
            description: "A Property constructed with just a name has null type, no access modifier, null static/readOnly, no value, and is not optional."
        )]
        public function testConstructorDefaultValues () : void {
            $property = new Property("count");

            $this->assertEquals("count", $property->getName());
            $this->assertNull($property->getType());
            $this->assertNull($property->getAccessModifier());
            $this->assertFalse($property->hasValue());
            $this->assertFalse($property->hasDefaultValue());
            $this->assertFalse($property->isOptional());
        }

        // ─── Expect Helpers ──────────────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "expectType() — Stores Type",
            description: "expectType() sets the type and getType() returns it."
        )]
        public function testExpectTypeSetsType () : void {
            $property = (new Property("p"))->expectType("string");

            $this->assertEquals("string", $property->getType());
        }

        #[Group("Property")]
        #[Define(
            name: "expectAccessModifier() — Stores Modifier",
            description: "expectAccessModifier() stores the given AccessModifier enum."
        )]
        public function testExpectAccessModifierStoresModifier () : void {
            $property = (new Property("p"))->expectAccessModifier(AccessModifier::Protected);

            $this->assertEquals(AccessModifier::Protected, $property->getAccessModifier());
        }

        #[Group("Property")]
        #[Define(
            name: "expectStatic() — Marks Property As Static",
            description: "expectStatic(true) marks the property as static."
        )]
        public function testExpectStaticSetsFlag () : void {
            $property = (new Property("p"))->expectStatic();

            $property2 = (new Property("p"))->expectStatic(false);

            # We only have getSignature() and public fields to inspect;
            # check signature contains 'static' when flag is true.
            $this->assertStringContains("static", $property->getSignature());
            $this->assertStringNotContains("static", $property2->getSignature());
        }

        #[Group("Property")]
        #[Define(
            name: "expectReadOnly() — Marks Property As ReadOnly",
            description: "expectReadOnly(true) marks the property as readonly."
        )]
        public function testExpectReadOnlySetsFlag () : void {
            $property = (new Property("p"))->expectReadOnly();

            $this->assertStringContains("readonly", $property->getSignature());
        }

        #[Group("Property")]
        #[Define(
            name: "expectValue() — Sets Value Sentinel",
            description: "expectValue() sets the stored value so that hasValue() returns true."
        )]
        public function testExpectValueSetsValueSentinel () : void {
            $property = new Property("p");

            $this->assertFalse($property->hasValue(), "No value should be set before expectValue().");

            $property->expectValue("hello");

            $this->assertTrue($property->hasValue());
            $this->assertEquals("hello", $property->getValue());
        }

        #[Group("Property")]
        #[Define(
            name: "expectDefaultValue() — Sets Default Value Sentinel",
            description: "expectDefaultValue() sets the stored default so that hasDefaultValue() returns true."
        )]
        public function testExpectDefaultValueSetsDefaultValueSentinel () : void {
            $property = new Property("p");

            $this->assertFalse($property->hasDefaultValue(), "No default value should be set before expectDefaultValue().");

            $property->expectDefaultValue(42);

            $this->assertTrue($property->hasDefaultValue());
        }

        // ─── Waiving ─────────────────────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "waive('value') — Clears Value Sentinel",
            description: "After waiving 'value', hasValue() returns false even if one was previously set."
        )]
        public function testWaiveValueClearsSentinel () : void {
            $property = (new Property("p"))->expectValue("test")->waive("value");

            $this->assertFalse($property->hasValue());
        }

        #[Group("Property")]
        #[Define(
            name: "waive('defaultValue') — Clears Default Value Sentinel",
            description: "After waiving 'defaultValue', hasDefaultValue() returns false."
        )]
        public function testWaiveDefaultValueClearsSentinel () : void {
            $property = (new Property("p"))->expectDefaultValue(99)->waive("defaultValue");

            $this->assertFalse($property->hasDefaultValue());
        }

        #[Group("Property")]
        #[Define(
            name: "waive('type') — Nullifies Type",
            description: "Waiving 'type' sets the type to null."
        )]
        public function testWaiveTypeNullifiesType () : void {
            $property = (new Property("p", "int"))->waive("type");

            $this->assertNull($property->getType());
        }

        // ─── Optional ────────────────────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "isOptional() — Defaults To False",
            description: "A newly created property is required by default."
        )]
        public function testIsOptionalDefaultsFalse () : void {
            $this->assertFalse((new Property("p"))->isOptional());
        }

        // ─── Signature ───────────────────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "getSignature() — Includes Type And Name",
            description: "getSignature() returns a string with the type and the prefixed property name."
        )]
        public function testGetSignatureIncludesTypeAndName () : void {
            $property = (new Property("label", "string"));
            $sig = $property->getSignature();

            $this->assertStringContains("string", $sig);
            $this->assertStringContains("\$label", $sig);
        }

        #[Group("Property")]
        #[Define(
            name: "getSignature() — Includes Default Value",
            description: "When a default value is set, getSignature() includes '= <value>'."
        )]
        public function testGetSignatureIncludesDefaultValue () : void {
            $property = (new Property("flag"))->expectDefaultValue(true);
            $sig = $property->getSignature();

            $this->assertStringContains("=", $sig);
        }

        // ─── Exists ──────────────────────────────────────────────────────────────

        #[Group("Property")]
        #[Define(
            name: "exists() — Returns True For Existing Property",
            description: "Property::exists() returns true for a property that is present on the target class."
        )]
        public function testExistsReturnsTrueForRealProperty () : void {
            $this->assertTrue(Property::exists(SampleClass::class, "name"));
        }

        #[Group("Property")]
        #[Define(
            name: "exists() — Returns False For Missing Property",
            description: "Property::exists() returns false when the property does not exist on the target class."
        )]
        public function testExistsReturnsFalseForMissingProperty () : void {
            $this->assertFalse(Property::exists(SampleClass::class, "noSuchProperty"));
        }
    }
?>
