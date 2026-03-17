<?php
    /**
     * Project Name:    Wingman Helix - Constant Tests
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
    use Wingman\Helix\Constant;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Constant class: construction, expect helpers, sentinel value
     * behaviour, waiving, signature generation, and static existence check.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Constructor & Defaults ───────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "Constructor — Default Values",
            description: "A Constant constructed with just a name has null access modifier, null type, no value, and is not optional."
        )]
        public function testConstructorDefaultValues () : void {
            $constant = new Constant("VERSION");

            $this->assertEquals("VERSION", $constant->getName());
            $this->assertNull($constant->getAccessModifier());
            $this->assertNull($constant->getType());
            $this->assertFalse($constant->hasValue());
            $this->assertFalse($constant->isOptional());
        }

        // ─── Expect Helpers ──────────────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "expectAccessModifier() — Stores Modifier",
            description: "expectAccessModifier() stores the given AccessModifier enum."
        )]
        public function testExpectAccessModifierStoresModifier () : void {
            $constant = (new Constant("C"))->expectAccessModifier(AccessModifier::Public);

            $this->assertEquals(AccessModifier::Public, $constant->getAccessModifier());
        }

        #[Group("Constant")]
        #[Define(
            name: "expectType() — Stores Type",
            description: "expectType() sets the type so getType() returns it."
        )]
        public function testExpectTypeSetsType () : void {
            $constant = (new Constant("C"))->expectType("string");

            $this->assertEquals("string", $constant->getType());
        }

        #[Group("Constant")]
        #[Define(
            name: "expectValue() — Sets Value Sentinel",
            description: "expectValue() sets the stored value so that hasValue() returns true."
        )]
        public function testExpectValueSetsValueSentinel () : void {
            $constant = new Constant("C");

            $this->assertFalse($constant->hasValue(), "hasValue() must be false before expectValue().");

            $constant->expectValue("1.0");

            $this->assertTrue($constant->hasValue());
            $this->assertEquals("1.0", $constant->getValue());
        }

        // ─── Waiving ─────────────────────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "waive('value') — Clears Value Sentinel",
            description: "After waiving 'value', hasValue() returns false even if one was previously set."
        )]
        public function testWaiveValueClearsSentinel () : void {
            $constant = (new Constant("C"))->expectValue("x")->waive("value");

            $this->assertFalse($constant->hasValue());
        }

        #[Group("Constant")]
        #[Define(
            name: "waive('accessModifier') — Clears Access Modifier",
            description: "After waiving 'accessModifier', getAccessModifier() returns null."
        )]
        public function testWaiveAccessModifierClearsModifier () : void {
            $constant = (new Constant("C"))->expectAccessModifier(AccessModifier::Public)->waive("accessModifier");

            $this->assertNull($constant->getAccessModifier());
        }

        #[Group("Constant")]
        #[Define(
            name: "waive('type') — Nullifies Type",
            description: "After waiving 'type', getType() returns null."
        )]
        public function testWaiveTypeNullifiesType () : void {
            $constant = (new Constant("C"))->expectType("int")->waive("type");

            $this->assertNull($constant->getType());
        }

        // ─── Optional / Required ─────────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "require() — Toggles Optional Flag",
            description: "require(false) marks the constant as optional; require(true) marks it required again."
        )]
        public function testRequireTogglesOptionalFlag () : void {
            $constant = new Constant("C");
            $constant->require(false);

            $this->assertTrue($constant->isOptional());

            $constant->require(true);

            $this->assertFalse($constant->isOptional());
        }

        // ─── Signature ───────────────────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "getSignature() — Bare Constant",
            description: "A constant with no modifier, no type, and no value renders as 'const NAME;'."
        )]
        public function testGetSignatureBareConstant () : void {
            $sig = (new Constant("LIMIT"))->getSignature();

            $this->assertStringContains("const", $sig);
            $this->assertStringContains("LIMIT", $sig);
        }

        #[Group("Constant")]
        #[Define(
            name: "getSignature() — With Modifier And Value",
            description: "When access modifier and value are set, they both appear in the signature."
        )]
        public function testGetSignatureWithModifierAndValue () : void {
            $sig = (new Constant("MAX", AccessModifier::Public))->expectValue(100)->getSignature();

            $this->assertStringContains("public", $sig);
            $this->assertStringContains("const", $sig);
            $this->assertStringContains("MAX", $sig);
            $this->assertStringContains("100", $sig);
        }

        // ─── Exists ──────────────────────────────────────────────────────────────

        #[Group("Constant")]
        #[Define(
            name: "exists() — Returns True For Existing Constant",
            description: "Constant::exists() returns true when the named constant is present on the target class."
        )]
        public function testExistsReturnsTrueForRealConstant () : void {
            $this->assertTrue(Constant::exists(SampleClass::class, "VERSION"));
        }

        #[Group("Constant")]
        #[Define(
            name: "exists() — Returns False For Missing Constant",
            description: "Constant::exists() returns false when the named constant does not exist."
        )]
        public function testExistsReturnsFalseForMissingConstant () : void {
            $this->assertFalse(Constant::exists(SampleClass::class, "NO_SUCH_CONSTANT"));
        }
    }
?>
