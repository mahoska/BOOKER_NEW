<?php

class Role extends Controller
{
    public function getAllRole()
    {
        $model = new RoleModel();
        return $model->selectAssoc(['id', 'name'], true);
    }
       
}

