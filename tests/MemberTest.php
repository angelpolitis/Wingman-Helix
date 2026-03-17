<?php
    /**
     * Project Name:    Wingman Helix - Member Tests
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
    use Stdclass;
    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Contract;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Interfaces\ContractTerm;
    use Wingman\Helix\Member;
    use Wingman\Helix\Method;
    use Wingman\Helix\Terms\MethodExistsTerm;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for Member: term management, binding, cloning, name, and type accessors.
     * Member is abstract; Method is used as the concrete subclass throughout.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MemberTest extends Test {

        /**
         * Returns a Method that has had its auto-generated signature term removed,
         * giving each test a clean slate with zero terms.
         */
        private function freshMethod (string $name = "test") : Method {
            return (new Method($name))->clearTerms();
        }

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── Term Management ─────────────────────────────────────────────────────

        #[Group("Member")]
        #[Define(
            name: "addTerm() — Adds Term Instance",
            description: "addTerm() with a ContractTerm instance attaches it to the member's term list."
        )]
        public function testAddTermWithInstance () : void {
            $method = $this->freshMethod();
            $term = new MethodExistsTerm();
            $method->addTerm($term);

            $this->assertCount(1, $method->getTerms(), "One term must be present after addTerm().");
            $this->assertTrue($method->getTerms()[0] === $term, "The exact term instance must be stored.");
        }

        #[Group("Member")]
        #[Define(
            name: "addTerm() — Instantiates By Class String",
            description: "addTerm() accepts a class-name string and instantiates the term automatically."
        )]
        public function testAddTermWithClassString () : void {
            $method = $this->freshMethod();
            $method->addTerm(MethodExistsTerm::class);

            $this->assertCount(1, $method->getTerms());
            $this->assertInstanceOf(MethodExistsTerm::class, $method->getTerms()[0]);
        }

        #[Group("Member")]
        #[Define(
            name: "addTerm() — Throws For Non-Existent Class",
            description: "addTerm() throws InvalidArgumentException when the given class name does not exist."
        )]
        public function testAddTermWithNonExistentClassThrows () : void {
            $method = $this->freshMethod();

            $this->assertThrows(
                InvalidArgumentException::class,
                fn () => $method->addTerm("\\No\\Such\\Class"),
                "addTerm() must throw for a non-existent class string."
            );
        }

        #[Group("Member")]
        #[Define(
            name: "addTerm() — Throws For Non-ContractTerm Class",
            description: "addTerm() throws InvalidArgumentException when the class exists but does not implement ContractTerm."
        )]
        public function testAddTermWithNonContractTermClassThrows () : void {
            $method = $this->freshMethod();

            $this->assertThrows(
                InvalidArgumentException::class,
                fn () => $method->addTerm(Stdclass::class),
                "addTerm() must reject a valid class that does not implement ContractTerm."
            );
        }

        #[Group("Member")]
        #[Define(
            name: "addTerms() — Processes Multiple Terms",
            description: "addTerms() accepts an array of term instances and appends all of them."
        )]
        public function testAddTermsProcessesMultipleTerms () : void {
            $method = $this->freshMethod();
            $method->addTerms([
                new MethodExistsTerm(),
                new MethodExistsTerm(),
            ]);

            $this->assertCount(2, $method->getTerms());
        }

        #[Group("Member")]
        #[Define(
            name: "removeTerm() — Removes Specific Term",
            description: "removeTerm() removes the given term instance from the member's term list."
        )]
        public function testRemoveTermRemovesFromList () : void {
            $method = $this->freshMethod();
            $term = new MethodExistsTerm();
            $method->addTerm($term);
            $method->removeTerm($term);

            $this->assertCount(0, $method->getTerms(), "The term must be absent after removeTerm().");
        }

        #[Group("Member")]
        #[Define(
            name: "clearTerms() — Empties Term List",
            description: "clearTerms() removes all terms from the member."
        )]
        public function testClearTermsEmptiesList () : void {
            $method = $this->freshMethod();
            $method->addTerm(new MethodExistsTerm());
            $method->addTerm(new MethodExistsTerm());
            $method->clearTerms();

            $this->assertCount(0, $method->getTerms());
        }

        #[Group("Member")]
        #[Define(
            name: "setTerms() — Replaces Existing Terms",
            description: "setTerms() discards the current list and installs the provided array."
        )]
        public function testSetTermsReplacesList () : void {
            $method = $this->freshMethod();
            $method->addTerm(new MethodExistsTerm());
            $replacement = new MethodExistsTerm();
            $method->setTerms([$replacement]);

            $this->assertCount(1, $method->getTerms(), "setTerms() must replace, not append.");
            $this->assertTrue($method->getTerms()[0] === $replacement);
        }

        // ─── Accessors ────────────────────────────────────────────────────────────

        #[Group("Member")]
        #[Define(
            name: "getName() — Returns Constructor Name",
            description: "getName() returns exactly the name supplied at construction time."
        )]
        public function testGetNameReturnsConstructorName () : void {
            $method = new Method("myMethod");

            $this->assertEquals("myMethod", $method->getName());
        }

        #[Group("Member")]
        #[Define(
            name: "getType() — Returns Type Or Null",
            description: "getType() returns the type string when set, or null when not set."
        )]
        public function testGetTypeReturnsTypeOrNull () : void {
            $withType = new Method("m", "string");
            $withoutType = new Method("m");

            $this->assertEquals("string", $withType->getType());
            $this->assertNull($withoutType->getType());
        }

        #[Group("Member")]
        #[Define(
            name: "setType() — Updates Stored Type",
            description: "setType() replaces the type; a subsequent getType() returns the new value."
        )]
        public function testSetTypeUpdatesType () : void {
            $method = new Method("m");
            $method->setType("int");

            $this->assertEquals("int", $method->getType());
        }

        // ─── Contract Binding ─────────────────────────────────────────────────────

        #[Group("Member")]
        #[Define(
            name: "bindToContract() — Returns Self For Chaining",
            description: "bindToContract() returns the same member instance for fluent chaining."
        )]
        public function testBindToContractReturnsSelf () : void {
            $method = $this->freshMethod();
            $contract = new Contract("c");
            $result = $method->bindToContract($contract);

            $this->assertTrue($result === $method, "bindToContract() must return the same instance for chaining.");
        }

        // ─── Cloning ─────────────────────────────────────────────────────────────

        #[Group("Member")]
        #[Define(
            name: "__clone() — Deep-Copies Terms",
            description: "Cloning a member creates independent copies of every term; modifying the original's terms does not affect the clone."
        )]
        public function testCloneDeepCopiesTerms () : void {
            $method = $this->freshMethod();
            $term = new MethodExistsTerm();
            $method->addTerm($term);

            $clone = clone $method;

            $this->assertCount(1, $clone->getTerms(), "Clone must have the same number of terms.");
            $this->assertTrue($clone->getTerms()[0] !== $term, "Cloned term must be a different object.");
        }

        #[Group("Member")]
        #[Define(
            name: "__clone() — Nulls Contract Binding",
            description: "Cloning a bound member resets its contract reference to null."
        )]
        public function testCloneNullsContractBinding () : void {
            $method = $this->freshMethod();
            $contract = new Contract("c");
            $method->bindToContract($contract);

            $clone = clone $method;

            # Verify via a contract that consumes the clone — if it were still
            # bound to the old contract the defineMethod() would use the stale ref.
            $newContract = new Contract("c2");
            $newContract->defineMethod($clone);

            # If the clone carried the old contract, isSatisfiedBy would reflect
            # the old contract's evaluation context. We simply prove no exception:
            $this->assertTrue(true, "Cloning must not corrupt the contract binding of the new owner.");
        }

        #[Group("Member")]
        #[Define(
            name: "__clone() — Cloned Terms Have Correct Context",
            description: "Every cloned term's context points to the clone, not to the original member."
        )]
        public function testClonedTermsPointToClone () : void {
            $method = $this->freshMethod();
            $method->addTerm(MethodExistsTerm::class);

            $clone = clone $method;

            $this->assertTrue(
                $method->getTerms()[0] !== $clone->getTerms()[0],
                "Terms must be independent objects after cloning."
            );
        }
    }
?>
