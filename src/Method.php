<?php
    /*/
     * Project Name:    Wingman — Helix — Method
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 19 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Terms\MethodMatchesSignatureTerm;

    /**
     * Represents a method definition for contract inspection.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Method extends Member {
        /**
         * A list of property names that can be waived during contract evaluation.
         * @var string[]
         */
        protected static array $waivableProperties = ["static", "final", "abstract", "accessModifier", "parameters", "type"];

        /**
         * The parameters expected by a method.
         * @var Parameter[]
         */
        protected array $parameters = [];

        /**
         * The access modifier of a method (public, protected, private).
         * @var AccessModifier|null
         */
        protected ?AccessModifier $accessModifier = null;

        /**
         * Whether a method is static.
         * @var bool|null
         */
        protected ?bool $static = null;

        /**
         * Whether a method is final.
         * @var bool|null
         */
        protected ?bool $final = null;

        /**
         * Whether a method is abstract.
         * @var bool|null
         */
        protected ?bool $abstract = null;

        /**
         * Whether a method is optional (for interface contracts).
         * @var bool
         */
        protected bool $optional = false;
        
        /**
         * Creates a new method.
         * @param string $name The name of the method.
         * @param string|null $type The return type of the method (optional).
         * @param AccessModifier|string|null $accessModifier The access modifier of the method (default: null).
         * @param bool|null $static Whether the method is static (default: null).
         * @param bool|null $final Whether the method is final (default: null).
         * @param bool|null $abstract Whether the method is abstract (default: null).
         * @param bool $optional Whether the method is optional (default: false).
         * @param Parameter[]|null $parameters The parameters of the method (optional).
         */
        public function __construct (
            string $name,
            ?string $type = null,
            AccessModifier|string|null $accessModifier = null,
            ?bool $static = null,
            ?bool $final = null,
            ?bool $abstract = null,
            bool $optional = false,
            ?array $parameters = null
        ) {
            parent::__construct($name, $type);

            $this->accessModifier = $accessModifier !== null ? AccessModifier::resolve($accessModifier) : null;
            $this->static = $static;
            $this->final = $final;
            $this->abstract = $abstract;
            $this->optional = $optional;
            $this->parameters = $parameters ?? [];

            $term = new MethodMatchesSignatureTerm();
            $term->setContext($this);
            $this->addTerm($term);
        }

        /**
         * Determines if a method exists in a given class or object.
         * @param object|string $target The class name or object to check for the method.
         * @param string $methodName The name of the method to check for.
         * @return bool Whether the method exists.
         */
        public static function exists (object|string $target, string $methodName) : bool {
            return method_exists($target, $methodName);
        }

        /**
         * Expects certain characteristics for a method, such as return type, static/final/abstract status, and parameters.
         * @param string|null $type The return type of the method (optional).
         * @param AccessModifier|string|null $accessModifier The access modifier of the method (default: null).
         * @param bool|null $static Whether the method is static (default: null).
         * @param bool|null $final Whether the method is final (default: null).
         * @param bool|null $abstract Whether the method is abstract (default: null).
         * @param bool $optional Whether the method is optional (default: false).
         * @param Parameter[]|null $parameters An array of parameters to expect for the method (optional).
         * @return static The method.
         */
        public function expect (
            ?string $type = null,
            AccessModifier|string|null $accessModifier = null,
            ?bool $static = null,
            ?bool $final = null,
            ?bool $abstract = null,
            bool $optional = false,
            ?array $parameters = null
        ) : static {
            if ($type !== null) {
                $this->type = $type;
            }
            if ($accessModifier !== null) {
                $this->accessModifier = AccessModifier::resolve($accessModifier);
            }
            if ($static !== null) {
                $this->static = $static;
            }
            if ($final !== null) {
                $this->final = $final;
            }
            if ($abstract !== null) {
                $this->abstract = $abstract;
            }
            $this->optional = $optional;
            if ($parameters !== null) {
                foreach ($parameters as $param) {
                    $this->expectParameter($param);
                }
            }
            return $this;
        }

        /**
         * Expects a method to be abstract or non-abstract.
         * @param bool $isAbstract Whether the method is expected to be abstract (true) or non-abstract (false).
         * @return static The method.
         */
        public function expectAbstract (bool $isAbstract = true) : static {
            $this->abstract = $isAbstract;
            return $this;
        }

        /**
         * Expects a specific access modifier for a method.
         * @param AccessModifier|string $accessModifier The access modifier to expect (public, protected, private).
         * @return static The method.
         */
        public function expectAccessModifier (AccessModifier|string $accessModifier) : static {
            $this->accessModifier = AccessModifier::resolve($accessModifier);
            return $this;
        }

        /**
         * Expects a method to be final or non-final.
         * @param bool $isFinal Whether the method is expected to be final (true) or non-final (false).
         * @return static The method.
         */
        public function expectFinal (bool $isFinal = true) : static {
            $this->final = $isFinal;
            return $this;
        }

        /**
         * Expects a parameter for the method with various options.
         * @param string|Parameter $nameOrObject The name of the parameter or a Parameter object.
         * @param string|null $type The type of the parameter (optional).
         * @param bool $optional Whether the parameter is optional (default: false).
         * @param mixed $defaultValue The default value of the parameter if it's optional (default: null).
         * @param bool $passedByReference Whether the parameter is passed by reference (default: false).
         * @param bool $variadic Whether the parameter is variadic (default: false).
         * @return static The method.
         */
        public function expectParameter (
            string|Parameter $nameOrObject,
            ?string $type = null,
            bool $optional = false,
            mixed $defaultValue = null,
            bool $passedByReference = false,
            bool $variadic = false
        ) : static {
            if ($nameOrObject instanceof Parameter) {
                $this->parameters[] = $nameOrObject;
                return $this;
            }
            $this->parameters[] = new Parameter($nameOrObject, $type, $optional, $defaultValue, $passedByReference, $variadic);
            return $this;
        }

        /**
         * Expects a specific return type for the method.
         * @param string|null $type The return type to expect (optional).
         * @return static The method.
         */
        public function expectReturnType (?string $type) : static {
            $this->type = $type;
            return $this;
        }

        /**
         * Expects a method to be static or non-static.
         * @param bool $isStatic Whether the method is expected to be static (true) or non-static (false).
         * @return static The method.
         */
        public function expectStatic (bool $isStatic = true) : static {
            $this->static = $isStatic;
            return $this;
        }

        /**
         * Expects a specific return type for the method.
         * @param string|null $type The return type to expect (optional).
         * @return static The method.
         */
        public function expectType (?string $type) : static {
            $this->type = $type;
            return $this;
        }

        /**
         * Gets the access modifier of the method.
         * @return AccessModifier|null The access modifier of the method, or `null` if not set.
         */
        public function getAccessModifier () : ?AccessModifier {
            return $this->accessModifier;
        }

        /**
         * Gets the parameters expected by a method.
         * @return Parameter[] An array of parameters expected by the method.
         */
        public function getParameters () : array {
            return $this->parameters;
        }

        /**
         * Gets the return type of a method (alias for getType()).
         * @return string|null The return type of the method, or `null` if not set.
         */
        public function getReturnType () : ?string {
            return $this->getType();
        }

        /**
         * Generates a string representation of a method's signature.
         * @return string The method signature as a string.
         */
        public function getSignature () : string {
            $parts = [];

            if ($this->abstract === true) $parts[] = "abstract";
            if ($this->final === true) $parts[] = "final";
            if ($this->accessModifier) $parts[] = $this->accessModifier->value;
            if ($this->static === true) $parts[] = "static";

            $parts[] = "function";
            
            $paramStrings = array_map(
                fn (Parameter $parameter) => $parameter->getSignature(), 
                $this->parameters
            );

            $signature = implode(' ', $parts) . " {$this->name}(" . implode(", ", $paramStrings) . ")";

            if ($this->type) {
                $signature .= ": " . $this->type;
            }

            return $signature . ';';
        }

        /**
         * Determines if a method is abstract.
         * @return bool|null Whether the method is abstract; `null` if not specified.
         */
        public function isAbstract () : ?bool {
            return $this->abstract;
        }

        /**
         * Determines if a method is final.
         * @return bool|null Whether the method is final; `null` if not specified.
         */
        public function isFinal () : ?bool {
            return $this->final;
        }

        /**
         * Determines if a method is optional.
         * @return bool Whether the method is optional.
         */
        public function isOptional () : bool {
            return $this->optional;
        }

        /**
         * Determines if a method is static.
         * @return bool|null Whether the method is static; `null` if not specified.
         */
        public function isStatic () : ?bool {
            return $this->static;
        }

        /**
         * Marks a method as required or optional for contract evaluation.
         * @param bool $required Whether the method is required (true) or optional (false).
         * @return static The method.
         */
        public function require (bool $required = true) : static {
            $this->optional = !$required;
            return $this;
        }

        /**
         * Waives specific properties of a method during contract evaluation.
         * @param string ...$propertyNames The names of the properties to waive.
         * @return static The method.
         */
        public function waive (string ...$propertyNames) : static {
            foreach ($propertyNames as $propertyName) {
                if (in_array($propertyName, static::$waivableProperties, true)) {
                    $this->$propertyName = null;
                }
            }
            return $this;
        }
    }
?>