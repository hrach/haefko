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



/**
 * Vyjimka aplikace
 */
class ApplicationException extends Exception
{

    /** @var string */
    public $error;



    /**
     * Konstruktor
     * @param   string  typ chyby (routing|missing-controller|missing-view|missing-helper|missing-file)
     * @param   string  promenna pro view
     * @return  void
     */
    public function __construct($error, $message = null)
    {
        static $errors = array('routing', 'missing-controller', 'missing-view', 'missing-helper', 'missing-file');

        if (!in_array($error, $errors))
            die("Exception: nepodporovany typ vyjimky '$error'.");

        Application::getInstance()->error = true;

        $this->error = $error;
        parent::__construct($message);
    }



}