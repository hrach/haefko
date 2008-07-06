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
 * Trida Autoload nacita vsechny potrebne tridy pro beh aplikace
 */
class Autoload
{



    public $skipDirs = array('.svn', '.cvs');
    public $skipFiles = array('\.phtml$', '\.html$');
    public $cacheFile = './autoload.cache.dat';

    protected $list = false;
    protected $scanDirs = array();

    private static $autoload = false;



    /**
     * Vrati instanci autoloadu
     * @return  Autoload
     */
    public static function getInstance()
    {
        if (self::$autoload === false) {
            self::$autoload = new Autoload();
        }

        return self::$autoload;
    }



    /**
     * Zaregistruje autoload
     * @return  void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadHandler'));
    }



    /**
     * Prida adresar frameworku pro zaindexovani
     * @return  Autoload
     */
    public function addFramework()
    {
        $this->scanDirs[] = dirname(__FILE__);
        return $this;
    }



    /**
     * Prida adresar aplikace pro zaindexovani
     * @return  Autoload
     */
    public function addApplication()
    {
        if (class_exists('Application', false)) {
            $this->addDir(Application::getInstance()->getPath());
        }

        return $this;
    }



    /**
     * Prida predany adresar pro zaindexovani
     * @param   string  cesta k adresari
     * @return  Autoload
     */
    public function addDir($dirPath)
    {
        if (file_exists($dirPath)) {
            $this->scanDirs[] = $dirPath;
        } else {
            throw new Exception('Autoload: adresar neexistuje: ' . $dirPath);
        }

        return  $this;
    }



    /**
     * Handler pro autoload
     * @param   string  jmeno tridy
     * @return  void
     */
    public function loadHandler($class)
    {
        if ($this->list == false) {
            $this->createClassList();
        }

        if (isset($this->list[$class]) && file_exists($this->list[$class])) {
            require_once $this->list[$class];
        }
    }




    /**
     * Vytvori seznam trid a jejich souboru
     * @return  void
     */
    private function createClassList()
    {
        if (class_exists('Config', false)) {
            $this->cacheFile = Config::read('Autoload.cache-file', Application::getInstance()->getPath(). 'temp/autoload.cache.dat');
        }

        if (file_exists($this->cacheFile)) {
            $this->list = unserialize(file_get_contents($this->cacheFile));
        } else {
            $this->findClasses();

            if (file_exists(dirname($this->cacheFile))) {
                file_put_contents($this->cacheFile, serialize($this->list));
            }
        }
    }




    /**
     * Najde vsechny tridy a vytvori jejich seznam
     * @retrun  void
     */
    private function findClasses()
    {
        $files  = array();

        foreach ($this->scanDirs as $scanDir) {
            $this->getFiles(new RecursiveDirectoryIterator($scanDir), $files);
        }

        foreach ($files as $file) {
            $this->getClasses($file);
        }
    }



    /**
     * Rekurzivni funkce pro najiti vsech souboru
     * @param   RecursiveDirectoryIterator  objekt adresare
     * @param   array                       seznam souboru
     * @return  void
     */
    private function getFiles(& $rdi, & $files)
    {
        if (!is_object($rdi)) {
            return;
        }

        for ($rdi->rewind(); $rdi->valid(); $rdi->next()) {
            if ($rdi->isDot()) {
                continue;
            }

            if ($rdi->isFile() && !in_array(substr(strrchr($rdi->getFilename(), '.'), 1), $this->skipFiles)) {
                $files[] = $rdi->getPathname();
            } elseif($rdi->isDir() && !in_array($rdi->getFilename(), $this->skipDirs) && $rdi->hasChildren()) {
                $this->getFiles($rdi->getChildren(), $files);
            }
        }
    }



    /**
     * Najde v souboru vsechny tridy a ulozi cesty k nim
     * @param   string  soubor
     * @return  void
     */
    private function getClasses($file)
    {
        $catch = false;

        foreach (token_get_all(file_get_contents($file)) as $token) {
            if (is_array($token)) {
                if ($token[0] == T_CLASS || $token[0] == T_INTERFACE) {
                    $catch = true;
                } elseif ($token[0] == T_STRING && $catch) {
                    $this->list[$token[1]] = $file;
                    $catch = false;
                }
            }
        }
    }



}