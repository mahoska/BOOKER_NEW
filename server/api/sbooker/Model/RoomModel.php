<?php

class RoomModel extends Model
{
    public function __construct() 
    {
        parent::__construct();
        $this->tableName = 'roomBooker';
    }
}
