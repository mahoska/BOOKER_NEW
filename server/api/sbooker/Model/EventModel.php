<?php

class EventModel extends Model
{
    public function __construct() 
    {
        parent::__construct();
        $this->tableName = 'eventBooker';
    }
}
