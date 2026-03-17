<?php
    /**
     * Project Name:    Wingman Helix - Contract Tests
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests;

    use InvalidArgumentException;
    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Constant;
    use Wingman\Helix\Contract;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Exceptions\ContractViolationException;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Member;
    use Wingman\Helix\Method;
    use Wingman\Helix\Property;
    use Wingman\Helix\Tests\Fixtures\SampleClass;
    use Wingman\Helix\Tests\Fixtures\SampleInterface;
    use Wingman\Helix\Tests\Fixtures\ViolatingClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Contract class: building, factory methods, evaluation, and validation.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ContractTest extends Test {

        /**
         * Resets the Inspector singleton after each test to prevent cross-test contamination.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Building ─────────────────────────────────────────────────────────────

        #[Group("Contract")]
        #[Define(
            name: "create() — Builds And Returns Contract",
            description: "create() passes the contract to the callback and returns a configured Contract instance."
        )]
        public function testCreateReturnsConfiguredContract () : void {
            $contract = Contract::create("acme", function (Contract $c) : void {
                $c->defineMethod("foo");
            });

            $this->assertInstanceOf(Contract::class, $contract, "create() must return a Contract.");
            $this->assertEquals("acme", $contract->getName(), "Name must match the first argument.");
            $this->assertCount(1, $contract->getMembers(), "The one defineMethod() call must add exactly one member.");
        }

        #[Group("Contract")]
        #[Define(
            name: "defineMethod() — Creates And Returns Method",
            description: "defineMethod() adds a Method to the contract and returns it for fluent chaining."
        )]
        public function testForMethodCreatesAndReturnsMethod () : void {
            $contract = new Contract("test");
            $method = $contract->defineMethod("bar");

            $this->assertInstanceOf(Method::class, $method, "defineMethod() must return a Method.");
            $this->assertEquals("bar", $method->getName(), "The returned method must carry the supplied name.");
            $this->assertCount(1, $contract->getMembers(), "One member must have been added.");
        }

        #[Group("Contract")]
        #[Define(
            name: "defineProperty() — Creates And Returns Property",
            description: "defineProperty() adds a Property to the contract and returns it for fluent chaining."
        )]
        public function testForPropertyCreatesAndReturnsProperty () : void {
            $contract = new Contract("test");
            $property = $contract->defineProperty("baz");

            $this->assertInstanceOf(Property::class, $property, "defineProperty() must return a Property.");
            $this->assertEquals("baz", $property->getName());
            $this->assertCount(1, $contract->getMembers());
        }

        #[Group("Contract")]
        #[Define(
            name: "defineConstant() — Creates And Returns Constant",
            description: "defineConstant() adds a Constant to the contract and returns it for fluent chaining."
        )]
        public function testForConstantCreatesAndReturnsConstant () : void {
            $contract = new Contract("test");
            $constant = $contract->defineConstant("QUX");

            $this->assertInstanceOf(Constant::class, $constant, "defineConstant() must return a Constant.");
            $this->assertEquals("QUX", $constant->getName());
            $this->assertCount(1, $contract->getMembers());
        }

        #[Group("Contract")]
        #[Define(
            name: "defineMethod() — Clones Existing Member Object",
            description: "Passing an existing Method instance to defineMethod() clones it, leaving the original unmodified."
        )]
        public function testForMethodClonesExistingMemberObject () : void {
            $original = new Method("clone_me");
            $contract = new Contract("test");
            $added = $contract->defineMethod($original);

            $this->assertTrue($added !== $original, "The added member must be a clone, not the original.");
            $this->assertEquals("clone_me", $added->getName(), "The clone must preserve the name.");
        }

        #[Group("Contract")]
        #[Define(
            name: "getMembers() — Returns All Added Members",
            description: "getMembers() returns an array containing exactly the members added via for*() calls."
        )]
        public function testGetMembersReturnsAllAddedMembers () : void {
            $contract = new Contract("test");
            $contract->defineMethod("m");
            $contract->defineProperty("p");
            $contract->defineConstant("C");

            $this->assertCount(3, $contract->getMembers(), "Three for*() calls must produce three members.");
        }

        #[Group("Contract")]
        #[Define(
            name: "getTerms() — Flattens All Member Terms",
            description: "getTerms() returns a flat array of every ContractTerm from every member."
        )]
        public function testGetTermsFlattensAllMemberTerms () : void {
            $contract = new Contract("test");
            $contract->defineMethod("x");
            $contract->defineProperty("y");

            $terms = $contract->getTerms();

            $this->assertTrue(count($terms) >= 2, "At least one term per member must appear in getTerms().");
        }

        #[Group("Contract")]
        #[Define(
            name: "getName() — Returns Contract Name",
            description: "getName() returns the name supplied to the constructor."
        )]
        public function testGetNameReturnsContractName () : void {
            $contract = new Contract("my-contract");

            $this->assertEquals("my-contract", $contract->getName());
        }

        #[Group("Contract")]
        #[Define(
            name: "__toString() — Includes Name And Count",
            description: "__toString() returns a human-readable string that includes the contract name and member count."
        )]
        public function testToStringIncludesNameAndCount () : void {
            $contract = new Contract("demo");
            $contract->defineMethod("a");
            $contract->defineMethod("b");

            $str = (string) $contract;

            $this->assertStringContains("demo", $str, "String form must contain the contract name.");
            $this->assertStringContains("2", $str, "String form must reference the member count.");
        }

        // ─── fromInterface() ──────────────────────────────────────────────────────

        #[Group("Contract")]
        #[Define(
            name: "fromInterface() — Throws For Non-Existent Interface",
            description: "fromInterface() throws InvalidArgumentException when the given interface does not exist."
        )]
        public function testFromInterfaceThrowsForNonexistentInterface () : void {
            $this->assertThrows(
                InvalidArgumentException::class,
                fn () => Contract::fromInterface("\\No\\Such\\Interface"),
                "fromInterface() must throw for a non-existent interface."
            );
        }

        #[Group("Contract")]
        #[Define(
            name: "fromInterface() — Creates Contract From Interface",
            description: "fromInterface() returns a Contract whose name is the FQCN of the given interface."
        )]
        public function testFromInterfaceCreatesContractWithCorrectName () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $this->assertInstanceOf(Contract::class, $contract);
            $this->assertEquals(SampleInterface::class, $contract->getName());
        }

        #[Group("Contract")]
        #[Define(
            name: "fromInterface() — Includes Methods With Public Modifier",
            description: "fromInterface() adds a method member for every interface method, each expecting AccessModifier::Public."
        )]
        public function testFromInterfaceIncludesMethodsWithPublicModifier () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $methods = array_filter(
                $contract->getMembers(),
                fn (Member $m) : bool => $m instanceof Method
            );

            $this->assertTrue(count($methods) >= 2, "Both interface methods must be represented.");

            foreach ($methods as $method) {
                /** @var Method $method */
                $this->assertEquals(
                    AccessModifier::Public,
                    $method->getAccessModifier(),
                    "Interface methods should be expected as public."
                );
            }
        }

        #[Group("Contract")]
        #[Define(
            name: "fromInterface() — Includes Constant With Correct Value",
            description: "fromInterface() adds a Constant member that expects the correct constant value."
        )]
        public function testFromInterfaceIncludesConstantWithCorrectValue () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $constants = array_filter(
                $contract->getMembers(),
                fn (Member $m) : bool => $m instanceof Constant
            );

            $this->assertCount(1, $constants, "Exactly one constant (VERSION) must be added.");

            /** @var Constant $versionConst */
            $versionConst = array_values($constants)[0];

            $this->assertEquals("VERSION", $versionConst->getName());
            $this->assertEquals("1.0", $versionConst->getValue());
            $this->assertEquals(AccessModifier::Public, $versionConst->getAccessModifier());
        }

        // ─── isSatisfiedBy() ─────────────────────────────────────────────────────

        #[Group("Contract")]
        #[Define(
            name: "isSatisfiedBy() — Returns True For Compliant Class",
            description: "isSatisfiedBy() returns true when every contract term passes for the target."
        )]
        public function testIsSatisfiedByReturnsTrueForCompliantClass () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $this->assertTrue($contract->isSatisfiedBy(SampleClass::class));
        }

        #[Group("Contract")]
        #[Define(
            name: "isSatisfiedBy() — Returns False For Non-Compliant Class",
            description: "isSatisfiedBy() returns false when the target is missing a required member."
        )]
        public function testIsSatisfiedByReturnsFalseForNonCompliantClass () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $this->assertFalse($contract->isSatisfiedBy(ViolatingClass::class));
        }

        #[Group("Contract")]
        #[Define(
            name: "isSatisfiedBy() — Accepts Object Instance",
            description: "isSatisfiedBy() works with an object instance as well as a class name."
        )]
        public function testIsSatisfiedByAcceptsObjectInstance () : void {
            $contract = Contract::create("getName", function (Contract $c) : void {
                $c->defineMethod("getName")->expectAccessModifier(AccessModifier::Public)->require();
            });

            $this->assertTrue($contract->isSatisfiedBy(new SampleClass("test")));
        }

        // ─── validate() ──────────────────────────────────────────────────────────

        #[Group("Contract")]
        #[Define(
            name: "validate() — Does Not Throw For Compliant Target",
            description: "validate() completes silently when the target satisfies all terms."
        )]
        public function testValidateDoesNotThrowForCompliantTarget () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $this->assertNotThrows(
                ContractViolationException::class,
                fn () => $contract->validate(SampleClass::class),
                "validate() must not throw for a compliant target."
            );
        }

        #[Group("Contract")]
        #[Define(
            name: "validate() — Throws ContractViolationException",
            description: "validate() throws ContractViolationException when any term fails."
        )]
        public function testValidateThrowsContractViolationException () : void {
            $contract = Contract::fromInterface(SampleInterface::class);

            $this->assertThrows(
                ContractViolationException::class,
                fn () => $contract->validate(ViolatingClass::class),
                "validate() must throw ContractViolationException for a non-compliant target."
            );
        }

        #[Group("Contract")]
        #[Define(
            name: "validate() — Error Message Contains Class Name",
            description: "The ContractViolationException message includes the target class name."
        )]
        public function testValidateErrorMessageContainsClassName () : void {
            $contract = Contract::create("test", function (Contract $c) : void {
                $c->defineMethod("nonExistentMethod")->require();
            });

            $message = '';
            try {
                $contract->validate(SampleClass::class);
            }
            catch (ContractViolationException $e) {
                $message = $e->getMessage();
            }

            $this->assertStringContains(SampleClass::class, $message, "Exception message must name the target class.");
        }

        #[Group("Contract")]
        #[Define(
            name: "validate() — Stops At First Error By Default",
            description: "With allErrors=false, only the first violation is included in the exception message."
        )]
        public function testValidateStopsAtFirstErrorByDefault () : void {
            $contract = Contract::create("multi", function (Contract $c) : void {
                $c->defineMethod("missingOne")->require();
                $c->defineMethod("missingTwo")->require();
            });

            $message = '';
            try {
                $contract->validate(SampleClass::class, false);
            }
            catch (ContractViolationException $e) {
                $message = $e->getMessage();
            }

            $this->assertStringNotContains("missingTwo", $message,
                "Without allErrors, only the first failure must appear in the message."
            );
        }

        #[Group("Contract")]
        #[Define(
            name: "validate() — Collects All Errors When Requested",
            description: "With allErrors=true, every violated term appears in the exception message."
        )]
        public function testValidateCollectsAllErrorsWhenRequested () : void {
            $contract = Contract::create("multi", function (Contract $c) : void {
                $c->defineMethod("missingOne")->require();
                $c->defineMethod("missingTwo")->require();
            });

            $message = '';
            try {
                $contract->validate(SampleClass::class, true);
            }
            catch (ContractViolationException $e) {
                $message = $e->getMessage();
            }

            $this->assertStringContains("missingOne", $message, "allErrors=true must collect all error messages.");
            $this->assertStringContains("missingTwo", $message, "allErrors=true must include the second failure.");
        }

        #[Group("Contract")]
        #[Define(
            name: "validate() — Uses Filename:Line For Anonymous Class",
            description: "When the target is an anonymous class object, the error message contains filename and line."
        )]
        public function testValidateUsesFilenameLineForAnonymousClass () : void {
            $contract = Contract::create("anon", function (Contract $c) : void {
                $c->defineMethod("missing")->require();
            });

            $anon = new class {};

            $message = '';
            try {
                $contract->validate($anon);
            }
            catch (ContractViolationException $e) {
                $message = $e->getMessage();
            }

            $this->assertStringContains("anonymous-class@", $message,
                "Anonymous class targets must be identified by their file and line."
            );
        }

        #[Group("Contract")]
        #[Define(
            name: "ContractViolationException — Exposes Contract Reference",
            description: "ContractViolationException::getContract() returns the Contract that triggered the exception."
        )]
        public function testViolationExceptionExposesContract () : void {
            $contract = Contract::create("exposed", function (Contract $c) : void {
                $c->defineMethod("ghost")->require();
            });

            $caught = null;
            try {
                $contract->validate(SampleClass::class);
            }
            catch (ContractViolationException $e) {
                $caught = $e;
            }

            $this->assertNotNull($caught, "An exception must have been thrown.");
            $this->assertTrue($caught->getContract() === $contract, "getContract() must return the originating contract.");
        }
    }
?>
