<?php

# nacteme knihovnu
ob_start();
require_once 'haefko/loader.php';
debug::init(true);


# nase data, ktera chceme strankovat
$data = array('Petr', 'Anna', 'Martin', 'Jan', 'Pavla', 'Romana', 'Alice', 'Lukas');
# stranka, kterou chceme zobrazit; ziskame z utl
$page = (isset($_GET['page']))? $_GET['page']: 1;
# pocet polozek na strance
$onpage = 2;
# vytvorime instanci strankovace
$paginator = new Paginator($page, count($data), $onpage);


# vybareme potrebna data
$data = array_slice($data, ($page - 1) * $onpage, $onpage);
# data vypiseme
foreach ($data as $name)
    echo $name . '<br />';



$html = new HtmlHelper();
echo $html->paginator($paginator);

# jeste pripojime styly
?>
<style>
div.pagination {
	margin-bottom: 20px;
	height: 28px;
}

div.pagination span.hellip {
	float: left;
	padding: 5px 0;
}

div.pagination span.button, div.pagination a {
	float: left;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	padding: 2px 4px;
	margin: 2px;
	text-align: center;
}

div.pagination a {
	background: #3B699F;
	color: white;
	text-decoration: none;
}

div.pagination a.page-link {
	width: 16px;
}

div.pagination span.button {
	background: #99BBDF;
	color: white;
}

div.pagination span {
	margin: 0 4px;
}
</style>