<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 * @subpackage  View
 */


class HtmlHelper extends Object
{


	/**
	 * Returns HTML link
	 * If text is null, then as the title is used link url
	 * @param   string    url
	 * @param   string    link text
	 * @param   array     html attributes
	 * @param   bool      escape link content
	 * @return  string
	 */
	public function link($url, $text = null, $attrs = array(), $escape = true)
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('a')->setAttrs($attrs)
		                   ->href($url);

		if ($escape == true)
			$el->setText($text === null ? $url : $text);
		else
			$el->setHtml($text === null ? $url : $text);

		return $el->render();
	}


	/**
	 * Returns HTML button
	 * If text is null, then as the title is used link url
	 * @param   string    url
	 * @param   string    hidden value for post
	 * @param   string    link text
	 * @param   string    javascript confirm question
	 * @param   array     html attributes
	 * @param   bool      escape link content
	 * @return  string
	 */
	public function button($url, $value, $text, $confirm = false, $attrs = array(), $escape = false)
	{
		$form = Html::el('form', null, array(
			'action' => $this->factoryUrl($url),
			'method' => 'post',
			'class' => 'button'
		));

		$form->setAttrs($attrs)
		     ->addHtml('<input type="hidden" name="entry" value="' . $value . '" />');


		if (!empty($confirm))
			$form->onclick("if (confirm('$confirm')) { return true; } else { return false; }");

		$button = Html::el('button');
		$button->type('submit');
		if ($escape == true)
			$button->addText($text);
		else
			$button->addHtml($text);

		$form->addHtml($button);
		return $form->render(false);
	}


	/**
	 * Returns HTML image
	 * @param   string    url
	 * @param   array     html attributes
	 * @return  string
	 */
	public function img($url, $attrs = array())
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('img')->setAttrs($attrs)
		                     ->src($url);

		return $el->render();
	}


	/**
	 * Returns HTML css-external tag
	 * @param   string    url
	 * @param   string    media type
	 * @param   bool      append timestamp?
	 * @return  string
	 */
	public function css($file, $media = 'screen', $timestamp = true)
	{
		$url = $this->factoryUrl($file);
		$el = Html::el('link')->rel('stylesheet')
		                      ->type('text/css')
		                      ->media($media);

		if ($timestamp) {
			$file = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
			$time = filemtime($file);
			if ($time !== false)
				$url .= '?' . $time;
		}

		return $el->href($url)->render(0);
	}


	/**
	 * Returns HTML js-external tag
	 * @param   string    url
	 * @param   bool      append timestamp?
	 * @return  string
	 */
	public function js($file, $timestamp = true)
	{
		$url = $this->factoryUrl($file);
		$el = Html::el('script')->type('text/javascript');

		if ($timestamp) {
			$file = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
			$time = filemtime($file);
			if ($time !== false)
				$url .= '?' . $time;
		}

		return $el->src($url)->render(0);
	}


	/**
	 * Returns HTML rss link tag
	 * @param   string    url
	 * @param   string    rss title
	 * @return  string
	 */
	public function rss($url, $title = 'RSS')
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('link')->rel('alternate')
		                      ->type('application/rss+xml')
		                      ->href($url)
		                      ->title($title);

		return $el->render(0);
	}


	/**
	 * Returns HTML favicon tag
	 * @param   string    url
	 * @return  string
	 */
	public function icon($url)
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('link')->rel('shortcut icon')
		                      ->href($url);

		return $el->render(0);
	}


	/**
	 * Returns HTML encoding-header tag
	 * @param   string    charset
	 * @return  string
	 */
	public function encoding($charset = 'UTF-8')
	{
		$el = Html::el('meta')->{'http-equiv'}('Content-type')
		                      ->content("text/html; charset=$charset");

		return $el->render(0);
	}


	/**
	 * Returns HTML title tag
	 * @param   string    title 
	 * @return  string
	 */
	public function title($title = null)
	{
		$el = Html::el('title');
		$el->setText(empty($title) ? Controller::get()->view->title : $title);
		return $el->render(0);
	}


	/**
	 * Returns tracking code for Google Analytics
	 * @param   string     id
	 * @return  string
	 */
	public function analytics($id)
	{
		return "<script type=\"text/javascript\">\n"
		     . "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n"
		     . "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n"
		     . "</script>\n<script type=\"text/javascript\">\n"
		     . "try {\nvar pageTracker = _gat._getTracker(\"$id\");\n"
		     . "pageTracker._trackPageview();\n} catch(err) {}</script>\n";
	}


	/**
	 * Returns parsed and sanitized URL
	 * @param   string  url
	 * @return  string
	 */
	protected function factoryUrl($url)
	{
		if (substr($url, 0, 4) == 'www.')
			$url = "http://$url";
		if (strpos($url, '://') === false)
			$url = call_user_func(array(Controller::get(), 'url'), $url);

		return $url;
	}


}