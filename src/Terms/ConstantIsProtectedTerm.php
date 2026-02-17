<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Is Protected Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use ReflectionClassConstant;
    use Wingman\Helix\Inspector;

    /**
     * Checks that a constant is protected.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantIsProtectedTerm extends ConstantContractTerm {
        /**
         * Evaluates whether a constant is protected on the given class or object.
         * @param object|string $objOrClass The class name or object to check for the constant.
         * @return bool Whether the constant is protected.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $constant = Inspector::getClassReflection($objOrClass)->getReflectionConstant($this->constant->getName());

            if (!($constant instanceof ReflectionClassConstant)) {
                return false;
            }

            return $constant->isProtected();
        }

        /**
         * Gets the error message for when a constant is not protected.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Constant '{$this->constant->getName()}' is not protected.";
        }
    }
?>