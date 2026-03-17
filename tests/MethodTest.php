<?php
    /**
     * Project Name:    Wingman Helix - Method Tests
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
    use Wingman\Helix\Method;
    use Wingman\Helix\Parameter;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Method class: construction, expects, waiving, signature generation,
     * cloning, and static existence check.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Constructor & Defaults ───────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "Constructor — Sets Name Only",
            description: "A Method constructed with just a name has null type, no parameters, and null for all modifiers."
        )]
        public function testConstructorDefaultValues () : void {
            $method = new Method("myMethod");

            $this->assertEquals("myMethod", $method->getName());
            $this->assertNull($method->getType());
            $this->assertNull($method->getAccessModifier());
            $this->assertNull($method->isStatic());
            $this->assertNull($method->isFinal());
            $this->assertNull($method->isAbstract());
            $this->assertFalse($method->isOptional());
            $this->assertCount(0, $method->getParameters());
        }

        #[Group("Method")]
        #[Define(
            name: "Constructor — Accepts Return Type",
            description: "The second constructor argument is stored as the return type."
        )]
        public function testConstructorWithReturnType () : void {
            $method = new Method("doWork", "string");

            $this->assertEquals("string", $method->getType());
        }

        // ─── Expect Helpers ──────────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "expectReturnType() — Stores Type",
            description: "expectReturnType() updates the stored return type."
        )]
        public function testExpectReturnTypeSetsType () : void {
            $method = (new Method("m"))->expectReturnType("int");

            $this->assertEquals("int", $method->getType());
            $this->assertEquals("int", $method->getReturnType());
        }

        #[Group("Method")]
        #[Define(
            name: "expectAccessModifier() — Resolves String To Enum",
            description: "Passing a plain string 'public' to expectAccessModifier() resolves to the AccessModifier enum."
        )]
        public function testExpectAccessModifierResolvesString () : void {
            $method = (new Method("m"))->expectAccessModifier("public");

            $this->assertInstanceOf(AccessModifier::class, $method->getAccessModifier());
            $this->assertEquals(AccessModifier::Public, $method->getAccessModifier());
        }

        #[Group("Method")]
        #[Define(
            name: "expectStatic() — Marks Method As Static",
            description: "expectStatic(true) sets the static flag; expectStatic(false) clears it."
        )]
        public function testExpectStaticSetsFlag () : void {
            $method = new Method("m");
            $method->expectStatic(true);

            $this->assertTrue($method->isStatic());

            $method->expectStatic(false);

            $this->assertFalse($method->isStatic());
        }

        #[Group("Method")]
        #[Define(
            name: "expectFinal() — Marks Method As Final",
            description: "expectFinal(true) sets the final flag."
        )]
        public function testExpectFinalSetsFlag () : void {
            $method = (new Method("m"))->expectFinal();

            $this->assertTrue($method->isFinal());
        }

        #[Group("Method")]
        #[Define(
            name: "expectAbstract() — Marks Method As Abstract",
            description: "expectAbstract(true) sets the abstract flag."
        )]
        public function testExpectAbstractSetsFlag () : void {
            $method = (new Method("m"))->expectAbstract();

            $this->assertTrue($method->isAbstract());
        }

        #[Group("Method")]
        #[Define(
            name: "expectParameter() — Adds By Name",
            description: "expectParameter() called with a plain string name creates and appends a new Parameter."
        )]
        public function testExpectParameterByName () : void {
            $method = (new Method("m"))->expectParameter("value", "string");

            $this->assertCount(1, $method->getParameters());
            $this->assertInstanceOf(Parameter::class, $method->getParameters()[0]);
            $this->assertEquals("value", $method->getParameters()[0]->getName());
        }

        #[Group("Method")]
        #[Define(
            name: "expectParameter() — Adds By Object",
            description: "expectParameter() called with an existing Parameter object appends that exact instance."
        )]
        public function testExpectParameterByObject () : void {
            $param = new Parameter("id", "int");
            $method = (new Method("m"))->expectParameter($param);

            $this->assertCount(1, $method->getParameters());
            $this->assertTrue($method->getParameters()[0] === $param);
        }

        // ─── Optional / Required ─────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "require() — Toggles Optional Flag",
            description: "require(false) marks a method as optional; require(true) marks it as required."
        )]
        public function testRequireTogglesOptionalFlag () : void {
            $method = new Method("m");
            $method->require(false);

            $this->assertTrue($method->isOptional());

            $method->require(true);

            $this->assertFalse($method->isOptional());
        }

        // ─── Signature ───────────────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "getSignature() — Bare Function",
            description: "A method with no modifiers, no return type, and no parameters produces 'function name();'."
        )]
        public function testGetSignatureBareFunction () : void {
            $sig = (new Method("run"))->getSignature();

            $this->assertEquals("function run();", $sig);
        }

        #[Group("Method")]
        #[Define(
            name: "getSignature() — Full Modifiers",
            description: "A method with abstract, public, and return type renders all parts in correct order."
        )]
        public function testGetSignatureWithModifiers () : void {
            $method = (new Method("compute"))
                ->expectAbstract()
                ->expectAccessModifier("public")
                ->expectReturnType("int");

            $this->assertStringContains("abstract", $method->getSignature());
            $this->assertStringContains("public", $method->getSignature());
            $this->assertStringContains(": int", $method->getSignature());
        }

        #[Group("Method")]
        #[Define(
            name: "getSignature() — With Parameter",
            description: "Parameters appear inside the parentheses in the signature string."
        )]
        public function testGetSignatureWithParameter () : void {
            $method = (new Method("setValue"))
                ->expectParameter("val", "string");

            $this->assertStringContains("string \$val", $method->getSignature());
        }

        // ─── Waiving ─────────────────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "waive('type') — Nullifies Return Type",
            description: "Waiving 'type' sets the return type to null."
        )]
        public function testWaiveTypeNullifiesType () : void {
            $method = (new Method("m", "string"))->waive("type");

            $this->assertNull($method->getType());
        }

        #[Group("Method")]
        #[Define(
            name: "waive('accessModifier') — Clears Modifier",
            description: "Waiving 'accessModifier' sets the access modifier to null regardless of the previously stored value."
        )]
        public function testWaiveAccessModifierClearsModifier () : void {
            $method = (new Method("m"))->expectAccessModifier("public")->waive("accessModifier");

            $this->assertNull($method->getAccessModifier());
        }

        #[Group("Method")]
        #[Define(
            name: "waive('parameters') — Empties Parameter List",
            description: "Waiving 'parameters' resets the parameter list to an empty array, not null."
        )]
        public function testWaiveParametersEmptiesList () : void {
            $method = (new Method("m"))
                ->expectParameter("x", "int")
                ->waive("parameters");

            $this->assertCount(0, $method->getParameters());
        }

        // ─── Cloning ─────────────────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "__clone() — Deep-Copies Parameters",
            description: "After cloning, the parameter instances are independent; mutating the original's parameter list does not affect the clone."
        )]
        public function testCloneDeepCopiesParameters () : void {
            $method = (new Method("m"))->expectParameter("a", "int");
            $clone = clone $method;

            $this->assertCount(1, $clone->getParameters(), "Clone must carry the same parameter count.");
            $this->assertTrue(
                $method->getParameters()[0] !== $clone->getParameters()[0],
                "Cloned parameter must be a different object."
            );
        }

        // ─── Exists ──────────────────────────────────────────────────────────────

        #[Group("Method")]
        #[Define(
            name: "exists() — Returns True For Existing Method",
            description: "Method::exists() returns true when the named method is present on the target class."
        )]
        public function testExistsReturnsTrueForRealMethod () : void {
            $this->assertTrue(Method::exists(SampleClass::class, "getName"));
        }

        #[Group("Method")]
        #[Define(
            name: "exists() — Returns False For Missing Method",
            description: "Method::exists() returns false when the named method does not exist on the target class."
        )]
        public function testExistsReturnsFalseForMissingMethod () : void {
            $this->assertFalse(Method::exists(SampleClass::class, "noSuchMethod"));
        }
    }
?>
