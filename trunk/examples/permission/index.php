<?php

require '../../haefko/loader.php';
debug::init(true);


$acl = new Permission();


# roles
$acl->addRole('member', 'guest');
$acl->addRole('admin', 'member');
$acl->addRole('superadmin', 'admin');


# resource
$acl->addResource('comments');
$acl->addResource('posts');


# privilegies
$acl->allow('guest', array('posts', 'comments'), 'view');
$acl->allow('member', 'comments', 'add');
$acl->allow('admin', 'posts', array('add', 'edit', 'delete'));
$acl->allow('superadmin', '*', '*');



echo "<br>allowed: " . ($acl->isAllowed('guest', 'posts', 'view') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('guest', 'comments', 'view') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('member', 'comments', 'view') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('admin', 'comments', 'add') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('admin', 'posts', 'view') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('superadmin', 'posts', 'delete') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('superadmin', 'comments', 'delete') ? "allowed" : "denied");

echo "<br>";
echo "<br>denied: " . ($acl->isAllowed('guest', 'comments', 'add') ? "allowed" : "denied");
echo "<br>denied: " . ($acl->isAllowed('guest', 'posts', 'add') ? "allowed" : "denied");
echo "<br>denied: " . ($acl->isAllowed('member', 'comments', 'delete') ? "allowed" : "denied");
echo "<br>denied: " . ($acl->isAllowed('admin', 'comments', 'delete') ? "allowed" : "denied");

echo "<br>";
echo "<br>allowed: " . ($acl->isAllowed('member', 'comments') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('guest', 'comments') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('superadmin', 'comments') ? "allowed" : "denied");
echo "<br>allowed: " . ($acl->isAllowed('admin', 'comments') ? "allowed" : "denied");