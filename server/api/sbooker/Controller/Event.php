<?php

class Event extends Controller
{
   /*
   function that checks the validity of the event
    @param <Array> $event - 
        data on the event of interest(start, end, date, room)
    @param <Bool> $without
        To exclude from the test sample the event itself
    @param <Integer> $id 
     - id event if $without == true
    @return <Bool> $flag -
        is the time for creating an event valid
    */
    private function checkEvent($event, $without = false, $id = null)
    {
        //check the validity of the event for the given day
        //select events in this date
        $unix_date_event = $event['day_event'];
        
        $unix_next_date_event = (new DateTime())->setTimestamp($event['day_event'])->modify('+1 day')->getTimestamp();
        $model = new EventModel();
        $cond = "start_event >= :day_event AND end_event < :nex_day_event AND room_id = :room_id" ;
        $conparams = [
                        'day_event'=>$unix_date_event, 
                        'nex_day_event'=>$unix_next_date_event, 
                        'room_id'=>$event['room_id']
                    ];
        if($without)
        {
            $cond .=  " AND id <> :id"; 
            $conparams['id'] = $id;
        }
        $res = $model->selectAssoc(
            ['start_event', 'end_event'],    
            true, null,
            $cond,
            $conparams,
            "start_event ASC"
          );

        $this_day_events = $res['data'];
        $count_events = count($this_day_events);

        if ($count_events == 0) 
            return true;

        $start_event = $event['start_event'];
        $end_event = $event['end_event'];
        if($count_events == 1 && ($end_event<=$this_day_events[0]['start_event'] || 
                $start_event>=$this_day_events[0]['end_event']))
        {
            return true;
        }
        else if($count_events >1)
        { 
            if($end_event<=$this_day_events[0]['start_event'] || 
                $start_event>=$this_day_events[$count_events-1]['end_event'])
            {
                return true;
            }
            else 
            {
                $flag = false;
                $len = count($this_day_events);
                for($i = 0; $i < $len; $i++) 
                {
                    if($start_event >= $this_day_events[$i]['end_event'] &&
                        $end_event <= $this_day_events[$i+1]['start_event'])
                    {
                        $flag = true;
                        break;
                    }
                }
                return $flag ? true : false;
            }
        }
        return false;
    } 
    
    
    public function postEvent($params)
    {
        if(count($params) != 9)
        {
            return ['status'=>400, 'clientCode'=>'0001'];
        }

        $model = new EventModel();
        //check events 
        $now  = (new DateTime())->getTimestamp();
        if($now > $params['start_event'])
            return ['status'=>200, "err"=>true, 
                'data'=>'event the event has already come'];
        else if($params['start_event'] >= $params['end_event'])
            return ['status'=>200, "err"=>true, 
                'data'=>'Uncorrect time. Time start must be less then time end and min time event = 30 minutese'];
        else
        {
            $eventCheck = ['start_event'=>$params['start_event'],
                            'end_event'=>$params['end_event'],
                            'day_event'=>$params['day_event'],
                            'room_id'=>$params['room_id']
                           ];
            
            $check = $this->checkEvent($eventCheck);
            $startP = (new DateTime())->setTimestamp($params['start_event']);
            $endP = (new DateTime())->setTimestamp($params['end_event']);
            if($check)
            { 
                $fparams = [
                            'user_id'=>$params['user_id'],
                            'room_id'=>$params['room_id'],
                            'start_event'=>$params['start_event'],
                            'start_event_format'=>$startP->format('d.m.y H:i:s'),
                            'end_event'=>$params['end_event'],
                            'end_event_format'=>$endP->format('d.m.y H:i:s'),
                            'description'=>$params['description'],
                            ];
                if(strcmp($params['is_repeate'],"false")==0)
                { 
                    $resAdd =  $model->insert($fparams);
                    $idRec  = $resAdd['data'];
                    if($idRec)
                    {
                        return [
                            'status'=>200,
                            'data'=>[
                                'count'=>1,
                                'events'=>[
                                    [
                                        'suc'=>1,
                                        'event'=> [
                                            "start"=>$fparams['start_event_format'],
                                            "end"=>$fparams['end_event_format']
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                    else 
                        return ['status'=>500, 'clientCode'=>'0014'];
                }
                else 
                {
                    $addEvent = [];
                    $count = 0;
                    $fparams['is_repeat'] = true;

                    $resAdd =  $model->insert($fparams);
                    $idRec  = $resAdd['data'];
                    if($idRec){
                        $addEvent[] = [
                                        'suc'=>1, 
                                        'event'=>[
                                            'start'=>$startP->format('d.m.y H:i:s'),
                                            'end'=>$endP->format('d.m.y H:i:s')
                                        ]
                                      ];
                        $count++;
                        $plus = "";
                        $correc = true;
                        switch($params['recur_period'])
                        {
                            case 'w':
                                $plus = ["+1 week", "+2 weeks", "+3 weeks", "+4 weeks"];
                                if($params['duration']<=0 || $params['duration']>4) $correc = false; 
                                break;
                            case 'b':
                                $plus = ["+2 weeks", "+4 weeks"];
                                if($params['duration']<=0 || $params['duration']>2) $correc = false;
                                break;
                            case 'm':
                                $plus = ["+1 month"];
                                if($params['duration']!=1) $correc = false;
                                break;
                        }
                        if($correc == false) 
                            return ['status'=>200,$err=true, 'data'=>'uncorrect duration'];

                        for($i = 0; $i < (int)$params['duration']; $i++)
                        {
                            $start_event = (new DateTime())->setTimestamp($params['start_event'])->modify($plus[$i])->getTimestamp();
                            $end_event = (new DateTime())->setTimestamp($params['end_event'])->modify($plus[$i])->getTimestamp();
                            $day_event = (new DateTime())->setTimestamp($params['day_event'])->modify($plus[$i])->getTimestamp();

                            $eventCheck = [
                                'start_event'=>$start_event,
                                'end_event'=>$end_event,
                                'day_event'=>$day_event,
                                'room_id'=>$params['room_id']
                             ];
                            $check = $this->checkEvent($eventCheck);
                            if($check)
                            {
                                $start = (new DateTime())->setTimestamp($start_event);
                                $end = (new DateTime())->setTimestamp($end_event);
                                $event = [
                                    'room_id'=>$params['room_id'], 
                                    'user_id' => $params['user_id'],
                                    'start_event'=>$start_event,
                                    'start_event_format' => $start->format('d.m.y H:i:s'),
                                    'end_event'=>$end_event,
                                    'end_event_format' => $end->format('d.m.y H:i:s'),
                                    'description' => $params['description'],
                                    'is_repeat' => false,
                                    'parent_event_id' => $idRec
                                ];
                                $res = $model->insert($event);
                                if($res['data']>0) 
                                {
                                    $count++;
                                    $addEvent[] = [
                                        'suc'=>1, 
                                        'event'=> [
                                            "start"=>$event['start_event_format'], 
                                            "end"=>$event['end_event_format']
                                        ]
                                    ];
                                }
                            }
                            else
                            {
                                $addEvent[] = [
                                                'suc'=>0, 
                                                'event'=> [
                                                            "start"=>(new DateTime())->setTimestamp($start_event)->format('d.m.y H:i:s'), 
                                                            "end"=>(new DateTime())->setTimestamp($end_event)->format('d.m.y H:i:s')
                                                ]
                                              ];
                            }
                        }
                       
                    }
                    else
                    {
                        $addEvent[] = [
                                        'suc'=>0,
                                        'event'=>[ "start"=>$startP->format('d.m.y H:i:s'), 
                                                   "end"=>$endP->format('d.m.y H:i:s')
                                        ]
                                      ];
                    }
                    return ['status'=>200, 'data'=>['count'=>$count, 'events'=>$addEvent]];
                }
            }
            else
            {
                return [
                        'status'=>200, 
                        'err'=>true, 
                        'data'=>"Event ". $startP->format('d.m.y H:i:s').":".$endP->format('H:i:s')." doesn't added. Most likely this time is already taken"
                    ];   
            }
        }
    }
    
    public function getDataEvent($params)
    {
        if(count($params) != 3)
            return ['status'=>400, 'clientCode'=>'0001'];

        $cur_month = $params[1];
        $next_month = (new DateTime())->setTimestamp($cur_month)->modify('+1 month')->getTimestamp();
        $next_month_test  = strtotime('+1 month',$cur_month);
        $model = new EventModel();
        $data =  $model->selectAssoc(
                        ['start_event', 'end_event', 'id', 'start_event_format', 'end_event_format'],     
                        true, null,
                        "start_event >= :cur_month AND end_event < :next_month AND room_id = :room_id",
                        ['cur_month'=>$cur_month,'next_month'=>$next_month, 'room_id'=>$params[0]],
                        "start_event ASC");
        //if events exists
        if(count($data['data']) != 0)
        {  
            $data_new = [];
            $days = [];
            
            $i = 0; 
            foreach($data['data'] as $key=>$val) 
            {
                $data_new[$i]= [
                    'day'=>(int) date('d',$val['start_event']),
                    'event'=>[
                                'start_event'=>(new DateTime())->setTimestamp($val['start_event'])->format('H:i'),
                                'end_event'=>(new DateTime())->setTimestamp($val['end_event'])->format('H:i'),
                                'start_event_12'=>(new DateTime())->setTimestamp($val['start_event'])->format('h:i A'),
                                'end_event_12'=>(new DateTime())->setTimestamp($val['end_event'])->format('h:i A'),
                                'id'=>$val['id']
                            ]
                    ];
                $i++;
            }
            $events = [];
            $events[] =  ['day'=> 0, 'day_events'=> []];
            for($i = 1; $i <= $params[2]; $i++) 
            {
                $temp = [];
                foreach ($data_new as $key => $val) 
                {
                    if($data_new[$key]['day'] == $i)
                        array_push($temp, $val['event']);
                }
                $events[] = ['day'=> $i, 'day_events'=> $temp];
            }
        }
        else
        {
            $events = [];
            $events[] =  ['day'=> 0, 'day_events'=> []];
            for($i = 1; $i <= $params[2]; $i++)
            {
                $events[] = ['day'=> $i, 'day_events'=> []];
            }
        }
        return ['status'=>200, 'data'=>$events];
    }
    
    public function getItemEvent($params)
    {   
        if(count($params) != 1 || !(int)$params[0]>0)
            return ['status'=>400, 'clientCode'=>'0001'];
        else
        {
            $model = new EventModel();
                return $model->selectAssoc(
                    [
                        'start_event', 
                        'end_event', 
                        'user_id',
                        'fullname', 
                        'description',
                        'room_id',
                        'data_create', 
                        'parent_event_id', 
                        'is_repeat'
                    ],
                    false,
                    ' JOIN userBooker on userBooker.id = eventBooker.user_id ',
                    "eventBooker.id = :id", 
                    ['id'=>$params[0]]
                );
        }
    }
    
    public function deleteEvent($params)
    {
         if(count($params) == 2 && (int)$params[0]>0 && ((int)$params[1] == 1 || (int)$params[1] == 0))
         {
            $model = new EventModel();
            if((int)$params[1] == 0)
                return $model->delete("id=:id",['id'=>$params[0]]);

            //read 'parent_event_id', 'is_repeat'
            $infoDelRec = $model->selectAssoc(
                ['parent_event_id', 'is_repeat', 'start_event'],
                false, null,
                ['id'], 
                ['id'=>$params[0]]
            );

            $parent = $infoDelRec['data']['parent_event_id'];
            $repeat = $infoDelRec['data']['is_repeat'];
            $startCur = $infoDelRec['data']['start_event'];
            $now = time();
            if(is_null($repeat) && is_null($parent))
                return $model->delete("id=:id",['id'=>$params[0]]);
            else if($parent > 0) 
            {
                //must delete the records of the same parent and have not yet arrived and after it
                return $model->delete(
                    "((parent_event_id = :parent AND start_event > :startCur) OR id=:id) AND start_event > $now", 
                    ['parent'=>$parent, 'id'=>$params[0], 'startCur'=>$startCur]
                );
            }
            else if($repeat == 1) 
            {
                //the record is recursive and parental => remove unreached descendants
                return $model->delete(
                    "(parent_event_id = :id OR id=:id) AND start_event > $now ", 
                    ['id'=>$params[0]]
                );
            }
            return ['status'=>400, 'clientCode'=>'0015'];
        }
        else
            return ['status'=>400, 'clientCode'=>'0001'];
    }
    
    public function putEvent($params)
    {
        $now  = (new DateTime())->getTimestamp();
        if($now > $params->unix_start)
            return ['status'=>200, "err"=>true, 
                'data'=>'event the event has already come. Unable to update'];
        else if($params->unix_start >= $params->unix_end)
            return ['status'=>200, "err"=>true, 
                'data'=>'Uncorrect time. Time start must be less then time end and min time event = 30 minutese'];
        else
        { 
            $model = new EventModel();
            if($params->is_recurs == 0)
            {
               return $this->updateItemEvent(
                    $params->id, $params->unix_start, 
                    $params->unix_end, $params->unix_day,
                    $params->room_id, $params->description, 
                    $params->user_id
                );  
            }
            else
            {
                //recurse
                $infoUpdRec = $model->selectAssoc(
                    ['parent_event_id', 'is_repeat', 'start_event'],
                    false, null,
                    ['id'], 
                    ['id'=>$params->id]
                );
                $parent = $infoUpdRec['data']['parent_event_id'];
                $repeat = $infoUpdRec['data']['is_repeat'];
                $startCur = $infoUpdRec['data']['start_event'];
                $now = time();
                //if the event is not repetitive, but this recursiveness flag 
                //was erroneously passed in the parameters
                if(is_null($repeat) && is_null($parent))
                {
                        return $this->updateItemEvent(
                            $params->id, 
                            $params->unix_start, 
                            $params->unix_end, 
                            $params->unix_day,
                            $params->room_id, 
                            $params->description, 
                            $params->user_id
                        );
                }
                else 
                {
                    if($parent > 0)
                    {
                        //must delete the records of the same parent and have not yet arrived and after it
                        $resSamplingUpdRecs =  $model->selectAssoc(
                            ['id', 'start_event', 'end_event'],
                            true, 
                            null,
                            "((parent_event_id = :parent AND start_event > :startCur) OR id=:id) AND start_event > $now",
                            ['parent'=>$parent, 'id'=>$params->id, 'startCur'=>$startCur]
                        );
                    }//if have parent
                    else if($repeat == 1)
                    {
                        //the record is recursive and parental => update unreached descendants
                        $resSamplingUpdRecs =  $model->selectAssoc(
                            ['id', 'start_event', 'end_event'],
                            true, 
                            null,
                            "(parent_event_id = :id OR id=:id) AND start_event > $now ",
                            ['id'=>$params->id]
                        );  
                    }//if parent itself
                    if($resSamplingUpdRecs['status'] == 200)
                    {
                        $resUpdRecs = $resSamplingUpdRecs['data'];
                        $updates = [];
                        $count = 0;
                        foreach ($resUpdRecs as $key=>$value)
                        {
                            $id = $value['id'];
                            $start = (new DateTime())->setTimestamp($value['start_event'])->setTime($params->start_hour,$params->start_minute)->getTimestamp();
                            $end = (new DateTime())->setTimestamp($value['end_event'])->setTime($params->end_hour,$params->end_minute)->getTimestamp();;
                            $startDate = (new DateTime())->setTimestamp($value['start_event']);
                            $year = $startDate->format('Y');
                            $month= $startDate->format('m');
                            $date= $startDate->format('d');
                            $day = (new DateTime($year.'-'.$month.'-'.$date))->getTimestamp();
                            $room_id = $params->room_id;
                            $description = $params->description; 
                            $user_id = $params->user_id;
                            
                            $resUpd = $this->updateItemEvent($id, $start, $end, $day, $room_id, $description, $user_id);
                            if($resUpd['status'] == 200 && $resUpd['data']['count'] == 1)
                            {
                                $suc = 1;
                                $count++;
                            }
                            else 
                                $suc = 0; 
                            
                            $endDate = (new DateTime())->setTimestamp($value['end_event']);
                            $updates[] = [
                                'suc'=>$suc,
                                'event'=>[ 
                                    "start"=>$startDate->format('d.m.y H:i:s'), 
                                    "end"=>$endDate->format('d.m.y H:i:s')
                                ]
                            ];
                        }
                        return  ['status'=>200, 'data'=>['count'=>$count, 'events'=>$updates]];
                    }
                    else 
                    {
                        return $this->updateItemEvent( 
                            $params->id, 
                            $params->unix_start, 
                            $params->unix_end, 
                            $params->unix_day,
                            $params->room_id, 
                            $params->description, 
                            $params->user_id
                        );
                
                    }//if exists recursive updates records    
                }
            }//is recurse        
        }//is valid
    }
       
    public function updateItemEvent($id, $start, $end, $day, $room_id, $description, $user_id)
    {
        $eventCheck = ['start_event'=> $start,
                        'end_event'=> $end,
                        'day_event'=> $day,
                        'room_id'=> $room_id
                       ];
        //validation takes place even if the date does not change,
        // as an additional safeguard mechanism for verifying the validity of the parameters
        $check = $this->checkEvent($eventCheck, true, $id);
        $startFormat = (new DateTime())->setTimestamp($start)->format('d.m.y H:i:s');
        $endFormat = (new DateTime())->setTimestamp($end)->format('d.m.y H:i:s');
        if($check)
        {
            $model = new EventModel();
            $resUpd  = $model->update(
                [
                    'start_event',
                    'start_event_format',
                    'end_event',
                    'end_event_format',
                    'description',
                    'user_id'
                ],
                ['id'],
                [
                    'id'=>$id,
                    'start_event'=>$start,
                    'start_event_format'=>$startFormat,
                    'end_event'=>$end,
                    'end_event_format'=>$endFormat,
                    'description'=>$description,
                    'user_id'=>$user_id,
                ]
            );

            if($resUpd['status'] == 200) 
            {
                return [
                    'status'=>200,
                    'data'=>[
                        'count'=>1, 
                        'events'=>[
                            [
                                'suc'=>1,  
                                'event'=>[
                                    "start"=>$startFormat,
                                    "end"=>$endFormat
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
                return ['status'=>500, 'clientCode'=>'0012'];   
        }
        else
        {
            return [
                'status'=>200,
                'data'=>[
                    'count'=>0, 
                    'events'=>[
                        [ 
                            'suc'=>0,  
                            'event'=>[
                                "start"=>$startFormat, 
                                "end"=>$endFormat
                            ]
                        ]
                    ]
                ]
            ];
        }
    }
    
    public function getAllEvent()
    {
        $model = new EventModel();
        return $model->selectAssoc(['id', 'user_id', 'room_id', 'description'], true);
    }
}

