<?php
    /*/
     * Project Name:    Wingman — Helix — Method Contract Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Interfaces\ContractTerm;
    use Wingman\Helix\Member;
    use Wingman\Helix\Method;

    /**
     * Base class for method-related contract terms.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    abstract class MethodContractTerm implements ContractTerm {
        /**
         * The arguments for a contract term evaluation.
         * @var array
         */
        protected array $args = [];

        /**
         * The method context for a contract term.
         * @var Method
         */
        protected Method $method;

        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the contract term is satisfied.
         */
        abstract public function evaluate (object|string $objOrClass) : bool;

        /**
         * Sets the arguments for a contract term evaluation.
         * @param array $args The arguments to set for evaluation.
         * @return static The term.
         */
        public function setArgs (array $args) : static {
            $this->args = $args;
            return $this;
        }

        /**
         * Sets the context for a contract term.
         * @param Member $method The method context to set.
         * @return static The term.
         */
        public function setContext (Member $method) : static {
            $this->method = $method;
            return $this;
        }
    }
?>