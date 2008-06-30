<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('Haefko/Strings.php');


class StringsTest extends UnitTestCase
{

	function __construct() {
		$this->UnitTestCase('Strings Test');
	}


	function testCamelize()
	{
		$name = Strings::camelize('test');
		$this->assertEqual($name, 'Test');

		$name = Strings::camelize('test-second');
		$this->assertEqual($name, 'TestSecond');

		$name = Strings::camelize('-test');
		$this->assertEqual($name, 'Test');

		$name = Strings::camelize('test-');
		$this->assertEqual($name, 'Test');

		$name = Strings::camelize('next-test-third');
		$this->assertEqual($name, 'NextTestThird');

		$name = Strings::camelize('CamelCase');
		$this->assertEqual($name, 'CamelCase');
	}

	function testDash()
	{
		$name = Strings::dash('Test');
		$this->assertEqual($name, 'test');

		$name = Strings::dash('TestSecond');
		$this->assertEqual($name, 'test-second');

		$name = Strings::dash('test');
		$this->assertEqual($name, 'test');

		$name = Strings::dash('test-second');
		$this->assertEqual($name, 'test-second');

		$name = Strings::dash('NextTestThird');
		$this->assertEqual($name, 'next-test-third');
	}

	function testToAscii()
	{
		$name = Strings::toAscii('První test dlouhými písmeny.');
		$this->assertEqual($name, 'Prvni test dlouhymi pismeny.');

		$name = Strings::toAscii('ěščřžýáíéúů');
		$this->assertEqual($name, 'escrzyaieuu');

		$name = Strings::toAscii('ĚŠČŘŽÝÁÍÉÚŮ');
		$this->assertEqual($name, 'ESCRZYAIEUU');
	}

	function testCoolUrl()
	{
		$name = Strings::coolUrl('První test dlouhými písmeny.');
		$this->assertEqual($name, 'prvni-test-dlouhymi-pismeny');

		$name = Strings::coolUrl('Problémy: dvojtečky apod...');
		$this->assertEqual($name, 'problemy-dvojtecky-apod');

		$name = Strings::coolUrl('Vykřičníkem se nedá zaskočit!!! Nebo ano? :) Ne!');
		$this->assertEqual($name, 'vykricnikem-se-neda-zaskocit-nebo-ano-ne');
	}

	function testSanitizeUrl()
	{
		$name = Strings::sanitizeurl('/first/second/third');
		$this->assertEqual($name, 'first/second/third');

		$name = Strings::sanitizeurl('/first/second/third/');
		$this->assertEqual($name, 'first/second/third');

		$name = Strings::sanitizeurl('///first/second/third////');
		$this->assertEqual($name, 'first/second/third');

		$name = Strings::sanitizeurl('first///second/third/');
		$this->assertEqual($name, 'first/second/third');
	}

	function testTrim()
	{
		$name = Strings::ltrim('MyTest', 'My');
		$this->assertEqual($name, 'Test');

		$name = Strings::ltrim('MyBigTest', 'MyB');
		$this->assertEqual($name, 'igTest');

		$name = Strings::ltrim('Test', 'A');
		$this->assertEqual($name, 'Test');

		$name = Strings::rtrim('MyTest', 'Test');
		$this->assertEqual($name, 'My');

		$name = Strings::rtrim('MyBigTest', 'gTest');
		$this->assertEqual($name, 'MyBi');

		$name = Strings::rtrim('Test', 'A');
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