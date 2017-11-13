<?php

class RoleModel extends Model
{
    public function __construct() 
    {
        parent::__construct();
        $this->tableName = 'roleBooker';
    }
}
