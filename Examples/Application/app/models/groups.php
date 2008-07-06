<?php


class GroupsModel extends Model
{


    public function groups()
    {
        return dibi::query('select *
                              from [demo_groups]');
    }


}