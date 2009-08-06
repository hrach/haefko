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
 */


class Control extends Object
{

	/** @var SessionNamespace */
	private $flashSession;


	/**
	 * Contructor
	 * @return Control
	 */
	public function __construct()
	{
		if (empty($this->flashSession))
			$this->flashSession = Session::getNamespace('flash-messages.' . $this->getClass());
	}


	/**
	 * Adds flash message and returns message class
	 * @param string $message
	 * @param string $type
	 * @return stdClass
	 */
	public function addFlash($message, $type = 'info')
	{
		$messages = $this->flashSession->messages;
		$messages[] = $flash = (object) array(
			'message' => $message,
			'type' => $type,
		);

		$this->flashSession->set('messages', $messages, time() + 3);
		return $flash;
	}


	/**
	 * Returns flash messages
	 * @return array
	 */
	public function getFlashes()
	{
		$messages = $this->flashSession->messages;
		if (empty($messages))
			return array();
		else
			return $messages;
	}


	/**
	 * Returns template instance
	 * @return ITemplate
	 */
	protected function getTemplateInstace()
	{
		$template = new Template();
		$template->flashes = $this->getFlashes();
		return $template;
	}


}