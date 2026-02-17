<?php
    /*/
     * Project Name:    Wingman — Helix — Method Has Attribute Term
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
     * Checks that a method declares a specific attribute.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodHasAttributeTerm extends MethodContractTerm {
        /**
         * The fully qualified name of the attribute to check for.
         * @var class-string
         */
        protected string $attribute;

        /**
         * Creates a new term.
         * @param class-string $attribute The fully qualified name of the attribute to check for.
         */
        public function __construct (string $attribute) {
            $this->attribute = $attribute;
        }

        /**
         * Evaluates whether a method has the specified attribute.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the method has the attribute.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Method::exists($objOrClass, $this->method->getName())) {
                return false;
            }

            $attributes = Inspector::getMethodReflection($objOrClass, $this->method)->getAttributes($this->attribute);

            if (empty($attributes)) {
                return false;
            }

            $attributeValue = $this->args[0] ?? null;

            if ($attributeValue === null) {
                return true;
            }

            foreach ($attributes as $attribute) {
                $arguments = $attribute->getArguments();
                
                if (in_array($attributeValue, $arguments, true) || $arguments === $attributeValue) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Generates an error message when the term evaluation fails.
         * @return string The error message indicating the failure reason.
         */
        public function getErrorMessage () : string {
            $baseMessage = "Method '{$this->method->getName()}' does not have the required attribute '{$this->attribute}'";
            if (isset($this->args[0])) {
                return "{$baseMessage} with value '{$this->args[0]}'.";
            }
            return "{$baseMessage}.";
        }
    }
?>