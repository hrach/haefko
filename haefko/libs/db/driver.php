<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Database
 */


require_once dirname(__FILE__) . '/../object.php';


abstract class DbDriver extends Object
{


	/** @var mixed */
	protected $resource;


	/** @var mixed */
	protected $result;


	/**
	 * Connects to database
	 * @param   array     configuration
	 * @throws  Exception
	 * @return  void
	 */
	abstract public function connect($config);


	/**
	 * Runs native sql query
	 * @param   string    sql query
	 * @throws  Exception
	 * @return  DbDriver  clone $this
	 */
	abstract public function query($sql);


	/**
	 * Fetchs one result's row
	 * @param   bool      true = associative array | false = array
	 * @return  array
	 */
	abstract public function fetch($assoc);


	/**
	 * Escapes $value as a $type
	 * @param   strign    column|text
	 * @param   strign    value
	 * @return  string
	 */
	abstract public function escape($type, $value);


	/**
	 * Returns number of affected rows
	 * @return  int
	 */
	abstract public function affectedRows();


	/**
	 * Returns array of information about columns
	 * @return  array
	 */
	abstract public function columnsMeta();


	/**
	 * Counts rows in result
	 * @return  int
	 */
	abstract public function rowCount();


	/**
	 * Returns last inserted id
	 * @param   string    sequence name
	 * @return  int
	 */
	abstract public function insertedId($sequence);


}