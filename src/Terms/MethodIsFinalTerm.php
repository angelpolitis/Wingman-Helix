<?php
    /*/
     * Project Name:    Wingman — Helix — Method Is Final Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Method;

    /**
     * Checks that a method is final.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodIsFinalTerm extends MethodContractTerm {
        /**
         * Evaluates whether a method is final on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the method is final.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Method::exists($objOrClass, $this->method->getName())) {
                return false;
            }
            return Inspector::getMethodReflection($objOrClass, $this->method)->isFinal();
        }

        /**
         * Gets the error message for when a method is not final.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Method '{$this->method->getName()}' is not final.";
        }
    }
?>