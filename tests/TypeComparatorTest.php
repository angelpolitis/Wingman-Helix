<?php
    /**
     * Project Name:    Wingman Helix - TypeComparator Tests
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests;

    use ReflectionMethod;
    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\TypeComparator;
    use Wingman\Helix\Tests\Fixtures\SampleClass;
    use Wingman\Helix\Tests\Fixtures\TypesFixture;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the TypeComparator class: matchType() for all type categories,
     * alias normalisation, and self/parent/static resolution; stringifyType() for
     * null input, named, nullable, union, and intersection types.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class TypeComparatorTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── matchType() ──────────────────────────────────────────────────────────

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — Named Type Matches",
            description: "A plain 'string' expected type matches a string return type from reflection."
        )]
        public function testMatchTypeNamedTypeMatches () : void {
            $actual = (new ReflectionMethod(SampleClass::class, "getName"))->getReturnType();

            $this->assertTrue(TypeComparator::matchType("string", $actual, SampleClass::class));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — Named Type Mismatch",
            description: "An 'int' expected type does not match a string return type."
        )]
        public function testMatchTypeNamedTypeMismatch () : void {
            $actual = (new ReflectionMethod(SampleClass::class, "getName"))->getReturnType();

            $this->assertFalse(TypeComparator::matchType("int", $actual, SampleClass::class));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — Nullable Shorthand Matches Nullable Type",
            description: "'?string' expected type matches a '?string' return type from reflection."
        )]
        public function testMatchTypeNullableShorthandMatches () : void {
            $actual = (new ReflectionMethod(TypesFixture::class, "nullableString"))->getReturnType();

            $this->assertTrue(TypeComparator::matchType("?string", $actual, TypesFixture::class));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — Union Type Matches Order-Independently",
            description: "'int|string' matches a 'string|int' union type — order does not matter."
        )]
        public function testMatchTypeUnionTypeOrderIndependent () : void {
            $actual = (new ReflectionMethod(TypesFixture::class, "unionType"))->getReturnType();

            $this->assertTrue(TypeComparator::matchType("int|string", $actual, TypesFixture::class));
            $this->assertTrue(TypeComparator::matchType("string|int", $actual, TypesFixture::class));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — 'boolean' Normalises To 'bool'",
            description: "Passing 'boolean' as the expected type matches a 'bool' return type after normalisation."
        )]
        public function testMatchTypeBooleanAliasNormalises () : void {
            $actual = (new ReflectionMethod(TypesFixture::class, "boolReturn"))->getReturnType();

            $this->assertTrue(TypeComparator::matchType("boolean", $actual, TypesFixture::class));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "matchType() — 'static' Resolves To Class Name",
            description: "An expected string equal to the concrete class matches a 'static' return type."
        )]
        public function testMatchTypeStaticResolvesToClassName () : void {
            $actual = (new ReflectionMethod(SampleClass::class, "create"))->getReturnType();

            $this->assertTrue(TypeComparator::matchType(SampleClass::class, $actual, SampleClass::class));
        }

        // ─── stringifyType() ──────────────────────────────────────────────────────

        #[Group("TypeComparator")]
        #[Define(
            name: "stringifyType() — Null Input Returns Null",
            description: "Passing null to stringifyType() returns null."
        )]
        public function testStringifyTypeNullInputReturnsNull () : void {
            $this->assertNull(TypeComparator::stringifyType(null));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "stringifyType() — Named Type Returns Name",
            description: "A non-nullable named type yields just the type name string."
        )]
        public function testStringifyTypeNamedTypeReturnsName () : void {
            $type = (new ReflectionMethod(SampleClass::class, "getName"))->getReturnType();

            $this->assertEquals("string", TypeComparator::stringifyType($type));
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "stringifyType() — Nullable Named Type Includes 'null'",
            description: "A nullable named type (e.g., ?string) returns 'string|null'."
        )]
        public function testStringifyTypeNullableNamedTypeIncludesNull () : void {
            $type = (new ReflectionMethod(TypesFixture::class, "nullableString"))->getReturnType();
            $result = TypeComparator::stringifyType($type);

            $this->assertStringContains("string", $result);
            $this->assertStringContains("null", $result);
        }

        #[Group("TypeComparator")]
        #[Define(
            name: "stringifyType() — Union Type Returns Parts Joined By Pipe",
            description: "A union return type (string|int) is stringified with '|' as separator."
        )]
        public function testStringifyTypeUnionTypeReturnsPipeSeparated () : void {
            $type = (new ReflectionMethod(TypesFixture::class, "unionType"))->getReturnType();
            $result = TypeComparator::stringifyType($type);

            $this->assertStringContains("|", $result);
        }
    }
?>
