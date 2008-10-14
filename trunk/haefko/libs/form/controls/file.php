<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


class FormFileControl extends FormInputContaineredControl
{

	protected $htmlType = 'file';
	protected $htmlTypeClass = 'file';

}

class FormUploadedFile
{

	/** @var string */
	public static $uploads;

	/** @var int */
	protected static $i = 0;

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
	 * @param   FormControl
	 * @param   array
	 * @return  void
	 */
	public function __construct(FormControl $control, $data)
	{
		$this->state = $data['error'][$control->name][self::$i];
		$this->name = $data['name'][$control->name][self::$i];
		$this->temp = $data['tmp_name'][$control->name][self::$i];
		$this->type = $data['type'][$control->name][self::$i];
		$this->size = $data['size'][$control->name][self::$i];
		self::$i++;
	}


	public function ok()
	{
		return $this->state == UPLOAD_ERR_OK;
	}


	public function move($to = null, $absolute = false)
	{
		if (!$this->ok())
			return false;

		if (empty($to))
			$to = $this->name;

		if ($absolute)
			$to = Tools::rTrim(self::$uploads, '/') . '/' . $to;

		$moved = move_uploaded_file($this->temp, $to);

		if (!$moved)
			return false;

		$this->name = $to;
		return true;
	}


	public function getSize()
	{
		static $s = array('B', 'Kb', 'MB', 'GB', 'TB', 'PB');

		$e = floor(log($this->size) / log(1024));
		return sprintf('%.2f ' . $s[$e], ($this->size / pow(1024, floor($e))));
	}


}