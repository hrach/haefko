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
 * @subpackage  Forms
 */


class FormFileControl extends FormInputControl
{


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('file')->class('file');
	}


}


class FormUploadedFile
{

	/** @var string */
	public static $uploads;

	/** @var int */
	public $state;

	/** @var string */
	public $name;

	/** @var string */
	public $temp;

	/** @var string */
	public $type;

	/** @var string */
	public $size;


	/**
	 * Contructor
	 * @param FormControl $control
	 * @param array $data sent post data
	 * @return FormUploadedFile
	 */
	public function __construct(FormControl $control, $data)
	{
		$this->state = $data['error'];
		$this->name = $data['name'];
		$this->temp = $data['tmp_name'];
		$this->type = $data['type'];
		$this->size = $data['size'];
	}


	/**
	 * Retursn true if is the file successfully uploaded
	 * @return bool
	 */
	public function ok()
	{
		return $this->state == UPLOAD_ERR_OK;
	}


	/**
	 * Moves uploaded file
	 * @param string $to path for move
	 * @param bool $absolute is path absolute?
	 * @return bool
	 */
	public function move($to = null, $absolute = false)
	{
		if (!$this->ok()) return false;
		if (empty($to)) $to = $this->name;
		if ($absolute)
			$to = Tools::rTrim(self::$uploads, '/') . '/' . $to;

		$moved = move_uploaded_file($this->temp, $to);
		if (!$moved)
			return false;

		$this->name = $to;
		return true;
	}


}