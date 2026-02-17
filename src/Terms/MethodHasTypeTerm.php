<?php
    /*/
     * Project Name:    Wingman — Helix — Method Has Type Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 16 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Method;

    /**
     * Checks that a method return type matches an expected type.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodHasTypeTerm extends MethodContractTerm {
        /**
         * The expected return type of a method, or `null` to just check for any return type.
         * @var string|null
         */
        protected ?string $type;

        /**
         * Creates a new term.
         * @param string|null $type The expected return type of the method, or `null` to just check for any return type.
         */
        public function __construct (?string $type = null) {
            $this->type = $type ?: "";
        }

        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Returns true if the method has the required return type, false otherwise.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Method::exists($objOrClass, $this->method->getName())) {
                return false;
            }
            $reflection = Inspector::getMethodReflection($objOrClass, $this->method);
            if ($this->type === "") {
                return $reflection->hasReturnType();
            }
            return $reflection->hasReturnType() && (string) $reflection->getReturnType() === $this->type;
        }

        /**
         * Gets the error message for when a contract term is not satisfied.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Method '{$this->method->getName()}' does not have the required return type '{$this->type}'.";
        }
    }
?>