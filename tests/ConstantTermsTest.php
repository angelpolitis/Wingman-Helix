<?php
    /**
     * Project Name:    Wingman Helix - Constant Terms Tests
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
    use Wingman\Argus\Attributes\Requires;
    use Wingman\Argus\Test;
    use Wingman\Helix\Constant;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Terms\ConstantExistsTerm;
    use Wingman\Helix\Terms\ConstantHasAttributeTerm;
    use Wingman\Helix\Terms\ConstantHasTypeTerm;
    use Wingman\Helix\Terms\ConstantIsPrivateTerm;
    use Wingman\Helix\Terms\ConstantIsProtectedTerm;
    use Wingman\Helix\Terms\ConstantIsPublicTerm;
    use Wingman\Helix\Terms\ConstantMatchesSignatureTerm;
    use Wingman\Helix\Tests\Fixtures\AttributedConstantsClass;
    use Wingman\Helix\Tests\Fixtures\SampleAttribute;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for all Constant-related ContractTerm implementations.
     * Each test follows the pattern: instantiate a Constant blueprint, attach the
     * term under test via setContext(), then call evaluate() against a fixture class.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantTermsTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── ConstantExistsTerm ───────────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantExistsTerm — Returns True For Existing Constant",
            description: "The term evaluates to true when the named constant is present on the target class."
        )]
        public function testConstantExistsTermReturnsTrueForRealConstant () : void {
            $constant = new Constant("VERSION");
            $term = new ConstantExistsTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantExistsTerm — Returns False For Missing Constant",
            description: "The term evaluates to false when the named constant does not exist on the target class."
        )]
        public function testConstantExistsTermReturnsFalseForMissingConstant () : void {
            $constant = new Constant("NO_SUCH_CONSTANT");
            $term = new ConstantExistsTerm();
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── ConstantIsPublicTerm ─────────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantIsPublicTerm — Returns True For Public Constant",
            description: "The term evaluates to true for a constant declared public."
        )]
        public function testConstantIsPublicTermReturnsTrueForPublicConstant () : void {
            $constant = new Constant("VERSION");
            $term = new ConstantIsPublicTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantIsPublicTerm — Returns False For Protected Constant",
            description: "The term evaluates to false for a constant declared protected."
        )]
        public function testConstantIsPublicTermReturnsFalseForProtectedConstant () : void {
            $constant = new Constant("SECRET");
            $term = new ConstantIsPublicTerm();
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── ConstantIsProtectedTerm ──────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantIsProtectedTerm — Returns True For Protected Constant",
            description: "The term evaluates to true for a constant declared protected."
        )]
        public function testConstantIsProtectedTermReturnsTrueForProtectedConstant () : void {
            $constant = new Constant("SECRET");
            $term = new ConstantIsProtectedTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        // ─── ConstantIsPrivateTerm ────────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantIsPrivateTerm — Returns True For Private Constant",
            description: "The term evaluates to true for a constant declared private."
        )]
        public function testConstantIsPrivateTermReturnsTrueForPrivateConstant () : void {
            $constant = new Constant("LIMIT");
            $term = new ConstantIsPrivateTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        // ─── ConstantHasTypeTerm ──────────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Requires(type: "php", value: "8.3")]
        #[Define(
            name: "ConstantHasTypeTerm — Returns True For Matching Type (PHP 8.3+)",
            description: "The term evaluates to true when the constant has the expected declared type."
        )]
        public function testConstantHasTypeTermReturnsTrueForMatchingType () : void {
            $constant = new Constant("VERSION");
            $term = new ConstantHasTypeTerm("string");
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Requires(type: "php", value: "8.3")]
        #[Define(
            name: "ConstantHasTypeTerm — Returns False For Wrong Type (PHP 8.3+)",
            description: "The term evaluates to false when the constant's declared type does not match."
        )]
        public function testConstantHasTypeTermReturnsFalseForMismatchedType () : void {
            $constant = new Constant("VERSION");
            $term = new ConstantHasTypeTerm("int");
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── ConstantHasAttributeTerm ─────────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantHasAttributeTerm — Returns True When Attribute Present",
            description: "The term evaluates to true when the named attribute exists on the constant."
        )]
        public function testConstantHasAttributeTermReturnsTrueWhenPresent () : void {
            $constant = new Constant("TAGGED");
            $term = new ConstantHasAttributeTerm(SampleAttribute::class);
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(AttributedConstantsClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantHasAttributeTerm — Returns False When Attribute Absent",
            description: "The term evaluates to false when the attribute is not present on the constant."
        )]
        public function testConstantHasAttributeTermReturnsFalseWhenAbsent () : void {
            $constant = new Constant("PLAIN");
            $term = new ConstantHasAttributeTerm(SampleAttribute::class);
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(AttributedConstantsClass::class));
        }

        // ─── ConstantMatchesSignatureTerm ─────────────────────────────────────────

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantMatchesSignatureTerm — Returns True For Correct Signature",
            description: "The term evaluates to true when the blueprint fully matches the real constant."
        )]
        public function testConstantMatchesSignatureTermReturnsTrueForMatchingSignature () : void {
            $constant = (new Constant("VERSION", AccessModifier::Public))->expectValue("1.0");
            $term = new ConstantMatchesSignatureTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantMatchesSignatureTerm — Returns False For Modifier Mismatch",
            description: "The term evaluates to false when the access modifier does not match."
        )]
        public function testConstantMatchesSignatureTermReturnsFalseForModifierMismatch () : void {
            $constant = new Constant("VERSION", AccessModifier::Private);
            $term = new ConstantMatchesSignatureTerm();
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantMatchesSignatureTerm — Returns False For Value Mismatch",
            description: "The term evaluates to false when the expected value does not match the actual constant value."
        )]
        public function testConstantMatchesSignatureTermReturnsFalseForValueMismatch () : void {
            $constant = (new Constant("VERSION"))->expectValue("99.0");
            $term = new ConstantMatchesSignatureTerm();
            $term->setContext($constant);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        #[Group("ConstantTerms")]
        #[Define(
            name: "ConstantMatchesSignatureTerm — Optional Returns True When Absent",
            description: "When the constant blueprint is optional, the term evaluates to true even if the constant does not exist."
        )]
        public function testConstantMatchesSignatureTermOptionalReturnsTrueWhenAbsent () : void {
            $constant = (new Constant("NO_SUCH_CONSTANT"))->require(false);
            $term = new ConstantMatchesSignatureTerm();
            $term->setContext($constant);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }
    }
?>
