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
        $this->addCode('$%s = new SimpleXMLElement("%s");', $rootName, $rootName);

        $this->generateAttributes($xml, $rootName);
        $this->generateChildCode($xml, $rootName);

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
            $varName = $this->getName($childName);

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

    protected function reset()
    {
        $this->code = [];
    }

    public function addCode($line, $params = null)
    {
        $this->code[] = call_user_func_array("sprintf", func_get_args());
    }

    protected function getName($name)
    {
        if (!isset($this->names[$name])) {
            $this->names[$name] = 1;

            return $name;
        }

        return sprintf("%s%d", $name, $this->names++);
    }
}