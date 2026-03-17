<?php
    /**
     * Project Name:    Wingman Helix - Property Terms Tests
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
    use Wingman\Helix\Terms\PropertyExistsTerm;
    use Wingman\Helix\Terms\PropertyHasAttributeTerm;
    use Wingman\Helix\Terms\PropertyHasTypeTerm;
    use Wingman\Helix\Terms\PropertyIsPrivateTerm;
    use Wingman\Helix\Terms\PropertyIsProtectedTerm;
    use Wingman\Helix\Terms\PropertyIsPublicTerm;
    use Wingman\Helix\Terms\PropertyIsReadOnlyTerm;
    use Wingman\Helix\Terms\PropertyIsStaticTerm;
    use Wingman\Helix\Terms\PropertyMatchesSignatureTerm;
    use Wingman\Helix\Terms\PropertyValueTerm;
    use Wingman\Helix\Tests\Fixtures\SampleAttribute;
    use Wingman\Helix\Tests\Fixtures\SampleClass;
    use Wingman\Helix\Tests\Fixtures\StaticPropertyFixture;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for all Property-related ContractTerm implementations.
     * Each test follows the pattern: instantiate a Property blueprint, attach the
     * term under test via setContext(), then call evaluate() against a fixture.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyTermsTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── PropertyExistsTerm ───────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyExistsTerm — Returns True For Existing Property",
            description: "The term evaluates to true when the named property is present on the target."
        )]
        public function testPropertyExistsTermReturnsTrueForRealProperty () : void {
            $property = new Property("name");
            $term = new PropertyExistsTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyExistsTerm — Returns False For Missing Property",
            description: "The term evaluates to false when the named property does not exist on the target."
        )]
        public function testPropertyExistsTermReturnsFalseForMissingProperty () : void {
            $property = new Property("noSuchProp");
            $term = new PropertyExistsTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyIsPublicTerm ─────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsPublicTerm — Returns True For Public Property",
            description: "The term evaluates to true for a property declared public."
        )]
        public function testPropertyIsPublicTermReturnsTrueForPublicProperty () : void {
            $property = new Property("name");
            $term = new PropertyIsPublicTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsPublicTerm — Returns False For Private Property",
            description: "The term evaluates to false for a property declared private."
        )]
        public function testPropertyIsPublicTermReturnsFalseForPrivateProperty () : void {
            $property = new Property("internal");
            $term = new PropertyIsPublicTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyIsProtectedTerm ──────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsProtectedTerm — Returns True For Protected Property",
            description: "The term evaluates to true for a property declared protected."
        )]
        public function testPropertyIsProtectedTermReturnsTrueForProtectedProperty () : void {
            $property = new Property("count");
            $term = new PropertyIsProtectedTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        // ─── PropertyIsPrivateTerm ────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsPrivateTerm — Returns True For Private Property",
            description: "The term evaluates to true for a property declared private."
        )]
        public function testPropertyIsPrivateTermReturnsTrueForPrivateProperty () : void {
            $property = new Property("internal");
            $term = new PropertyIsPrivateTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        // ─── PropertyIsStaticTerm ─────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsStaticTerm — Returns True For Static Property",
            description: "The term evaluates to true for a property declared static."
        )]
        public function testPropertyIsStaticTermReturnsTrueForStaticProperty () : void {
            $property = new Property("registry");
            $term = new PropertyIsStaticTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsStaticTerm — Returns False For Non-Static Property",
            description: "The term evaluates to false when the property is not static."
        )]
        public function testPropertyIsStaticTermReturnsFalseForNonStaticProperty () : void {
            $property = new Property("name");
            $term = new PropertyIsStaticTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyIsReadOnlyTerm ───────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsReadOnlyTerm — Returns True For Readonly Property",
            description: "The term evaluates to true for a property declared readonly."
        )]
        public function testPropertyIsReadOnlyTermReturnsTrueForReadonlyProperty () : void {
            $property = new Property("id");
            $term = new PropertyIsReadOnlyTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyIsReadOnlyTerm — Returns False For Mutable Property",
            description: "The term evaluates to false for a property that is not readonly."
        )]
        public function testPropertyIsReadOnlyTermReturnsFalseForMutableProperty () : void {
            $property = new Property("name");
            $term = new PropertyIsReadOnlyTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyHasTypeTerm ──────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyHasTypeTerm — Returns True When Type Matches",
            description: "The term evaluates to true when the property's declared type matches the expected type."
        )]
        public function testPropertyHasTypeTermReturnsTrueForMatchingType () : void {
            $property = new Property("name");
            $term = new PropertyHasTypeTerm("string");
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyHasTypeTerm — Returns False When Type Differs",
            description: "The term evaluates to false when the property's actual type does not match."
        )]
        public function testPropertyHasTypeTermReturnsFalseForWrongType () : void {
            $property = new Property("name");
            $term = new PropertyHasTypeTerm("int");
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyHasAttributeTerm ─────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyHasAttributeTerm — Returns True When Attribute Present",
            description: "The term evaluates to true when the specified attribute is found on the property."
        )]
        public function testPropertyHasAttributeTermReturnsTrueWhenPresent () : void {
            $property = new Property("name");
            $term = new PropertyHasAttributeTerm(SampleAttribute::class);
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyHasAttributeTerm — Returns False When Attribute Absent",
            description: "The term evaluates to false when the attribute is not present on the property."
        )]
        public function testPropertyHasAttributeTermReturnsFalseWhenAbsent () : void {
            $property = new Property("count");
            $term = new PropertyHasAttributeTerm(SampleAttribute::class);
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }

        // ─── PropertyValueTerm ────────────────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyValueTerm — Returns True For Matching Instance Value",
            description: "The term evaluates to true when the live instance value equals the expected value."
        )]
        public function testPropertyValueTermReturnsTrueForMatchingInstanceValue () : void {
            $instance = new SampleClass("Alice");
            $property = (new Property("name"))->expectValue("Alice");
            $term = new PropertyValueTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate($instance));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyValueTerm — Returns False For Non-Matching Instance Value",
            description: "The term evaluates to false when the live instance value does not equal the expected value."
        )]
        public function testPropertyValueTermReturnsFalseForMismatchedValue () : void {
            $instance = new SampleClass("Alice");
            $property = (new Property("name"))->expectValue("Bob");
            $term = new PropertyValueTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate($instance));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyValueTerm — Returns True When No Value Expected",
            description: "When no value expectation is set, the term always evaluates to true."
        )]
        public function testPropertyValueTermReturnsTrueWhenNoValueExpected () : void {
            $instance = new SampleClass("Anyone");
            $property = new Property("name");
            $term = new PropertyValueTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate($instance));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyValueTerm — Returns True For Class-String Static Property",
            description: "When the target is a class-string and the property is static, the default value is compared."
        )]
        public function testPropertyValueTermReturnsTrueForClassStringStaticProperty () : void {
            $property = (new Property("label"))->expectValue("static-default");
            $term = new PropertyValueTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(StaticPropertyFixture::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyValueTerm — Returns False For Class-String Non-Static Property",
            description: "When the target is a class-string and the property is non-static, the term evaluates to false."
        )]
        public function testPropertyValueTermReturnsFalseForClassStringNonStaticProperty () : void {
            $property = (new Property("instance"))->expectValue("instance-value");
            $term = new PropertyValueTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(StaticPropertyFixture::class));
        }

        // ─── PropertyMatchesSignatureTerm ─────────────────────────────────────────

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyMatchesSignatureTerm — Returns True For Correct Signature",
            description: "The term evaluates to true when the blueprint fully matches the real property."
        )]
        public function testPropertyMatchesSignatureTermReturnsTrueForMatchingSignature () : void {
            $property = (new Property("name", "string"))
                ->expectAccessModifier(AccessModifier::Public);
            $term = new PropertyMatchesSignatureTerm();
            $term->setContext($property);

            $this->assertTrue($term->evaluate(SampleClass::class));
        }

        #[Group("PropertyTerms")]
        #[Define(
            name: "PropertyMatchesSignatureTerm — Returns False For Modifier Mismatch",
            description: "The term evaluates to false when the access modifier does not match."
        )]
        public function testPropertyMatchesSignatureTermReturnsFalseForModifierMismatch () : void {
            $property = (new Property("name"))
                ->expectAccessModifier(AccessModifier::Private);
            $term = new PropertyMatchesSignatureTerm();
            $term->setContext($property);

            $this->assertFalse($term->evaluate(SampleClass::class));
        }
    }
?>
