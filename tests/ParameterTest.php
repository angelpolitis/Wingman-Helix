<?php
    /**
     * Project Name:    Wingman Helix - Parameter Tests
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
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Parameter;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Parameter class: construction, individual expect helpers,
     * sentinel behaviour for default value, exact-name requirement, and signature
     * generation in all variants.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ParameterTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Constructor & Defaults ───────────────────────────────────────────────

        #[Group("Parameter")]
        #[Define(
            name: "Constructor — Default Values",
            description: "A Parameter constructed with just a name has null type, no default value, is not optional, not by-reference, not variadic, and does not require an exact name."
        )]
        public function testConstructorDefaultValues () : void {
            $param = new Parameter("value");

            $this->assertEquals("value", $param->getName());
            $this->assertNull($param->getType());
            $this->assertFalse($param->isOptional());
            $this->assertFalse($param->hasDefaultValue());
            $this->assertNull($param->isPassedByReference());
            $this->assertNull($param->isVariadic());
            $this->assertFalse($param->isExactNameRequired());
        }

        // ─── Expect Helpers ──────────────────────────────────────────────────────

        #[Group("Parameter")]
        #[Define(
            name: "expectType() — Stores Type",
            description: "expectType() sets the type so getType() returns it."
        )]
        public function testExpectTypeSetsType () : void {
            $param = (new Parameter("p"))->expectType("int");

            $this->assertEquals("int", $param->getType());
        }

        #[Group("Parameter")]
        #[Define(
            name: "expectOptional() — Marks As Optional",
            description: "expectOptional(true) marks the parameter as optional."
        )]
        public function testExpectOptionalMarksOptional () : void {
            $param = (new Parameter("p"))->expectOptional();

            $this->assertTrue($param->isOptional());
        }

        #[Group("Parameter")]
        #[Define(
            name: "expectPassedByReference() — Marks As By-Reference",
            description: "expectPassedByReference(true) marks the parameter as passed by reference."
        )]
        public function testExpectPassedByReferenceSetsFlag () : void {
            $param = (new Parameter("p"))->expectPassedByReference();

            $this->assertTrue($param->isPassedByReference());
        }

        #[Group("Parameter")]
        #[Define(
            name: "expectVariadic() — Marks As Variadic",
            description: "expectVariadic(true) marks the parameter as variadic."
        )]
        public function testExpectVariadicSetsFlag () : void {
            $param = (new Parameter("p"))->expectVariadic();

            $this->assertTrue($param->isVariadic());
        }

        #[Group("Parameter")]
        #[Define(
            name: "expectDefaultValue() — Sets Default Value Sentinel",
            description: "expectDefaultValue() sets the stored default so hasDefaultValue() returns true."
        )]
        public function testExpectDefaultValueSetsSentinel () : void {
            $param = new Parameter("p");

            $this->assertFalse($param->hasDefaultValue(), "No default value before expectDefaultValue().");

            $param->expectDefaultValue(42);

            $this->assertTrue($param->hasDefaultValue());
            $this->assertEquals(42, $param->getDefaultValue());
        }

        // ─── Exact Name Requirement ──────────────────────────────────────────────

        #[Group("Parameter")]
        #[Define(
            name: "requireExactName() — Toggles Exact Name Flag",
            description: "requireExactName(true) enables the exact-name requirement; requireExactName(false) disables it."
        )]
        public function testRequireExactNameTogglesFlag () : void {
            $param = (new Parameter("p"))->requireExactName();

            $this->assertTrue($param->isExactNameRequired());

            $param->requireExactName(false);

            $this->assertFalse($param->isExactNameRequired());
        }

        // ─── Signature Generation ────────────────────────────────────────────────

        #[Group("Parameter")]
        #[Define(
            name: "getSignature() — Name Only",
            description: "A parameter with no type, no reference, and no variadic renders just '\$name'."
        )]
        public function testGetSignatureNameOnly () : void {
            $sig = (new Parameter("value"))->getSignature();

            $this->assertEquals("\$value", $sig);
        }

        #[Group("Parameter")]
        #[Define(
            name: "getSignature() — With Type",
            description: "A parameter with a type renders as 'type \$name'."
        )]
        public function testGetSignatureWithType () : void {
            $sig = (new Parameter("count", "int"))->getSignature();

            $this->assertEquals("int \$count", $sig);
        }

        #[Group("Parameter")]
        #[Define(
            name: "getSignature() — Passed By Reference",
            description: "A by-reference parameter prefixes the name with '&'."
        )]
        public function testGetSignatureByReference () : void {
            $sig = (new Parameter("result"))->expectPassedByReference()->getSignature();

            $this->assertStringContains("&\$result", $sig);
        }

        #[Group("Parameter")]
        #[Define(
            name: "getSignature() — Variadic",
            description: "A variadic parameter prefixes the name with '...'."
        )]
        public function testGetSignatureVariadic () : void {
            $sig = (new Parameter("args"))->expectVariadic()->getSignature();

            $this->assertStringContains("...\$args", $sig);
        }

        #[Group("Parameter")]
        #[Define(
            name: "getSignature() — With Default Value",
            description: "When a default value is set, '= <exported value>' is appended to the signature."
        )]
        public function testGetSignatureWithDefaultValue () : void {
            $sig = (new Parameter("flag"))->expectDefaultValue(true)->getSignature();

            $this->assertStringContains("=", $sig);
            $this->assertStringContains("true", $sig);
        }
    }
?>
