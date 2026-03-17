<?php
    /**
     * Project Name:    Wingman Helix - Test Fixtures
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests\Fixtures;

    use Attribute;

    // ─── Attribute ────────────────────────────────────────────────────────────────

    /**
     * A sample PHP attribute used to exercise attribute-detection contract terms.
     */
    #[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
    class SampleAttribute {
        public function __construct (public readonly string $label = '') {}
    }

    // ─── Interface ────────────────────────────────────────────────────────────────

    /**
     * A minimal interface used to test Contract::fromInterface() and interface compliance.
     */
    interface SampleInterface {
        public const string VERSION = '1.0';

        public function getName () : string;
        public function setName (string $name) : void;
    }

    // ─── Main Fixture Class ───────────────────────────────────────────────────────

    /**
     * A comprehensive fixture class covering all member types, access modifiers,
     * and PHP attribute usages needed by the Helix test suite.
     */
    class SampleClass implements SampleInterface {
        public const string VERSION = '1.0';
        protected const string SECRET = 'top-secret';
        private const int LIMIT = 42;

        #[SampleAttribute(label: 'name-prop')]
        public string $name;
        protected int $count = 0;
        public static string $registry = 'default';
        public readonly string $id;
        private string $internal = 'hidden';

        public function __construct (string $name, string $id = 'auto') {
            $this->name = $name;
            $this->id   = $id;
        }

        #[SampleAttribute(label: 'getter')]
        public function getName () : string {
            return $this->name;
        }

        public function setName (string $name) : void {
            $this->name = $name;
        }

        public static function create (string $name) : static {
            return new static($name);
        }

        final public function describe () : string {
            return "SampleClass({$this->name})";
        }

        protected function getCount () : int {
            return $this->count;
        }

        private function internalReset () : void {
            $this->count = 0;
        }
    }

    // ─── Subclass ─────────────────────────────────────────────────────────────────

    /**
     * Extends SampleClass to exercise inheritance in reflection and contract evaluation.
     */
    class SubClass extends SampleClass {
        public function __construct (string $name) {
            parent::__construct($name, 'sub');
        }
    }

    // ─── Violating Class ──────────────────────────────────────────────────────────

    /**
     * A class that deliberately breaks a SampleInterface-based contract:
     * - No VERSION constant.
     * - getName() is private (wrong visibility).
     * - setName() takes int instead of string (wrong parameter type).
     */
    class ViolatingClass {
        public string $name = '';

        private function getName () : string {
            return '';
        }

        public function setName (int $name) : void {}
    }

    // ─── Colliding Names Class ────────────────────────────────────────────────────

    /**
     * Has a constant, a property, and a method all sharing the name "foo".
     * Used to verify that the Inspector's reflection cache uses per-kind keys
     * and does not conflate different member types with the same name.
     */
    class CollidingNamesClass {
        public const string FOO = 'constant';
        public string $foo = 'property';

        public function foo () : string {
            return 'method';
        }
    }

    // ─── Abstract Fixture ─────────────────────────────────────────────────────────

    /**
     * An abstract class containing an abstract method, used for testing
     * MethodIsAbstractTerm and expectAbstract() contract enforcement.
     */
    abstract class AbstractFixture {
        abstract public function compute () : int;

        public function greet () : string {
            return 'hello';
        }
    }

    /**
     * A concrete subclass of AbstractFixture used in tests that require an
     * instantiatable target.
     */
    class ConcreteFixture extends AbstractFixture {
        public function compute () : int {
            return 42;
        }
    }

    // ─── Attributed Constant Fixture ─────────────────────────────────────────────

    /**
     * A class whose constant carries SampleAttribute, used to exercise ConstantHasAttributeTerm.
     */
    class AttributedConstantsClass {
        #[SampleAttribute(label: 'version-tag')]
        public const string TAGGED = '1.0';

        public const string PLAIN = 'plain';
    }

    // ─── Types Fixture ───────────────────────────────────────────────────────────

    /**
     * A class with methods that exercise all type-edge-cases tested by TypeComparatorTest:
     * nullable shorthand, union, intersection, bool aliases, and static.
     */
    class TypesFixture {
        public function nullableString () : ?string { return null; }
        public function unionType ()       : string|int { return 0; }
        public function boolReturn ()      : bool { return true; }
        public function selfReturn ()      : static { return $this; }
    }

    // ─── Static Property Fixture ─────────────────────────────────────────────────

    /**
     * A class with a known static property default value, used to exercise
     * PropertyValueTerm against a class-string target.
     */
    class StaticPropertyFixture {
        public static string $label = 'static-default';
        public string $instance = 'instance-value';
    }
?>
