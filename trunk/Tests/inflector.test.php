<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('Haefko/Application/Inflector.php');


class InflectorTest extends UnitTestCase
{

	function __construct() {
		$this->UnitTestCase('Inflector Test');
	}


	function testControllerClass()
	{
		$name = Inflector::controllerClass('');
		$this->assertEqual($name, 'Controller');

		$name = Inflector::controllerClass('posts');
		$this->assertEqual($name, 'PostsController');

		$name = Inflector::controllerClass('super-posts');
		$this->assertEqual($name, 'SuperPostsController');

		$name = Inflector::controllerClass('posts', 'admin');
		$this->assertEqual($name, 'AdminPostsController');

		$name = Inflector::controllerClass('posts', 'my-admin');
		$this->assertEqual($name, 'MyAdminPostsController');
	}


	function testControllerFile()
	{
		$name = Inflector::controllerFile('posts');
		$this->assertEqual($name, 'controllers/posts.php');

		$name = Inflector::controllerFile('super-posts');
		$this->assertEqual($name, 'controllers/super-posts.php');

		$name = Inflector::controllerFile('posts', 'admin');
		$this->assertEqual($name, 'controllers/admin/posts.php');

		$name = Inflector::controllerFile('posts', 'my-admin');
		$this->assertEqual($name, 'controllers/my-admin/posts.php');
	}


	function testModelClass()
	{
		$name = Inflector::modelClass('posts');
		$this->assertEqual($name, 'PostsModel');

		$name = Inflector::modelClass('super-posts');
		$this->assertEqual($name, 'SuperPostsModel');

		$name = Inflector::modelClass('posts', 'admin');
		$this->assertEqual($name, 'AdminPostsModel');

		$name = Inflector::modelClass('posts', 'my-admin');
		$this->assertEqual($name, 'MyAdminPostsModel');
	}


	function testModelFile()
	{
		$name = Inflector::modelFile('posts');
		$this->assertEqual($name, 'models/posts.php');

		$name = Inflector::modelFile('super-posts');
		$this->assertEqual($name, 'models/super-posts.php');

		$name = Inflector::modelFile('posts', 'admin');
		$this->assertEqual($name, 'models/admin/posts.php');

		$name = Inflector::modelFile('posts', 'my-admin');
		$this->assertEqual($name, 'models/my-admin/posts.php');
	}

	function testHelperClass()
	{
		$name = Inflector::helperClass('html');
		$this->assertEqual($name, 'HtmlHelper');

		$name = Inflector::helperClass('RSSrender');
		$this->assertEqual($name, 'RssrenderHelper');
	}

	function testHelperFile()
	{
		$name = Inflector::helperFile('html');
		$this->assertEqual($name, 'helpers/html.php');

		$name = Inflector::helperFile('RSSrender');
		$this->assertEqual($name, 'helpers/rssrender.php');
	}

	function testActionName()
	{
		$name = Inflector::actionName('show');
		$this->assertEqual($name, 'ShowAction');

		$name = Inflector::actionName('get-rating');
		$this->assertEqual($name, 'GetRatingAction');
	}

	function testViewFile()
	{
		$name = Inflector::viewFile('phtml', 'show', '', '', 'posts', '');
		$this->assertEqual($name, 'views/posts/show.phtml');

		$name = Inflector::viewFile('ztp', 'show', '', '', 'posts', '');
		$this->assertEqual($name, 'views/posts/show.ztp');

		$name = Inflector::viewFile('phtml', 'show', 'admin', '', 'posts', '');
		$this->assertEqual($name, 'views/admin-posts/show.phtml');

		$name = Inflector::viewFile('phtml', 'show', 'my-admin', '', 'peter-posts', '');
		$this->assertEqual($name, 'views/my-admin-peter-posts/show.phtml');

		$name = Inflector::viewFile('phtml', 'show', '', 'green', 'posts', '');
		$this->assertEqual($name, 'views/green/posts/show.phtml');

		$name = Inflector::viewFile('phtml', 'show', 'admin', 'green', 'posts', '');
		$this->assertEqual($name, 'views/green/admin-posts/show.phtml');

		$name = Inflector::viewFile('rss.php', 'show', '', '', 'posts', true);
		$this->assertEqual($name, 'views/posts/service/show.rss.php');

		$name = Inflector::viewFile('rss.php', 'show', '', 'blue', 'posts', true);
		$this->assertEqual($name, 'views/blue/posts/service/show.rss.php');

		$name = Inflector::viewFile('xml.php', 'myShow', 'my-admin', 'blue', 'posts', true);
		$this->assertEqual($name, 'views/blue/my-admin-posts/service/my-show.xml.php');
	}

	function testLayoutFile()
	{
		$name = Inflector::layoutFile('phtml', 'layout', '', '');
		$this->assertEqual($name, 'views/layout.phtml');

		$name = Inflector::layoutFile('ztp', 'layout', 'admin', '');
		$this->assertEqual($name, 'views/admin-layout.ztp');

		$name = Inflector::layoutFile('phtml', 'myLayout', 'my-admin', 'green');
		$this->assertEqual($name, 'views/green/my-admin-my-layout.phtml');

		$name = Inflector::layoutFile('phtml', 'layout', '', 'red');
		$this->assertEqual($name, 'views/red/layout.phtml');
	}

	function testErrorViewFile()
	{
		$name = Inflector::errorViewFile('phtml', 'e404', '');
		$this->assertEqual($name, 'views/errors/e404.phtml');

		$name = Inflector::errorViewFile('phtml', 'e404', 'green');
		$this->assertEqual($name, 'views/green/errors/e404.phtml');
	}

	function testElementFile()
	{
		$name = Inflector::elementFile('phtml', 'rating');
		$this->assertEqual($name, 'views/rating.phtml');

		$name = Inflector::elementFile('phtml', '/_elements//rating');
		$this->assertEqual($name, 'views/_elements/rating.phtml');
	}

}

$test = new InflectorTest();
$test->run(new HtmlReporter());