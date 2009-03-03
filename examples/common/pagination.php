<?php

# nacteme knihovnu
require_once 'haefko/libs/paginator.php';

# nase data, ktera chceme strankovat
$data = array('Petr', 'Anna', 'Martin', 'Jan', 'Pavla', 'Romana', 'Alice', 'Lukas');

# stranka, kterou chceme zobrazit; ziskame z utl
$page = (isset($_GET['page']))? $_GET['page']: 1;

# pocet polozek na strance
$onpage = 5;

# vytvorime instanci strankovace
$paginator = new Paginator($page, ceil(count($data) / $onpage));

# vybareme potrebna data
$data = array_slice($data, ($page - 1) * $onpage, $onpage);

# data vypiseme
foreach ($data as $name)
    echo $name . '<br />';

# vypiseme strankovac
echo $paginator->render('pagination.php?page=<:page>');

# jeste pripojime styly
?>
<style>
.pagination {
	height: 30px;
	padding: 2em 0;
}

.pagination ul {
	margin: 0;
	padding: 0;
}

.pagination li {
	border: 0;
	margin: 0;
	padding: 0;
	list-style: none;
    float: left;
}

.pagination a {
	border: solid 1px #9aafe5;
	margin-right: 2px;
}

.pagination .prev-off, .pagination .next-off, .pagination .hellip {
	border: solid 1px #ababab;
	color: #888888;
	display: block;
	float: left;
	font-weight: bold;
	margin-right: 2px;
	padding: 3px 4px;
}
.pagination .next a, .pagination .prev a {
	font-weight: bold;
}

.pagination .active a {
	background: #CCE3FF;
	font-weight: bold;
}

.pagination a {
	color: #0e509e;
	display: block;
	float: left;
	padding: 3px 6px;
	text-decoration: none;
}
.pagination a:hover{
	border:solid 1px #0e509e
}
</style>