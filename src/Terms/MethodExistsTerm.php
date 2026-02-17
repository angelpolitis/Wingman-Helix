<?php
    /*/
     * Project Name:    Wingman — Helix — Method Exists Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 16 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Method;

    /**
     * Checks that a method exists on a class or object.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodExistsTerm extends MethodContractTerm {
        /**
         * Evaluates whether a method exists on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the method exists.
         */
        public function evaluate (object|string $objOrClass) : bool {
            return Method::exists($objOrClass, $this->method->getName());
        }

        /**
         * Gets the error message for when a method does not exist.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Method '{$this->method->getName()}' does not exist.";
        }
    }
?>