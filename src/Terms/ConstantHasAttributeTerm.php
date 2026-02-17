<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Has Attribute Term
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
     * Checks that a constant declares a specific attribute.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantHasAttributeTerm extends ConstantContractTerm {
        /**
         * The name of the attribute to check for.
         * @var string
         */
        protected string $attribute;

        /**
         * Creates a new term.
         * @param string $attribute The name of the attribute to check for.
         */
        public function __construct (string $attribute) {
            $this->attribute = $attribute;
        }

        /**
         * Evaluates whether a constant has the specified attribute.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the constant has the attribute.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $reflection = Inspector::getClassReflection($objOrClass);
            $constant = $reflection->getReflectionConstant($this->constant->getName());

            if (!($constant instanceof ReflectionClassConstant)) {
                return false;
            }

            return !empty($constant->getAttributes($this->attribute));
        }

        /**
         * Gets the error message for when a constant does not have the required attribute.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Constant '{$this->constant->getName()}' does not have the required attribute '{$this->attribute}'.";
        }
    }
?>