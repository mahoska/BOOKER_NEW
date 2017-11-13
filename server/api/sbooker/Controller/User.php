<?php

class User  extends Controller
{  
    public function getAllUser()
    {
        $model = new UserModel();
        return $model->selectAssoc(['id', 'fullname', 'email'], true);
    }
    
    public function getItemUser($params)
    {
        if(count($params) != 1)
            return ['status'=>400, 'clientCode'=>'0001'];
        else
        {
            if(strlen($params[0]) == 32)
            {
                $model = new UserModel();
                //isLogin
                $data = $model->selectAssoc(
                    [
                        'userBooker.time_life',
                        'userBooker.id', 
                        'fullname', 
                        'roleBooker.name as role'
                    ],
                    false, 
                    "JOIN roleBooker ON userBooker.role_id = roleBooker.id",
                    ['status'], 
                    ['status'=>$params[0]]
                );

                $timeLife = $data['data']['time_life'];
                $timeNow =  time();
                if($timeLife > $timeNow)
                    return [
                        'status'=>200, 
                        'data'=>[
                            'id'=>$data['data']['id'], 
                            'role'=>$data['data']['role'],  
                            'fullname'=>$data['data']['fullname']
                        ]
                    ];
                else
                    return ['status'=>200, 'err'=>true, 'data'=>'hash is not valid'];
            }
            else if((int)$params[0]>0)
            {
                $model = new UserModel();
                return $model->selectAssoc(['login', 'email', 'fullname', 'id', 'role_id'],
                                            false, null,
                                            ['id'], 
                                            ['id'=>$params[0]]);
            }
            return ['status'=>400, 'clientCode'=>'0001'];
        }
    }
        
    public function postUser($params)
    {
        if(count($params) != 5)
            return ['status'=>400, 'clientCode'=>'0001'];
          
        $model = new UserModel();
        //isLoginExists
        $isUser = $model->selectExists(['login' => $params['login']], ['login']);
        if($isUser)
            return ['status'=>200, 'err'=>true, 'data'=>'login already exists'];
        
        $params['status'] = $this->setHash();
        $params['time_life'] =  time()+LIFE_ACTIVE_LOGIN;
        $params['password'] =  $this->getPasswordDb($params['password']);
       
        return $model->insert($params);
    }
    
    public function putUser($params)
    {
        $model = new UserModel();
        $fparams['login'] = $params->login;
        if(isset($params->edit))
        {
            //Check to not change the last admin role on user
            $countAdmins = $model->selectCount(
                ['id'=>$params->id], 
                "JOIN roleBooker ON userBooker.role_id = roleBooker.id",
                "roleBooker.name = 'admin' AND userBooker.id != :id"
            );
            //if the administrator tries to edit himself           
            if($countAdmins == 0)
            {
                $res = $model->selectAssoc(
                    ['role_id'],
                    false, 
                    null,
                    ['id'], 
                    ['id'=>$params->id]
                );
                if($res['data']['role_id'] !== $params->role_id)
                    return [
                        'status'=>200, 
                        'err'=>true, 
                        'data'=>'since you are the only admin, you can not change your rights'
                    ];
            }
            
            $fparams['fullname'] = $params->fullname;
            $fparams['role_id'] = $params->role_id;
            $fparams['email'] = $params->email;
            $fparams['id'] = $params->id;
            
            if($params->password != "")
            {
                $pass = md5($params->password);
                $str= 'passsolt';
                $str = md5($str);
                $pass_db = md5($params->password.$str);
                $fparams['password'] = $pass_db;
                return $model->updateUser($fparams, true);
            }
            else
                return $model->updateUser($fparams, false);
        }
        else
        {
            $fparams['password'] = $this->getPasswordDb($params->password);
            //isUser
            $isUser = $model->selectExists($fparams,['login','password']);
            if(!$isUser)
                return ['status'=>200, 'err'=>true, 'data'=>'user not exists'];

            $fparams['status'] = $this->setHash(); 
            $fparams['time_life'] =  time()+LIFE_ACTIVE_LOGIN;
            $what = ['status','time_life'];
            $condition = ['login', 'password'];
            $data =  $model->update($what, $condition, $fparams);
            if($data['status']==200)
            {
                //getClientId
                return  $model->selectAssoc(
                                            ['status', 'fullname', "roleBooker.name as role"],
                                            false, 
                                            "JOIN roleBooker ON userBooker.role_id = roleBooker.id",
                                            ['login', 'password'], 
                                            [
                                                'login'=>$fparams['login'],
                                                'password'=>$fparams['password']
                                            ]
                );
            }
        }
    }

    private function setHash()
    {
        $model = new UserModel();
        $countUsers = $model->countUser();
        //generate rand string
        $randStr = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $rand = '';
        for($i=0; $i<5; $i++) 
        {
            $key = rand(0, strlen($randStr)-1);
            $rand .= $randStr[$key];
        }
        
        return md5($rand.$countUsers);
    }
    
    private function getPasswordDb($password)
    {
        $pass = md5($password);
        $str= 'passsolt';
        $str = md5($str);
        return md5($password.$str);
    }
    
    public function deleteUser($params  = false)
    {
        if(count($params) == 1 && (int)$params[0]>0)
        {
            $model = new UserModel();
            $countAdmins = $model->selectCount(
                ['id'=>$params[0]], 
                "JOIN roleBooker ON userBooker.role_id = roleBooker.id",
                "roleBooker.name = 'admin' AND userBooker.id != :id"
            );
            if($countAdmins == 0)
                return [
                    'status'=>200, 
                    'err'=>true, 
                    'data'=>'removal impossible (this person is the only admin)'
                ];
            
            return $model->delete("id=:id",['id'=>$params[0]]);
            
            /*
            after deleting the user is triggered trigger:
                CREATE TRIGGER delete_events
                AFTER delete ON userBooker 
                for each row 
                begin
                    delete from eventBooker where start_event >= UNIX_TIMESTAMP(NOW()) and user_id = old.id
                end 
            */
        }
        return ['status'=>400, 'clientCode'=>'0001'];
    }

}
