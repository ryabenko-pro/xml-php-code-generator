<?php


class XmlCodeGenerator
{

    /**
     * @param string|SimpleXMLElement $xml
     * @return string
     */
    public function generate($xml)
    {
        if (is_string($xml)) {
            $xml = new SimpleXMLElement($xml);
        }

        $rootName = $xml->getName();
        $code[] = sprintf('$%s = new SimpleXMLElement("%s");', $rootName, $rootName);

        $code = array_merge($code, $this->generateAttributes($xml, $rootName));

        $code = array_merge($code, $this->generateChildCode($xml, $rootName));

        return implode("\n", $code);
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
            $value = (string)$child;

            if (empty($value)) {
                $code[] = sprintf('$%s = $%s->addChild("%s");', $childName, $previousName, $childName);
            } else {
                $code[] = sprintf('$%s = $%s->addChild("%s", "%s");', $childName, $previousName, $childName, $value);
            }

            $code = array_merge($code, $this->generateAttributes($child, $childName));

            $code = array_merge($code, $this->generateChildCode($child, $childName));
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
            $code[] = sprintf('$%s["%s"] = "%s";', $elementName, $key, $value);
        }

        return $code;
    }
}