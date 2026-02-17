<?php
    /*/
     * Project Name:    Wingman — Helix — Property Contract Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Interfaces\ContractTerm;
    use Wingman\Helix\Member;
    use Wingman\Helix\Property;

    /**
     * Base class for property-related contract terms.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    abstract class PropertyContractTerm implements ContractTerm {
        /**
         * The arguments for a contract term evaluation.
         * @var array
         */
        protected array $args = [];
        
        /**
         * The property context for a contract term.
         * @var Property
         */
        protected Property $property;

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
         * @param Member $property The property context to set.
         * @return static The term.
         */
        public function setContext (Member $property) : static {
            $this->property = $property;
            return $this;
        }
    }
?>