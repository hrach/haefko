<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('Haefko/Config.php');


class ConfigTest extends UnitTestCase
{

    function __construct()
    {
        $this->UnitTestCase('Config Test');
    }

    function testReadAndWrite()
    {
        Config::write('Core.debug', 'yes');
        $value = Config::read('Core.debug');
        $this->assertEqual($value, 'yes');

        Config::write('Core.next', 'yes');
        $value = Config::read('Core');
        $this->assertEqual($value, array('next' => 'yes', 'debug' => 'yes'));
    }

    function testYml()
    {
        $array = array(
            'Core.debug' => 1,
            'servers' => array(
                'localhost' => array(
                    'Core.debug' => 1,
                    'Db.connection' => array(
                        'driver' => 'mysqli',
                        'host' => 'localhost'
                    ),
                    'Array' => array('One', 'Two')
                )
            )
        );

        $data = Config::parseFile(dirname(__FILE__) . '/config.test.yml');
        $this->assertEqual($data, $array);
    }

}

$test = new ConfigTest();
$test->run(new HtmlReporter());