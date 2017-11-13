<?php

class UserModel extends Model
{
    public function __construct() 
    {
        parent::__construct();
        $this->tableName = 'userBooker';
    }
    
    public function countUser()
    {
        try{
            return $this->selectCount(); 
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        } 
    } 
    
    public function updateUser($params, $ifPass)
    {
        try{
            $query = "UPDATE $this->tableName "
                    . "SET fullname = :fullname, login = :login,"
                    . "role_id = :role_id, email = :email"
                    . (($ifPass)? ", password = :password  ":" ")
                    . "WHERE id = :id LIMIT 1";

            $sth = $this->pdo->prepare($query);
            $sth->execute($params);
            $count =  $sth->rowCount();
            if($count>0)
                return ['status'=>200, 'data'=>$count];
             else 
                return ['status'=>500, 'clientCode'=>'0009'];
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        }
    }
}
    




