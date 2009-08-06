<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Database
 */


interface IDbDriver
{


	/**
	 * Connects to database
	 * @param array $config configuration
	 * @throws Exception
	 */
	public function connect($config);


	/**
	 * Runs native sql query
	 * @param string $sql sql query
	 * @throws Exception
	 * @return DbDriver
	 */
	public function query($sql);


	/**
	 * Fetchs one result's row
	 * @param bool $assoc true = associative array | false = array
	 * @return array
	 */
	public function fetch($assoc);


	/**
	 * Escapes $value as a $type
	 * @param string $type
	 * @param string $value
	 * @return string
	 */
	public function escape($type, $value);


	/**
	 * Returns number of affected rows
	 * @return int
	 */
	public function affectedRows();


	/**
	 * Counts rows in result
	 * @return int
	 */
	public function rowCount();


	/**
	 * Returns last inserted id
	 * @return int
	 */
	public function insertedId();


	/**
	 * Returns list of tables
	 * @return array
	 */
	public function getTables();


	/**
	 * Returns description of table columns
	 * @param string $table table name
	 * @return array
	 */
	public function getTableColumnsDescription($table);


	/**
	 * Returns result columns
	 * @return array
	 */
	public function getResultColomns();


}