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
        $code = $this->generator->generate('<root><element><child>value</child><child>0</child></element></root>');

        $this->assertEquals('$root = new SimpleXMLElement("<root />");
$element = $root->addChild("element");
$child = $element->addChild("child", "value");
$child1 = $element->addChild("child", "0");', $code);
    }

    public function testShouldGenerateAttributes()
    {
        $code = $this->generator->generate('<root attribute="value"><element att="val" /></root>');

        $this->assertEquals('$root = new SimpleXMLElement("<root />");
$root["attribute"] = "value";
$element = $root->addChild("element");
$element["att"] = "val";', $code);
    }

    public function testShouldGenerateUniqueNames()
    {
        $code = $this->generator->generate('<root><element/><element><root/></element><root/></root>');

        $this->assertEquals('$root = new SimpleXMLElement("<root />");
$element = $root->addChild("element");
$element1 = $root->addChild("element");
$root1 = $element1->addChild("root");
$root2 = $root->addChild("root");', $code);
    }

    public function testShouldGenerateCamelCaseVariables()
    {
        $code = $this->generator->generate('<Root><SomeElement/><some_element/></Root>');

        $this->assertEquals('$root = new SimpleXMLElement("<Root />");
$someElement = $root->addChild("SomeElement");
$someElement1 = $root->addChild("some_element");', $code);
    }

    public function testShouldGenerateDocType()
    {
        $docType = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE DeliveryHeader SYSTEM "DeliveryHeader_MS_V02_00.dtd">';
        $this->generator->setDocType($docType);
        $code = $this->generator->generate('<Root />');

        $this->assertEquals('$root = new SimpleXMLElement("' . addcslashes($docType, "\"") . '<Root />");', $code);
    }

    public function testShouldGenerateNamespaces()
    {
        $code = $this->generator->generate('<ns:root xmlns:ns="http://some.ns/">
        <other:element xmlns:other="http://other.ns/"><other:root ns:attribute="value"/></other:element></ns:root>');

        $this->assertEquals('$root = new SimpleXMLElement("<ns:root xmlns:ns=\"http://some.ns/\"/>");
$element = $root->addChild("other:element", null, "http://other.ns/");
$root1 = $element->addChild("other:root");
$root1["ns:attribute"] = "value";', $code);
    }

}
