<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



class ApplicationException extends Exception
{



    public $error;



    public function __construct($error, $message = null)
    {
        static $errors = array('controller', 'method', 'routing', 'view');

        if (!in_array($error, $errors))
            throw new Exception("Haefko: nepodporovany typ vyjimky ApplicationException $error!");

        $this->error = $error;
        parent::__construct($message);
    }



}