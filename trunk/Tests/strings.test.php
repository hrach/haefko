<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('Haefko/functions.php');


class StringsTest extends UnitTestCase
{

	function __construct() {
		$this->UnitTestCase('Strings Test');
	}


	function testCamelize()
	{
		$name = strCamelize('test');
		$this->assertEqual($name, 'Test');

		$name = strCamelize('test-second');
		$this->assertEqual($name, 'TestSecond');

		$name = strCamelize('-test');
		$this->assertEqual($name, 'Test');

		$name = strCamelize('test-');
		$this->assertEqual($name, 'Test');

		$name = strCamelize('next-test-third');
		$this->assertEqual($name, 'NextTestThird');

		$name = strCamelize('CamelCase');
		$this->assertEqual($name, 'CamelCase');
	}

	function testDash()
	{
		$name = strDash('Test');
		$this->assertEqual($name, 'test');

		$name = strDash('TestSecond');
		$this->assertEqual($name, 'test-second');

		$name = strDash('test');
		$this->assertEqual($name, 'test');

		$name = strDash('test-second');
		$this->assertEqual($name, 'test-second');

		$name = strDash('NextTestThird');
		$this->assertEqual($name, 'next-test-third');
	}

	function testToAscii()
	{
		$name = strToAscii('První test dlouhými písmeny.');
		$this->assertEqual($name, 'Prvni test dlouhymi pismeny.');

		$name = strToAscii('ěščřžýáíéúů');
		$this->assertEqual($name, 'escrzyaieuu');

		$name = strToAscii('ĚŠČŘŽÝÁÍÉÚŮ');
		$this->assertEqual($name, 'ESCRZYAIEUU');
	}

	function testCoolUrl()
	{
		$name = strToCoolUrl('První test dlouhými písmeny.');
		$this->assertEqual($name, 'prvni-test-dlouhymi-pismeny');

		$name = strToCoolUrl('Problémy: dvojtečky apod...');
		$this->assertEqual($name, 'problemy-dvojtecky-apod');

		$name = strToCoolUrl('Vykřičníkem se nedá zaskočit!!! Nebo ano? :) Ne!');
		$this->assertEqual($name, 'vykricnikem-se-neda-zaskocit-nebo-ano-ne');
	}

	function testSanitizeUrl()
	{
		$name = strSanitizeurl('/first/second/third');
		$this->assertEqual($name, 'first/second/third');

		$name = strSanitizeurl('/first/second/third/');
		$this->assertEqual($name, 'first/second/third');

		$name = strSanitizeurl('///first/second/third////');
		$this->assertEqual($name, 'first/second/third');

		$name = strSanitizeurl('first///second/third/');
		$this->assertEqual($name, 'first/second/third');
	}

	function testTrim()
	{
		$name = strLeftTrim('MyTest', 'My');
		$this->assertEqual($name, 'Test');

		$name = strLeftTrim('MyBigTest', 'MyB');
		$this->assertEqual($name, 'igTest');

		$name = strLeftTrim('Test', 'A');
		$this->assertEqual($name, 'Test');

		$name = strRightTrim('MyTest', 'Test');
		$this->assertEqual($name, 'My');

		$name = strRightTrim('MyBigTest', 'gTest');
		$this->assertEqual($name, 'MyBi');

		$name = strRightTrim('Test', 'A');
		$this->assertEqual($name, 'Test');
	}

	function testLcfirst()
	{
		$name = lcfirst('Test');
		$this->assertEqual($name, 'test');

		$name = lcfirst('TEST');
		$this->assertEqual($name, 'tEST');

		$name = lcfirst('test');
		$this->assertEqual($name, 'test');
	}

}

$test = new StringsTest();
$test->run(new HtmlReporter());