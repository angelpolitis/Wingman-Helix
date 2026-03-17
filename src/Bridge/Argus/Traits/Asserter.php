<?php
    /**
     * Project Name:    Wingman Helix - Asserter Trait
     * Created by:      Angel Politis
     * Creation Date:   Feb 26 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix.Bridge.Argus.Traits namespace.
    namespace Wingman\Helix\Bridge\Argus\Traits;

    # Import the following classes to the current scope.
    use Throwable;
    use Wingman\Helix\Contract;

    /**
     * Provides assertion methods for validating that values satisfy or do not satisfy specified contracts and interfaces, and records the results of these assertions using an abstract recordAssertion method that must be implemented by the consuming class.
     * This trait is designed to be used within test classes that need to perform contract-based assertions, allowing for flexible validation logic and consistent recording of assertion outcomes.
     * @package Wingman\Helix\Bridge\Argus\Traits
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    trait Asserter {
        /**
         * Executes a contract assertion, handling both callable and Contract instances, and records the result using the consuming class's recordAssertion method.
         * @param Contract|callable $contract The contract to validate against, which can be an instance of Contract or a callable that defines the contract logic.
         * @param mixed $actual The actual value to be validated against the contract.
         * @param bool $shouldSatisfy Indicates whether the contract is expected to be satisfied (true) or not satisfied (false) by the actual value.
         * @param string $message An optional message providing additional context about the assertion.
         */
        private function runContractAssertion (mixed $contract, mixed $actual, bool $shouldSatisfy, string $message) : void {
            if (is_callable($contract)) {
                $contract = Contract::create("DynamicContract", $contract);
            }

            $satisfied = true;
            $error = "";

            try {
                $contract->validate($actual, true);
            }
            catch (Throwable $e) {
                $satisfied = false;
                $error = $e->getMessage();
            }

            $this->recordAssertion(
                ($satisfied === $shouldSatisfy),
                ($shouldSatisfy ? "Satisfies" : "Does not satisfy") . " Contract: " . $contract->getName(),
                $satisfied ? "Satisfied" : $error,
                $message ?: "Helix contract verification failed."
            );
        }

        /**
         * Records the result of an assertion, including its status, expected and actual values, and an optional message.
         * This method is intended to be implemented by the consuming class to handle assertion recording in a way that fits its architecture.
         * @param bool $status The result of the assertion (true for pass, false for fail).
         * @param mixed $expected The expected value in the assertion.
         * @param mixed $actual The actual value obtained during the test.
         * @param string $message An optional message providing additional context about the assertion.
         */
        abstract protected function recordAssertion (bool $status, mixed $expected, mixed $actual, string $message) : void;

        /**
         * Asserts that a value does not satisfy a specific interface and records the result.
         * @param string $interface The fully qualified name of the interface that the actual value should not satisfy.
         * @param object $actual The actual value obtained during the test.
         * @param string $message An optional message providing additional context about the assertion.
         */
        public function assertNotSatisfiesInterface (string $interface, object $actual, string $message = "") : void {
            $status = !Contract::fromInterface($interface)->isSatisfiedBy($actual);
            $this->recordAssertion($status, "Not satisfies " . $interface, "Actual " . get_class($actual), $message ?: "Failed asserting that objects are not equivalent.");
        }

        /**
         * Asserts that a given value does not satisfy a specified contract, recording the result of the assertion.
         * @param Contract|callable $contract The contract to validate against, which can be an instance of Contract or a callable that defines the contract logic.
         * @param mixed $actual The actual value to be validated against the contract.
         * @param string $message An optional message providing additional context about the assertion.
         */
        public function assertNotSatisfiesContract (Contract|callable $contract, mixed $actual, string $message = "") : void {
            $this->runContractAssertion($contract, $actual, false, $message);
        }

        /**
         * Asserts that a value satisfies a specific interface and records the result.
         * @param string $interface The fully qualified name of the interface that defines the expected structure and behavior.
         * @param object $actual The actual object obtained during the test.
         * @param string $message An optional message providing additional context about the assertion.
         */
        public function assertSatisfiesInterface (string $interface, object $actual, string $message = "") : void {
            $status = Contract::fromInterface($interface)->isSatisfiedBy($actual);
            $this->recordAssertion($status, "Expected equivalent to " . $interface, "Actual " . get_class($actual), $message ?: "Failed asserting that objects are equivalent.");
        }

        /**
         * Asserts that a given value satisfies a specified contract, recording the result of the assertion.
         * @param Contract|callable $contract The contract to validate against, which can be an instance of Contract or a callable that defines the contract logic.
         * @param mixed $actual The actual value to be validated against the contract.
         * @param string $message An optional message providing additional context about the assertion.
         */
        public function assertSatisfiesContract (Contract|callable $contract, mixed $actual, string $message = "") : void {
            $this->runContractAssertion($contract, $actual, true, $message);
        }
    }
?>