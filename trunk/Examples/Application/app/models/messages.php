<?php

class MessagesModel
{

	public function getMessages()
	{
		return dibi::query('
			SELECT *
			FROM [demo_messages]
				LEFT JOIN [demo_groups] USING([group_id])
			WHERE [group_id] = %i', $this->controller->getArg('group')
		)->fetchAssoc('=,message_id');
	}

}