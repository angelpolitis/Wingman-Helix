<?php
    /**
     * Project Name:    Wingman Helix - Method Terms Tests
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
    use Wingman\Helix\Method;
    use Wingman\Helix\Terms\MethodExistsTerm;
    use Wingman\Helix\Terms\MethodHasAttributeTerm;
    use Wingman\Helix\Terms\MethodHasTypeTerm;
    use Wingman\Helix\Terms\MethodIsAbstractTerm;
    use Wingman\Helix\Terms\MethodIsFinalTerm;
    use Wingman\Helix\Terms\MethodIsPrivateTerm;
    use Wingman\Helix\Terms\MethodIsProtectedTerm;
    use Wingman\Helix\Terms\MethodIsPublicTerm;
    use Wingman\Helix\Terms\MethodIsStaticTerm;
    use Wingman\Helix\Terms\MethodMatchesSignatureTerm;
    use Wingman\Helix\Terms\MethodReturnValueTerm;
    use Wingman\Helix\Tests\Fixtures\AbstractFixture;
    use Wingman\Helix\Tests\Fixtures\ConcreteFixture;
    use Wingman\Helix\Tests\Fixtures\SampleAttribute;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for all Method-related ContractTerm implementations.
     * Each test follows the pattern: instantiate a Method, attach the term under
     * test via setContext(), then call evaluate() against a fixture class.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodTermsTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── MethodExistsTerm ─────────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodExistsTerm — Returns True For Existing Method",
            description: "The term evaluates to true when the named method is present on the target."
        )]
        public function testMethodExistsTermReturnsTrueForRealMethod () : void {
            $method = new Method("getName");
            $term = new MethodExistsTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodExistsTerm — Returns False For Missing Method",
            description: "The term evaluates to false when the named method is absent from the target."
        )]
        public function testMethodExistsTermReturnsFalseForMissingMethod () : void {
            $method = new Method("noSuchMethod");
            $term = new MethodExistsTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsPublicTerm ───────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsPublicTerm — Returns True For Public Method",
            description: "The term evaluates to true for a method declared public."
        )]
        public function testMethodIsPublicTermReturnsTrueForPublicMethod () : void {
            $method = new Method("getName");
            $term = new MethodIsPublicTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsPublicTerm — Returns False For Private Method",
            description: "The term evaluates to false for a method declared private."
        )]
        public function testMethodIsPublicTermReturnsFalseForPrivateMethod () : void {
            $method = new Method("internalReset");
            $term = new MethodIsPublicTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsProtectedTerm ────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsProtectedTerm — Returns True For Protected Method",
            description: "The term evaluates to true for a method declared protected."
        )]
        public function testMethodIsProtectedTermReturnsTrueForProtectedMethod () : void {
            $method = new Method("getCount");
            $term = new MethodIsProtectedTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsProtectedTerm — Returns False For Public Method",
            description: "The term evaluates to false for a method declared public."
        )]
        public function testMethodIsProtectedTermReturnsFalseForPublicMethod () : void {
            $method = new Method("getName");
            $term = new MethodIsProtectedTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsPrivateTerm ──────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsPrivateTerm — Returns True For Private Method",
            description: "The term evaluates to true for a method declared private."
        )]
        public function testMethodIsPrivateTermReturnsTrueForPrivateMethod () : void {
            $method = new Method("internalReset");
            $term = new MethodIsPrivateTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsStaticTerm ───────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsStaticTerm — Returns True For Static Method",
            description: "The term evaluates to true for a method declared static."
        )]
        public function testMethodIsStaticTermReturnsTrueForStaticMethod () : void {
            $method = new Method("create");
            $term = new MethodIsStaticTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsStaticTerm — Returns False For Instance Method",
            description: "The term evaluates to false when the method is not static."
        )]
        public function testMethodIsStaticTermReturnsFalseForInstanceMethod () : void {
            $method = new Method("getName");
            $term = new MethodIsStaticTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsFinalTerm ────────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsFinalTerm — Returns True For Final Method",
            description: "The term evaluates to true for a method declared final."
        )]
        public function testMethodIsFinalTermReturnsTrueForFinalMethod () : void {
            $method = new Method("describe");
            $term = new MethodIsFinalTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsFinalTerm — Returns False For Non-Final Method",
            description: "The term evaluates to false when the method is not final."
        )]
        public function testMethodIsFinalTermReturnsFalseForNonFinalMethod () : void {
            $method = new Method("getName");
            $term = new MethodIsFinalTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodIsAbstractTerm ─────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsAbstractTerm — Returns True For Abstract Method",
            description: "The term evaluates to true for an abstract method in an abstract class."
        )]
        public function testMethodIsAbstractTermReturnsTrueForAbstractMethod () : void {
            $method = new Method("compute");
            $term = new MethodIsAbstractTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(AbstractFixture::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodIsAbstractTerm — Returns False For Concrete Method",
            description: "The term evaluates to false for a concrete method that implements an abstract parent."
        )]
        public function testMethodIsAbstractTermReturnsFalseForConcreteMethod () : void {
            $method = new Method("compute");
            $term = new MethodIsAbstractTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(ConcreteFixture::class));
        }

        // ─── MethodHasTypeTerm ────────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasTypeTerm — Returns True When Return Type Matches",
            description: "The term evaluates to true when the method's return type equals the expected type."
        )]
        public function testMethodHasTypeTermReturnsTrueForMatchingType () : void {
            $method = new Method("getName");
            $term = new MethodHasTypeTerm("string");
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasTypeTerm — Returns False When Return Type Differs",
            description: "The term evaluates to false when the method's actual return type does not match the expected type."
        )]
        public function testMethodHasTypeTermReturnsFalseForWrongType () : void {
            $method = new Method("getName");
            $term = new MethodHasTypeTerm("int");
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodHasAttributeTerm ───────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasAttributeTerm — Returns True When Attribute Present",
            description: "The term evaluates to true when the named attribute is found on the method."
        )]
        public function testMethodHasAttributeTermReturnsTrueWhenPresent () : void {
            $method = new Method("getName");
            $term = new MethodHasAttributeTerm(SampleAttribute::class);
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasAttributeTerm — Returns False When Attribute Absent",
            description: "The term evaluates to false when the attribute is not present on the method."
        )]
        public function testMethodHasAttributeTermReturnsFalseWhenAbsent () : void {
            $method = new Method("setName");
            $term = new MethodHasAttributeTerm(SampleAttribute::class);
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasAttributeTerm — Matches Attribute Argument Value",
            description: "When setArgs(['getter']) is called the term evaluates to true only if the attribute carries that label."
        )]
        public function testMethodHasAttributeTermMatchesArgumentValue () : void {
            $method = new Method("getName");
            $term = new MethodHasAttributeTerm(SampleAttribute::class);
            $term->setContext($method);
            $term->setArgs(["getter"]);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodHasAttributeTerm — Rejects Wrong Attribute Argument Value",
            description: "When setArgs(['wrong']) is called the term evaluates to false even if the attribute is present."
        )]
        public function testMethodHasAttributeTermRejectsWrongArgumentValue () : void {
            $method = new Method("getName");
            $term = new MethodHasAttributeTerm(SampleAttribute::class);
            $term->setContext($method);
            $term->setArgs(["wrong-label"]);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodReturnValueTerm ────────────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodReturnValueTerm — Matches Declared Return Type",
            description: "The term evaluates to true when the method's return type matches the expected type string."
        )]
        public function testMethodReturnValueTermMatchesReturnType () : void {
            $method = new Method("getName");
            $term = new MethodReturnValueTerm("string");
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodReturnValueTerm — Rejects Wrong Return Type",
            description: "The term evaluates to false when the method's return type does not match the expected type string."
        )]
        public function testMethodReturnValueTermRejectsWrongReturnType () : void {
            $method = new Method("getName");
            $term = new MethodReturnValueTerm("int");
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── MethodMatchesSignatureTerm ───────────────────────────────────────────

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodMatchesSignatureTerm — Returns True For Correct Signature",
            description: "When the Method blueprint matches the real method exactly, the term evaluates to true."
        )]
        public function testMethodMatchesSignatureTermReturnsTrueForMatchingSignature () : void {
            $method = new Method("getName", "string", "public");
            $term = new MethodMatchesSignatureTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodMatchesSignatureTerm — Returns False For Modifier Mismatch",
            description: "The term evaluates to false when the access modifier does not match."
        )]
        public function testMethodMatchesSignatureTermReturnsFalseForModifierMismatch () : void {
            $method = new Method("getName", "string", "private");
            $term = new MethodMatchesSignatureTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodMatchesSignatureTerm — Returns False For Static Mismatch",
            description: "The term evaluates to false when static expectation does not match the real method."
        )]
        public function testMethodMatchesSignatureTermReturnsFalseForStaticMismatch () : void {
            $method = (new Method("getName"))->expectStatic(true);
            $term = new MethodMatchesSignatureTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodMatchesSignatureTerm — Returns False For Return Type Mismatch",
            description: "The term evaluates to false when the expected return type does not match the actual return type."
        )]
        public function testMethodMatchesSignatureTermReturnsFalseForTypeMismatch () : void {
            $method = new Method("getName", "int");
            $term = new MethodMatchesSignatureTerm();
            $term->setContext($method);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("MethodTerms")]
        #[Define(
            name: "MethodMatchesSignatureTerm — Optional Returns True When Method Absent",
            description: "When the method blueprint is optional, the term evaluates to true even if the method does not exist."
        )]
        public function testMethodMatchesSignatureTermOptionalReturnsTrueWhenAbsent () : void {
            $method = (new Method("noSuchMethod"))->require(false);
            $term = new MethodMatchesSignatureTerm();
            $term->setContext($method);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }
    }
?>
