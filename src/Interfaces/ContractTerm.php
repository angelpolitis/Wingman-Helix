<?php
    /*/
     * Project Name:    Wingman — Helix — Contract Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 16 2026
    /*/

    # Use the Helix.Interfaces namespace.
    namespace Wingman\Helix\Interfaces;

    # Import the following classes to the current scope.
    use Wingman\Helix\Member;

    /**
     * Defines the interface for contract evaluation terms.
     * @package Wingman\Helix\Interfaces
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    interface ContractTerm {
        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the contract term is satisfied.
         */
        public function evaluate (object|string $objOrClass) : bool;

        /**
         * Returns an error message describing the contract violation.
         * @return string The error message for a failed contract evaluation.
         */
        public function getErrorMessage () : string;

        /**
         * Sets the arguments for a contract term evaluation.
         * @param array $args The arguments to set for evaluation.
         * @return static Returns the current instance for method chaining.
         */
        public function setArgs (array $args) : static;

        /**
         * Sets the context for a contract term.
         * @param Member $member The member context to set.
         * @return static Returns the current instance for method chaining.
         */
        public function setContext (Member $member) : static;
    }
?>