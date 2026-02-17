<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Contract Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Constant;
    use Wingman\Helix\Interfaces\ContractTerm;
    use Wingman\Helix\Member;

    /**
     * Base class for constant-related contract terms.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    abstract class ConstantContractTerm implements ContractTerm {
        /**
         * The arguments for a contract term evaluation.
         * @var array
         */
        protected array $args = [];

        /**
         * The constant context for a contract term.
         * @var Constant
         */
        protected Constant $constant;

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
         * @param Member $constant The constant context to set.
         * @return static The term.
         */
        public function setContext (Member $constant) : static {
            $this->constant = $constant;
            return $this;
        }
    }
?>