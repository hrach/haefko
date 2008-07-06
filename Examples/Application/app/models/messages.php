<?php


class MessagesModel extends Model
{


    public function message($id)
    {
        return dibi::fetch('select *
                              from [demo_messages]
                             where [message_id] = %i', $id);
    }


    public function messages($group)
    {
        return dibi::query('select *
                              from [demo_messages]
                                   left join [demo_groups] using([group_id])
                             where [group_id] = %i', $group)->fetchAssoc('=,message_id');
    }


}