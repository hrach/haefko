<?php

$data = $controller->model->getMessages();

$this->title = "Zpravičky - skupina: $data[name]";
$this->description = 'Vaše uložiště pro vaše zpravičky';

foreach ($data['message_id'] as $message) {
	$item = $this->item();

	//$item->title = $message['author'] . '-' . substr($message['text'], 0, 20);

	$item->link = "/edit/message:$message[message_id]";
	$item->description = $message['text'];
	$item->author = $message['author'];
	$item->date = $message['date'];
	$item->category = $data['name'];

}