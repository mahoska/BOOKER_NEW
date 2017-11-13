<?php

class Room  extends Controller
{
    public function getAllRoom()
    {
        $model = new RoomModel();
        return $model->selectAssoc(['id', 'name'], true);
    }
    
    public function getItemRoom($params)
    {  
        if(count($params) != 1 && !(int)$params[0]>0)
        {
            return ['status'=>400, 'clientCode'=>'0001'];
        }
        $model = new RoomModel();
        return $model->selectAssoc(['name'], false, null, ['id'], ['id'=>$params[0]]);
     }
    
}

