<?php

require 'XmlCodeGenerator.php';

use PHPUnit\Framework\TestCase;


class XmlCodeGeneratorTest extends TestCase
{


    public function testShouldGenerateNestingCode()
    {
        $generator = new XmlCodeGenerator();

        $code = $generator->generate("<root><element><child>value</child></element></root>");

        $this->assertEquals('$root = new SimpleXMLElement("root");
$element = $root->addChild("element");
$child = $element->addChild("child", "value");', $code);
    }
}
