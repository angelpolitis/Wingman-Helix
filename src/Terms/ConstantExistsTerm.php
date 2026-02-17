<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Exists Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Constant;

    /**
     * Checks that a constant exists on a class or object.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantExistsTerm extends ConstantContractTerm {
        /**
         * Evaluates whether a constant exists on the given class or object.
         * @param object|string $objOrClass The class name or object to check for the constant.
         * @return bool Whether the constant exists.
         */
        public function evaluate (object|string $objOrClass) : bool {
            return Constant::exists($objOrClass, $this->constant->getName());
        }

        /**
         * Gets the error message for when a constant does not exist.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Constant '{$this->constant->getName()}' does not exist.";
        }
    }
?>