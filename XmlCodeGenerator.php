<?php


class XmlCodeGenerator
{

    protected $code;

    protected $names = [];

    protected $docType;

    protected $namespaces = [];
    protected $namespaceKeys = [];

    public function __construct($docType = null)
    {
        $this->setDocType($docType);
    }

    /**
     * @param string|SimpleXMLElement $xml
     * @return string
     */
    public function generate($xml)
    {
        $this->reset();

        if (is_string($xml)) {
            $xml = new SimpleXMLElement($xml);
        }

        $this->namespaces = $xml->getNamespaces(true);
        $this->namespaceKeys = array_flip($this->namespaces);

        $rootName = $xml->getName();
        $varName = $this->dashesToCamelCase($rootName);
        $varName = $this->getUniqueVariableName($varName);

        $namespaceDefinition = '';

        $namespaces = $xml->getNamespaces();
        if (count($namespaces) > 0) {
            $rootNamespace = reset($namespaces);
            $namespaceKey = $this->namespaceKeys[$rootNamespace];
            $rootName = sprintf("%s:%s", $namespaceKey, $rootName);

            $namespaceDefinition = sprintf('xmlns:%s=\"%s\"', $namespaceKey, $rootNamespace);
        }

        $this->addCode('$%s = new SimpleXMLElement("' . $this->docType . '<%s %s/>");', $varName, $rootName, $namespaceDefinition);

        $this->generateAttributes($xml, $varName);
        $this->generateChildCode($xml, $varName);

        return implode("\n", $this->code);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $previousName
     * @param string[] $definedNamespaces Namespaces defined in parent nodes to avoid namespace redeclaration
     */
    public function generateChildCode($xml, $previousName, $definedNamespaces = [])
    {
        /** @var SimpleXMLElement $child */
        foreach ($xml->children() as $child) {
            $childName = $child->getName();
            $varName = $this->dashesToCamelCase($childName);
            $varName = $this->getUniqueVariableName($varName);

            $value = trim((string)$child);

            if (empty($value)) {
                $this->addCode('$%s = $%s->addChild("%s");', $varName, $previousName, $childName);
            } else {
                $this->addCode('$%s = $%s->addChild("%s", "%s");', $varName, $previousName, $childName, $value);
            }

            $this->generateAttributes($child, $varName);
            $this->generateChildCode($child, $varName);
        }

        foreach ($this->namespaces as $namespace) {
            /** @var SimpleXMLElement $child */
            foreach ($xml->children($namespace) as $child) {
                $childName = $child->getName();
                $varName = $this->dashesToCamelCase($childName);
                $varName = $this->getUniqueVariableName($varName);

                $value = trim((string)$child);

                if (isset($definedNamespaces[$namespace])) {
                    if (empty($value)) {
                        $this->addCode('$%s = $%s->addChild("%s:%s");', $varName, $previousName, $this->namespaceKeys[$namespace], $childName);
                    } else {
                        $this->addCode('$%s = $%s->addChild("%s:%s", "%s");', $varName, $previousName, $this->namespaceKeys[$namespace], $childName, $value);
                    }

                } else {

                    if (empty($value)) {
                        $this->addCode('$%s = $%s->addChild("%s:%s", null, "%s");', $varName, $previousName, $this->namespaceKeys[$namespace], $childName, $namespace);
                    } else {
                        $this->addCode('$%s = $%s->addChild("%s:%s", "%s", "%s");', $varName, $previousName, $this->namespaceKeys[$namespace], $childName, $value, $namespace);
                    }
                }

                $definedNamespaces[$namespace] = true;

                $this->generateAttributes($child, $varName);
                $this->generateChildCode($child, $varName, $definedNamespaces);
            }
        }
    }


    /**
     * @param SimpleXMLElement $xml
     * @param string $elementName
     * @return string[]
     */
    public function generateAttributes(SimpleXMLElement $xml, $elementName)
    {
        $code = [];

        foreach ($xml->attributes() as $key => $value) {
            $this->addCode('$%s["%s"] = "%s";', $elementName, $key, $value);
        }

        foreach ($this->namespaces as $namespaceKey => $namespace) {
            foreach ($xml->attributes($namespace) as $key => $value) {
                $this->addCode('$%s["%s:%s"] = "%s";', $elementName, $namespaceKey, $key, $value);
            }
        }

        return $code;
    }

    /**
     * Reset the state of generator
     */
    protected function reset()
    {
        $this->code = [];
        $this->names = [];
    }

    /**
     * @param string $line sprintf line template
     * @param string[] $params list of parameters to replace $line placeholders
     */
    public function addCode($line, $params = null)
    {
        $this->code[] = call_user_func_array("sprintf", func_get_args());
    }

    /**
     * Generates unique variable name
     * @param string $name
     * @return string
     */
    protected function getUniqueVariableName($name)
    {
        if (!isset($this->names[$name])) {
            $this->names[$name] = 1;

            return $name;
        }

        return sprintf("%s%d", $name, $this->names[$name]++);
    }

    /**
     * @param string $string String to camel case
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    protected function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('_', '', ucwords($string, '-_ '));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    public function setDocType($docType)
    {
        $this->docType = $docType;
    }

}