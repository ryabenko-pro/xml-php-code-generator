<?php

require 'XmlCodeGenerator.php';

use PHPUnit\Framework\TestCase;


class XmlCodeGeneratorTest extends TestCase
{

    /** @var XmlCodeGenerator */
    protected $generator;

    protected function setUp()
    {
        $this->generator = new XmlCodeGenerator();
    }


    public function testShouldGenerateNestingCode()
    {
        $code = $this->generator->generate('<root><element><child>value</child></element></root>');

        $this->assertEquals('$root = new SimpleXMLElement("root");
$element = $root->addChild("element");
$child = $element->addChild("child", "value");', $code);
    }

    public function testShouldGenerateAttributes()
    {
        $code = $this->generator->generate('<root attribute="value"><element att="val" /></root>');

        $this->assertEquals('$root = new SimpleXMLElement("root");
$root["attribute"] = "value";
$element = $root->addChild("element");
$element["att"] = "val";', $code);
    }


}
