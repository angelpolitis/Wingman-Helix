<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Is Public Term
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
     * Checks that a constant is public.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantIsPublicTerm extends ConstantContractTerm {
        /**
         * Evaluates whether a constant is public on the given class or object.
         * @param object|string $objOrClass The class name or object to check for the constant.
         * @return bool Whether the constant is public.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $constant = Inspector::getClassReflection($objOrClass)->getReflectionConstant($this->constant->getName());

            if (!($constant instanceof ReflectionClassConstant)) {
                return false;
            }

            return $constant->isPublic();
        }

        /**
         * Gets the error message for when a constant is not public.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Constant '{$this->constant->getName()}' is not public.";
        }
    }
?>