<?php


class XmlCodeGenerator
{

    protected $code;

    protected $names = [];

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

        $rootName = $xml->getName();
        $varName = $this->dashesToCamelCase($rootName);
        $varName = $this->getUniqueVariableName($varName);

        $this->addCode('$%s = new SimpleXMLElement("%s");', $varName, $rootName);

        $this->generateAttributes($xml, $varName);
        $this->generateChildCode($xml, $varName);

        return implode("\n", $this->code);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $previousName
     * @return string[]
     */
    public function generateChildCode($xml, $previousName)
    {
        $code = [];

        /** @var SimpleXMLElement $child */
        foreach ($xml as $child) {
            $childName = $child->getName();
            $varName = $this->dashesToCamelCase($childName);
            $varName = $this->getUniqueVariableName($varName);

            $value = (string)$child;

            if (empty($value)) {
                $this->addCode('$%s = $%s->addChild("%s");', $varName, $previousName, $childName);
            } else {
                $this->addCode('$%s = $%s->addChild("%s", "%s");', $varName, $previousName, $childName, $value);
            }

            $this->generateAttributes($child, $varName);
            $this->generateChildCode($child, $varName);
        }

        return $code;
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

}