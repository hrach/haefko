<?php

require 'haefko/loader.php';
debug::init(true);



class PostsResource extends Resource
{
	public $user_id;
	protected $name = 'posts';
}



$acl = new Permission;
$acl->addRole('author', 'guest');
$acl->addResource('posts');

$acl->allow('guest', 'posts');
$acl->deny('guest', 'posts', 'edit');
$acl->allow('author', 'posts', 'edit', new UserPostsAssertion);


$posts = new PostsResource;
$posts->user_id = 1234;


echo "<br>allowed: " . ($acl->isAllowed('guest', 'posts', 'view') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('author', $posts, 'edit') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('author', $posts, 'view') ? "allowed" : "denied");