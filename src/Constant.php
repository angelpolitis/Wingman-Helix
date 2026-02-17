<?php
    /*/
     * Project Name:    Wingman — Helix — Constant
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use ReflectionException;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Terms\ConstantMatchesSignatureTerm;

    /**
     * Represents a constant definition for contract inspection.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Constant extends Member {
        /**
         * A special marker value used to indicate that a property characteristic should be waived during contract evaluation.
         * @var string
         */
        private const string UNSET = "[`[`[`[`[`[__HELIX_UNSET__]`]`]`]`]`]";

        /**
         * A list of property names that can be waived during contract evaluation.
         * @var string[]
         */
        protected static array $waivableProperties = ["type", "value", "accessModifier"];

        /**
         * The access modifier of a constant (public, protected, private).
         * @var AccessModifier|null
         */
        protected ?AccessModifier $accessModifier = null;

        /**
         * The value of a constant.
         * @var mixed
         */
        protected mixed $value = self::UNSET;

        /**
         * Whether a constant is optional (i.e., can be absent without causing contract evaluation to fail).
         * @var bool
         */
        protected bool $optional = false;
        
        /**
         * Creates a new constant with a name, an optional type, and an optional value.
         * @param string $name The name of the constant.
         * @param AccessModifier|string|null $accessModifier The access modifier of the constant, or null if not set.
         * @param string|null $type The type of the constant, or null if not set.
         * @param mixed $value The value of the constant, or null if not set.
         * @param bool $optional Whether the constant is optional.
         */
        public function __construct (
            string $name,
            AccessModifier|string|null $accessModifier = null,
            ?string $type = null,
            mixed $value = self::UNSET,
            bool $optional = false
        ) {
            parent::__construct($name, $type);
            if ($accessModifier !== null) {
                $this->accessModifier = AccessModifier::resolve($accessModifier);
            }
            $this->value = $value;
            $this->optional = $optional;

            $term = new ConstantMatchesSignatureTerm();
            $term->setContext($this);
            $this->addTerm($term);
        }

        /**
         * Determines if a constant exists in a given class or object.
         * @param object|string $target The class name or object to check for the constant.
         * @param string $constantName The name of the constant to check for.
         * @return bool Whether the constant exists.
         */
        public static function exists (object|string $target, string $constantName) : bool {
            try {
                return Inspector::getClassReflection($target)->hasConstant($constantName);
            }
            catch (ReflectionException $e) {
                return false;
            }
        }

        /**
         * Expects specific properties for a constant, such as type, value, access modifier, and whether it is optional.
         * @param AccessModifier|string|null $accessModifier The expected access modifier of the constant, or null to ignore this property.
         * @param string|null $type The expected type of the constant, or null to ignore this property.
         * @param mixed $value The expected value of the constant, or null to ignore this property.
         * @param bool $optional Whether the constant is expected to be optional.
         * @return static The constant.
         */
        public function expect (
            AccessModifier|string|null $accessModifier = null,
            ?string $type = null,
            mixed $value = self::UNSET,
            bool $optional = false
        ) : static {
            if ($type !== null) {
                $this->type = $type;
            }
            if ($value !== self::UNSET) {
                $this->value = $value;
            }
            if ($accessModifier !== null) {
                $this->accessModifier = AccessModifier::resolve($accessModifier);
            }
            $this->optional = $optional;
            return $this;
        }

        /**
         * Expects a specific access modifier for a constant.
         * @param AccessModifier $accessModifier The expected access modifier of the constant.
         * @return static The constant.
         */
        public function expectAccessModifier (AccessModifier $accessModifier) : static {
            $this->accessModifier = $accessModifier;
            return $this;
        }

        /**
         * Expects a specific type for a constant.
         * @param string $type The expected type of the constant.
         * @return static The constant.
         */
        public function expectType (string $type) : static {
            $this->type = $type;
            return $this;
        }

        /**
         * Expects a specific value for a constant.
         * @param mixed $value The expected value of the constant.
         * @return static The constant.
         */
        public function expectValue (mixed $value) : static {
            $this->value = $value;
            return $this;
        }

        /**
         * Gets the access modifier of a constant.
         * @return AccessModifier|null The access modifier of the constant, or `null` if not set.
         */
        public function getAccessModifier () : ?AccessModifier {
            return $this->accessModifier;
        }

        /**
         * Marks a constant as required or optional for contract evaluation.
         * @param bool $required Whether the constant is required (true) or optional (false).
         * @return static The constant.
         */
        public function require (bool $required = true) : static {
            $this->optional = !$required;
            return $this;
        }

        /**
         * Gets the signature of a constant, including its access modifier, type, and name.
         * @return string The signature of the constant.
         */
        public function getSignature() : string {
            $parts = [];

            if ($this->accessModifier !== null) {
                $parts[] = $this->accessModifier->value;
            }

            $parts[] = "const";
            
            if ($this->type !== null) {
                $parts[] = $this->type;
            }
            
            $signature = implode(" ", $parts) . " {$this->name}";
            
            if ($this->hasValue()) {
                $signature .= " = " . var_export($this->value, true);
            }
            
            return $signature . ";";
        }

        /**
         * Gets the type of a constant.
         * @return string|null The type of the constant, or `null` if not set.
         */
        public function getType () : ?string {
            return $this->type;
        }

        /**
         * Gets the value of a constant.
         * @param object|string $target The class name or object to get the constant value from.
         * @param string $constantName The name of the constant to get the value of.
         * @return mixed The value of the constant, or `null` if it does not exist.
         */
        public function getValue () : mixed {
            return $this->value;
        }

        /**
         * Checks whether a property has a specific value set.
         * @return bool True if the property has a value set, false otherwise.
         */
        public function hasValue () : bool {
            return $this->value !== self::UNSET;
        }

        /**
         * Checks whether a constant is optional for contract evaluation.
         * @return bool Whether the constant is optional.
         */
        public function isOptional () : bool {
            return $this->optional;
        }

        /**
         * Waives specific properties of a constant during contract evaluation.
         * @param string ...$propertyNames The names of the properties to waive.
         * @return static The constant.
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