<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek http://hrach.netuje.cz
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



/**
 * Trida Autoload nacita vsechny potrebne tridy pro beh aplikace
 */
final class Autoload
{

    /** @var array Pole s s povolenymi priponami */
    public $exts = array('php');

    /** @var string Jmeno souboru s cache */
    public $cache = 'autoload.dat';

    /** @var bool Automaticky provest rebuild pri chybejici tride? */
    public $autoRebuild = false;

    /** @var bool Znovu vytvorit cache trid? */
    public $rebuild = false;

    /** @var array Pole kde klic je jmeno tridy a hodnota je jmeno souboru */
    private $classes = array();

    /** @var array Pole se sboury k preparsovani */
    private $files = array();

    /** @var array Pole adresaru, ktere se projdou a preparsuji */
    private $dirs = array();



    /**
     * Kontruktor
     * Provede nastaveni podle konfigurace
     * Zaregistruje autoload
     * @return  void
     */
    final public function __construct()
    {
        if (class_exists('Config', false)) {
            $this->exts = Config::read('Autoload.exts', $this->exts);
            $this->cache = Config::read('Autoload.cache', Application::getInstance()->path . "temp/cache/{$this->cache}");
            $this->autoRebuild = Config::read('Core.debug') > 0;
        }

        spl_autoload_register(array($this, 'autoloadHandler'));
    }



    /**
     * Prida predany adresar pro zaindexovani
     * @param   string  cesta
     * @return  void
     */
    public function addDir($path)
    {
        if (is_dir($path))
            $this->dirs[] = $path;
        else
            throw new Exception("Autoload: adresar '$path' neexistuje.");
    }



    /**
     * Nacte soubor obsahujici $class
     * @param   string  jmeno tridy
     * @return  void
     */
    public function autoloadHandler($class)
    {
        if (isset($this->classes[$class]) && file_exists($this->classes[$class]))
            require_once $this->classes[$class];
        elseif (!$this->rebuild && $this->autoRebuild)
            $this->rebuild();
    }



    /**
     * Znovu vytvori cache seznamu trid a jejich souboru
     * @return  void
     */
    public function rebuild()
    {
        $this->findClasses();
        $this->rebuild = true;

        if (is_dir(dirname($this->cache)))
            file_put_contents($this->cache, serialize($this->classes));
        else
            throw new Exception("Autoload: adresar '" . dirname($this->cache) ."' neexistuje.");
    }



    /**
     * Nacte z cache seznam trid a jejich souboru
     * @return  void
     */
    public function load()
    {
        if (file_exists($this->cache)) {
            $this->classes = unserialize(file_get_contents($this->cache));
        } else {
            $this->rebuild();
        }
    }



    /**
     * Najde vsechny tridy a vytvori jejich seznam
     * @retrun  void
     */
    private function findClasses()
    {
        foreach ($this->dirs as $dir)
            $this->getFiles(new RecursiveDirectoryIterator($dir));

        foreach ($this->files as $file)
            $this->getClasses($file);
    }



    /**
     * Rekurzivni funkce pro najiti vsech souboru
     * @param   RecursiveDirectoryIterator  objekt adresare
     * @return  void
     */
    private function getFiles(& $rdi)
    {
        $exts = '#\.(' . implode('|', $this->exts) . ')$#i';

        for ($rdi->rewind(); $rdi->valid(); $rdi->next()) {
            if ($rdi->isDot())
                continue;

            if ($rdi->isFile() && preg_match($exts, $rdi->getFilename()))
                $this->files[] = $rdi->getPathname();
            elseif($rdi->isDir() && preg_match('#^\.(svn|cvs)$#i', $rdi->getFilename()) && $rdi->hasChildren())
                $this->getFiles($rdi->getChildren());
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
                    $this->classes[$token[1]] = $file;
                    $catch = false;
                }
            }
        }
    }



}