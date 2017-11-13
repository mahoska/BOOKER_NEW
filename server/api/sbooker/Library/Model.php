<?php

abstract class Model
{
    protected $pdo;
    protected $tableName;
    
    public function __construct()
    {
        try{
            $this->pdo = DbConnection::getInstance()->getLink(); 
        }catch(PDOException $err){}
    }
    
    public function __destruct()
    {
        $this->pdo = null;
    }
    
    /*
    base function insert records
    @param <Array> $params - 
        associative array containing a list of fields and their meanings 
    @return <Array> result -
        return status the status of the operation, 
        in the case of the success of the id of the last added record
    */
    public function insert($params)
    {
        try{
            $tempFields = array_keys($params);
            $fieldStr = implode(", ", $tempFields);
            foreach ($tempFields as &$value) 
            {
                $value = ":".$value;
            }
            $valueStr = implode(", ", $tempFields);
 
            $query = "INSERT into $this->tableName ($fieldStr) VALUES ($valueStr)";
            $sth = $this->pdo->prepare($query); 
            $sth->execute($params);
            if($this->pdo->lastInsertId()>0)
                return ['status'=>200, 'data'=>$this->pdo->lastInsertId()];
            else 
                return ['status'=>500, 'clientCode'=>'0006'];
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        }
    }
    
    /*
    base function delete records
    @param <String> $condition - 
        string containing the condition of deleting a record
    @param <Array> $params - 
        associative array containing data for substitution into the condition
    @return <Array> result -
        return status the status of the operation, 
        if successful, returns the number of deleted records
    */
    public function delete($condition = null, $params = null){
        try{
            $query = "DELETE FROM $this->tableName "
                        . (isset($condition) ? "WHERE $condition" : "");
            if(isset($condition))
            {
                $sth = $this->pdo->prepare($query);
                $sth->execute($params); 
            }
            else
                $sth = $this->pdo->query($query);

            $count =  $sth->rowCount();
            if($count>0)
                return ['status'=>200, 'data'=>$count];
            else 
                return ['status'=>500, 'clientCode'=>'0012'];

        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        }
    }

     /*
    base function update records
    @param <Array> $fields - 
        list of editable fields
    @param <Array> $condition - 
        list of fields for which conditions are imposed
    @param <Array> $params - 
        associative array containing data for substitution into the condition
    @return <Array> result -
        return status the status of the operation, 
        if successful, returns 1
    */
    public function update($fields,  $condition, $params)
    {
        try{
            foreach ($fields as &$value) 
            {
                $value = $value." = :".$value;
            }
            $whatStr = implode(", ", $fields);

            if(isset($condition))
            {
                foreach ($condition as &$value) 
                {
                    $value = $value." = :".$value;
                }
                $conditionStr = implode(" AND ", $condition);
            }
            $query = "UPDATE  $this->tableName "
                       . "SET $whatStr "
                       . (isset($condition) ? "WHERE $conditionStr" : "");

            $sth = $this->pdo->prepare($query);
            $sth->execute($params); 
            $count =  $sth->rowCount();
            if($count>0)
                return ['status'=>200, 'data'=>1];
            else 
                return ['status'=>500, 'clientCode'=>'0012'];

       }catch(PDOException $err){
           file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
           return ['status'=>500, 'clientCode'=>'0006'];
       }
    }
    
     /*
    basic function of retrieving records
    @param <Array> $what - 
        list of fields whose values interest us
    @param <Bool> $allRecords -
      flag characterizing how many records should be selected 
      (all - true or one - false) 
    @param <String> $join -  join condition
    @param <Array> $condition - 
        list of fields for which conditions are imposed
    @param <Array> $params - 
        associative array containing data for substitution into the condition
    @param <String> $order - type sort
    @return <Array> result -
        return status the status of the operation associative array - sample
    */
    public function selectAssoc($what, $allRecords, $join = null, $condition = null, $params = null, $order = null)
    {
        try{
            $whatStr = implode(", ", $what);
                
            if(isset($condition))
            {
                if(is_array($condition))
                {
                    foreach ($condition as &$value) 
                    {
                        $value = $value." = :".$value;
                    }
                        $conditionStr = implode(" AND ", $condition);
                }
                else
                {
                    $conditionStr = $condition;
                }
            }

            $query = "SELECT $whatStr "
                        . "FROM $this->tableName "
                        . (isset($join) && !is_null($join) ? "$join " : "")
                        . (isset($condition) ? "WHERE $conditionStr " : "")
                        . (isset($order) ? "ORDER BY $order" : "");

            if(isset($condition))
            {
                $sth = $this->pdo->prepare($query);
                $sth->execute($params);
            }
            else
            {
                $sth = $this->pdo->query($query);
            }
            if($allRecords)
                return ['status'=>200, 'data'=>$this->getFetchAccoss($sth)];
            else
                return  ['status'=>200, 'data'=>$sth->fetch(\PDO::FETCH_ASSOC)];
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        } 
    }
    
    
      /*
    recording function
    @param <Array> $condition - 
        list of fields for which conditions are imposed
    @param <Array> $params - 
        associative array containing data for substitution into the condition
    @param <String> $order - type sort
    @return <Bool> $result -
        return bool value - whether there is a record
    */
    public function selectExists($params = null,  $condition = null)
    {
        try{
            if(isset($condition))
                $count = $this->selectCount($params, null,  $condition);
            else 
                $count = $this->selectCount();          
                if($count>0) 
                    return true;
                return false;
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            return ['status'=>500, 'clientCode'=>'0006'];
        } 
    }
    
    /*
    function that returns the number of records
    @param <Array>or<String> $condition - 
        list of fields or string -  characterizing the sampling condition
    @param <String> $join -  join condition
    @param <Array> $params - 
        associative array containing data for substitution into the condition
    @return <Integer> $result -
        number of records
     */
    public function selectCount($params=null,  $join = null, $condition = null )
    {
        if(isset($condition))
        {
            if(is_array($condition))
            {
                foreach ($condition as &$value) 
                {
                    $value = $value." = :".$value;
                }
                $conditionStr = implode(" AND ", $condition);
            }
            else
            {
                $conditionStr = $condition;
            }
        }

        $query = "SELECT  COUNT(*) "
                . "FROM $this->tableName "
                . (isset($join) && !is_null($join) ? "$join " : "")
                . (isset($condition) ? "WHERE $conditionStr" : "");

        if(isset($condition))
        {
            $sth = $this->pdo->prepare($query);
            $sth->execute($params); 
            
        }
        else
        {
            $sth = $this->pdo->query($query);
        }
            
        $res = $sth->fetch(\PDO::FETCH_NUM);
        return $res[0];
    }
    
    /*
    auxiliary function - associative array formation
     @return <Array> $result -
        associative array of sample data
    */   
    public function getFetchAccoss($sth)
    {
        $collection = [];
        while($res = $sth->fetch(\PDO::FETCH_ASSOC))
        {
            $collection[] = $res;
        }
         return $collection;
    }
}

