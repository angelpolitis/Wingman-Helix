<?php
    /*/
     * Project Name:    Wingman — Helix — Property
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Terms\PropertyMatchesSignatureTerm;
    use Wingman\Helix\Terms\PropertyValueTerm;

    /**
     * Represents a property definition for contract inspection.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Property extends Member {
        /**
         * A special marker value used to indicate that a property characteristic should be waived during contract evaluation.
         * @var string
         */
        private const string UNSET = "[`[`[`[`[`[__HELIX_UNSET__]`]`]`]`]`]";

        /**
         * A list of property names that can be waived during contract evaluation.
         * @var string[]
         */
        protected static array $waivableProperties = ["type", "value", "accessModifier", "static", "readOnly"];

        /**
         * The access modifier of a property (public, protected, private).
         * @var AccessModifier|null
         */
        protected ?AccessModifier $accessModifier = null;

        /**
         * The default value of a property.
         * @var mixed
         */
        protected mixed $defaultValue = self::UNSET;

        /**
         * The value of a property.
         * @var mixed
         */
        protected mixed $value = self::UNSET;

        /**
         * Whether a property is static.
         * @var bool|null
         */
        protected ?bool $static = null;

        /**
         * Whether a property is read-only.
         * @var bool|null
         */
        protected ?bool $readOnly = null;

        /**
         * Whether a property is optional (i.e., can be absent without causing contract evaluation to fail).
         * @var bool
         */
        protected bool $optional = false;
        
        /**
         * Creates a new property with a name, an optional type, and an optional value.
         * @param string $name The name of the property.
         * @param string|null $type The type of the property, or `null` if not set.
         * @param mixed $value The value of the property, or `null` if not set.
         * @param mixed $defaultValue The default value of the property, or `null` if not set.
         * @param AccessModifier|string|null $accessModifier The access modifier of the property, or `null` if not set.
         * @param bool|null $static Whether the property is static, or `null` if not set.
         * @param bool|null $readOnly Whether the property is read-only, or `null` if not set.
         * @param bool $optional Whether the property is optional, or `false` if not set.
         */
        public function __construct (
            string $name,
            ?string $type = null,
            mixed $value = self::UNSET,
            mixed $defaultValue = self::UNSET,
            AccessModifier|string|null $accessModifier = null,
            ?bool $static = null,
            ?bool $readOnly = null,
            bool $optional = false
        ) {
            parent::__construct($name, $type);

            $this->value = $value;
            $this->defaultValue = $defaultValue;
            $this->accessModifier = $accessModifier !== null ? AccessModifier::resolve($accessModifier) : null;
            $this->static = $static;
            $this->readOnly = $readOnly;
            $this->optional = $optional;

            $term = new PropertyMatchesSignatureTerm();
            $term->setContext($this);
            $this->addTerm($term);

            $term = new PropertyValueTerm();
            $term->setContext($this);
            $this->addTerm($term);
        }

        /**
         * Determines if a property exists in a given class or object.
         * @param object|string $target The class name or object to check for the property.
         * @param string $propertyName The name of the property to check for.
         * @return bool Whether the property exists.
         */
        public static function exists (object|string $target, string $propertyName) : bool {
            return property_exists($target, $propertyName);
        }

        /**
         * Expects certain characteristics for a property, such as type, value, access modifier, static status, and read-only status.
         * @param string|null $type The type of the property, or `null` if not set.
         * @param mixed $value The value of the property, or `null` if not set.
         * @param mixed $defaultValue The default value of the property, or `null` if not set.
         * @param AccessModifier|string|null $accessModifier The access modifier of the property, or `null` if not set.
         * @param bool|null $static Whether the property is static, or `null` if not set.
         * @param bool|null $readOnly Whether the property is read-only, or `null` if not set.
         * @param bool $optional Whether the property is optional, or `false` if not set.
         * @return static The property.
         */
        public function expect (
            ?string $type = null,
            mixed $value = self::UNSET,
            mixed $defaultValue = self::UNSET,
            AccessModifier|string|null $accessModifier = null,
            ?bool $static = null,
            ?bool $readOnly = null,
            bool $optional = false
        ) : static {
            if ($type !== null) {
                $this->type = $type;
            }
            if ($value !== self::UNSET) {
                $this->value = $value;
            }
            if ($defaultValue !== self::UNSET) {
                $this->defaultValue = $defaultValue;
            }
            if ($accessModifier !== null) {
                $this->accessModifier = AccessModifier::resolve($accessModifier);
            }
            if ($static !== null) {
                $this->static = $static;
            }
            if ($readOnly !== null) {
                $this->readOnly = $readOnly;
            }
            $this->optional = $optional;
            return $this;
        }

        /**
         * Expects a specific access modifier for a property.
         * @param AccessModifier $accessModifier The expected access modifier of the property.
         * @return static The property.
         */
        public function expectAccessModifier (AccessModifier $accessModifier) : static {
            $this->accessModifier = $accessModifier;
            return $this;
        }

        /**
         * Expects a specific default value for a property.
         * @param mixed $defaultValue The expected default value of the property.
         * @return static The property.
         */
        public function expectDefaultValue (mixed $defaultValue) : static {
            $this->defaultValue = $defaultValue;
            return $this;
        }

        /**
         * Expects a property to be read-only or not.
         * @param bool $isReadOnly Whether the property is expected to be read-only (true) or not (false).
         * @return static The property.
         */
        public function expectReadOnly (bool $isReadOnly = true) : static {
            $this->readOnly = $isReadOnly;
            return $this;
        }

        /**
         * Expects a property to be static or non-static.
         * @param bool $isStatic Whether the property is expected to be static (true) or non-static (false).
         * @return static The property.
         */
        public function expectStatic (bool $isStatic = true) : static {
            $this->static = $isStatic;
            return $this;
        }

        /**
         * Expects a specific type for a property.
         * @param string $type The expected type of the property.
         * @return static The property.
         */
        public function expectType (string $type) : static {
            $this->type = $type;
            return $this;
        }

        /**
         * Expects a specific value for a property.
         * @param mixed $value The expected value of the property.
         * @return static The property.
         */
        public function expectValue (mixed $value) : static {
            $this->value = $value;
            return $this;
        }

        /**
         * Gets the access modifier of a property.
         * @return AccessModifier|null The access modifier of the property, or `null` if not set.
         */
        public function getAccessModifier () : ?AccessModifier {
            return $this->accessModifier;
        }

        /**
         * Gets the default value of a property.
         * @return mixed The default value of the property, or `null` if not set.
         */
        public function getDefaultValue () : mixed {
            return $this->defaultValue;
        }

        /**
         * Gets the signature of a property as a string.
         * @return string The property signature.
         */
        public function getSignature () : string {
            $parts = [];
            
            $parts[] = $this->accessModifier ? $this->accessModifier->value : "public";
            
            if ($this->static === true) $parts[] = "static";
            if ($this->readOnly === true) $parts[] = "readonly";
            
            if ($this->type !== null) {
                $parts[] = $this->type;
            }
            
            $signature = implode(" ", $parts) . " \${$this->name}";

            if ($this->defaultValue !== self::UNSET) {
                $signature .= " = " . var_export($this->defaultValue, true);
            }
            
            return $signature . ";";
        }

        /**
         * Gets the type of a property.
         * @return string|null The type of the property, or `null` if not set.
         */
        public function getType () : ?string {
            return $this->type;
        }

        /**
         * Gets the value of a property.
         * @param object|string $target The class name or object to get the property value from.
         * @param string $propertyName The name of the property to get the value of.
         * @return mixed The value of the property, or `null` if it does not exist.
         */
        public function getValue () : mixed {
            return $this->value;
        }

        /**
         * Checks whether a property has a default value set.
         * @return bool True if the property has a default value, false otherwise.
         */
        public function hasDefaultValue () : bool {
            return $this->defaultValue !== self::UNSET;
        }

        /**
         * Checks whether a property has a specific value set.
         * @return bool True if the property has a value set, false otherwise.
         */
        public function hasValue () : bool {
            return $this->value !== self::UNSET;
        }

        /**
         * Marks a property as required or optional for contract evaluation.
         * @param bool $required Whether the property is required (true) or optional (false).
         * @return static The property.
         */
        public function require (bool $required = true) : static {
            $this->optional = !$required;
            return $this;
        }

        /**
         * Checks whether a property is optional for contract evaluation.
         * @return bool True if the property is optional, false otherwise.
         */
        public function isOptional () : bool {
            return $this->optional;
        }

        /**
         * Checks whether a property is read-only.
         * @return bool|null True if the property is read-only, false if it is not read-only, or `null` if not set.
         */
        public function isReadOnly () : ?bool {
            return $this->readOnly;
        }

        /**
         * Checks whether a property is static.
         * @return bool|null True if the property is static, false if it is not static, or `null` if not set.
         */
        public function isStatic () : ?bool {
            return $this->static;
        }

        /**
         * Waives specific properties of a property during contract evaluation.
         * @param string ...$propertyNames The names of the properties to waive.
         * @return static The property.
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