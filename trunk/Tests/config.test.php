<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('Haefko/Config.php');


class ConfigTest extends UnitTestCase
{

    function __construct() {
        $this->UnitTestCase('Config Test');
    }

    function testYaml() {
        $array = array(
            'Core.debug' => 1,
            'multi' => array(
                'localhost' => array(
                    'Core.debug' => 1,
                    'Db.connection' => array(
                        'driver' => 'mysqli',
                        'host' => 'localhost'
                    )
                )
            )
        );

        $data = Config::parseFile(dirname(__FILE__) . '/config.test.yml');
        $this->assertEqual($data, $array);
    }

}

$test = new ConfigTest();
$test->run(new HtmlReporter());