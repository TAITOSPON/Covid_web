<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_Covid_Doctor extends CI_Model
{       
 
    public function Get_id_chief_by_user_ad_dapt_code($user_ad_dept_code){

    
        $dept_code = substr($user_ad_dept_code, 0 ,-4); 
      
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://change.toat.co.th/api_list/index.php/api/Users/getBossDept');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( array('dept' =>  $dept_code)) );
        $result = curl_exec($ch);
        curl_close($ch);
  
        return $result;
        
    }

    public function Get_id_chief_by_dapt_code($dept_code){

      
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://change.toat.co.th/api_list/index.php/api/Users/getBossDept');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( array('dept' =>  $dept_code)) );
        $result = curl_exec($ch);
        curl_close($ch);
  
        return $result;
        
    }

   

    public function Get_All_Dept_Name(){
        // $user = $this->db->query("SELECT DISTINCT user_ad_dept_code FROM cv_user")->result_array();

        $user = $this->db
        ->query("SELECT DISTINCT user_ad_dept_code 
            FROM `cv_user_latest_status` 
            INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code ")
            // WHERE `chief_approve_result_check` = 2")
        ->result_array();
        //    print_r($this->db->last_query());  exit();
        $data = array();

        for($i=0; $i < sizeof($user); $i++){

            $user_ad_dept_code = $user[$i]['user_ad_dept_code'];
            $result_dept = array(json_decode($this->Get_id_chief_by_user_ad_dapt_code($user_ad_dept_code), true));
            $data[$i] = $result_dept[0]['DEPT_CODE'];   
        }

        $result_dept_name['DEPT_CODE'] = array_unique($data);
        // return $result_dept_name;
       
        $data = array();
        for($i=0; $i < sizeof($user); $i++){

            $user_ad_dept_code = $user[$i]['user_ad_dept_code'];
            $result_dept = array(json_decode($this->Get_id_chief_by_user_ad_dapt_code($user_ad_dept_code), true));
            $data[$i] = $result_dept[0]['DEPT_NAME'];   
        }

        // return $data;
        $result_dept_name['DEPT_NAME'] = array_unique($data);
        // return $result_dept_name;

        $data_DEPT_NAME = array();
        $data_DEPT_CODE = array();
        foreach ($result_dept_name['DEPT_NAME'] as $value) { array_push( $data_DEPT_NAME, $value); }
        foreach ($result_dept_name['DEPT_CODE'] as $value) { array_push( $data_DEPT_CODE, $value);  }
 
        $data_DEPT = array();

        for($i=0; $i < sizeof($data_DEPT_CODE); $i++){
                
            // $data_DEPT[$i]['DEPT_CODE'] = $data_DEPT_CODE[$i];   
            // $data_DEPT[$i]['DEPT_NAME'] = $data_DEPT_NAME[$i];   

            $data_DEPT[$i]['DEPT_CODE'] = $data_DEPT_CODE[$i];   
            $data_DEPT[$i]['DEPT_NAME'] = $data_DEPT_CODE[$i];  

          
        }
        return array(  'status' => "true" , 'result' => $data_DEPT);


        // print_r($data_DEPT); exit();
        
    }


    public function Doctor_Get_All_User_by_dept_code($result){

        if(isset($result['dept_code'])){
            $dept_code = $result['dept_code'];

            if($dept_code == "0000"){
                $result_dept = $this->Get_All_Dept_Name();
                // print_r($result_dept); exit();
                $result_dept =  $result_dept['result'];
            
                for($i=0; $i < sizeof($result_dept); $i++){
    
                    $dept_code = $result_dept[$i]['DEPT_CODE'] ;
                    $dept_code = substr($dept_code, 0 ,-4); 
    
                
                    $user_result = $this->db
                        ->query("SELECT * FROM `cv_user_latest_status`
                            INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                            WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'

                            AND `cv_user_latest_status`.`self_assessment_sum_result` !=1
                            --  AND `cv_user_latest_status`.chief_approve_result_check = 2

                          ")

                        ->result_array();
                            // print_r($this->db->last_query());  exit();
                    // print_r($); exit();
                    for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                        $user_ad_code = $user_result[$index_user_result]['user_ad_code'];
    
                    
                        $user_self_assessment_result = $this->db  
                        ->query("SELECT * FROM `cv_self_assessment`
                            WHERE `user_ad_code` =  '$user_ad_code'
                            -- AND `chief_approve_result_check` = 2
                            -- AND `nurse_comment_id` != 0 
                            ORDER BY `self_assessment_id` DESC LIMIT 100")
                        ->result_array();

                        
                        $user_result[$index_user_result]['user_self_assessment_result'] = $user_self_assessment_result;


                        for($index_user_self_assessment_result=0; $index_user_self_assessment_result < sizeof($user_self_assessment_result); $index_user_self_assessment_result++){


                            $self_assessment_result = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result"];
                            // $self_assessment_result_specific = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result_specific"];
                            
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextNormal'] = $this->db
                            ->query("SELECT *  FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                            ->result_array();
            
                            // $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextSpecific'] = $this->db
                            // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                            // ->result_array();

                            $self_assessment_id  = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_id"];

                            $self_assessment_detail =  $this->db
                            ->query("SELECT * FROM `cv_self_assessment_detail` WHERE self_assessment_id = '$self_assessment_id'")
                            ->result_array();

                            if(sizeof($self_assessment_detail) != 0){

                                $self_assessment_detail_result = array(json_decode($self_assessment_detail[0]['self_assessment_detail_result'], true)); 
                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_detail'] = $self_assessment_detail_result;

                            }else{
                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_detail'] = array();
                            }

                            $nurse_comment_id = $user_self_assessment_result[$index_user_self_assessment_result]['nurse_comment_id'];
                        
                            if($nurse_comment_id != "0"){

                                $nurse_comment_result = $this->db
                                ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                                ->result_array();

                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = $nurse_comment_result;

                            }else{
                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = array();

                            }

                            $chief_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['chief_approve_id'];

                            if($chief_approve_id != "0"){

                                $chief_approve_result = $this->db
                                ->query("SELECT * FROM `cv_chief_approve` WHERE `chief_approve_id` =  '$chief_approve_id'")
                                ->result_array();

                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = $chief_approve_result;

                            }else{
                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = array();

                            }

                            $doctor_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['doctor_approve_id'];

                            if($doctor_approve_id != "0"){

                                $doctor_approve_result = $this->db
                                ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                                ->result_array();

                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = $doctor_approve_result;

                            }else{
                                $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = array();

                            }

                        }
    
                    }
    
                    $result_dept[$i]["RESULT_ALL_USER"] =  $user_result ;

                    $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                    $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
    
                }
    
            }else{

                $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                // $result_dept[]
                $result_dept[0]['DEPT_CODE'] =  $result_dept_[0]['DEPT_CODE'];
                $result_dept[0]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];

                $dept_code = $result_dept[0]['DEPT_CODE'] ;
                $dept_code = substr($dept_code, 0 ,-4); 
    
                $user_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`
                    INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                  
                    AND `cv_user_latest_status`.`self_assessment_sum_result` !=1
                    ")
                ->result_array();

                for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                    $user_ad_code = $user_result[$index_user_result]['user_ad_code'];

                 
                    $user_self_assessment_result = $this->db  
                    ->query("SELECT * FROM `cv_self_assessment`
                        WHERE `user_ad_code` =  '$user_ad_code'
                        -- AND `chief_approve_result_check` = 2
                        -- AND `nurse_comment_id` != 0 
                        ORDER BY `self_assessment_id` DESC LIMIT 100")
                    ->result_array();


                    $user_result[$index_user_result]['user_self_assessment_result'] = $user_self_assessment_result;

                    for($index_user_self_assessment_result=0; $index_user_self_assessment_result < sizeof($user_self_assessment_result); $index_user_self_assessment_result++){


                        $self_assessment_result = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result"];
                        // $self_assessment_result_specific = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result_specific"];
                        
                        $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextNormal'] = $this->db
                        ->query("SELECT *  FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                        ->result_array();
        
                        // $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextSpecific'] = $this->db
                        // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                        // ->result_array();

                        $self_assessment_id  = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_id"];

                        $self_assessment_detail =  $this->db
                        ->query("SELECT * FROM `cv_self_assessment_detail` WHERE self_assessment_id = '$self_assessment_id'")
                        ->result_array();

                        if(sizeof($self_assessment_detail) != 0){

                            $self_assessment_detail_result = array(json_decode($self_assessment_detail[0]['self_assessment_detail_result'], true)); 
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_detail'] = $self_assessment_detail_result;

                        }else{
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_detail'] = array();
                        }

                        $nurse_comment_id = $user_self_assessment_result[$index_user_self_assessment_result]['nurse_comment_id'];
                    
                        if($nurse_comment_id != "0"){

                            $nurse_comment_result = $this->db
                            ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                            ->result_array();

                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = $nurse_comment_result;

                        }else{
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = array();

                        }

                        $chief_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['chief_approve_id'];

                        if($chief_approve_id != "0"){

                            $chief_approve_result = $this->db
                            ->query("SELECT * FROM `cv_chief_approve` WHERE `chief_approve_id` =  '$chief_approve_id'")
                            ->result_array();

                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = $chief_approve_result;

                        }else{
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = array();

                        }

                        $doctor_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['doctor_approve_id'];

                        if($doctor_approve_id != "0"){

                            $doctor_approve_result = $this->db
                            ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                            ->result_array();

                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = $doctor_approve_result;

                        }else{
                            $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = array();

                        }

                    }

                }

                // return array(  'status' => "true" , 'result' => $user_result);
                $result_dept[0]['RESULT_ALL_USER'] = $user_result;
              
            }

            $data = array(  'status' => "true" , 'result' => $result_dept);

            for($j=0; $j < sizeof($result_dept); $j++){
                 
                  if(sizeof( $result_dept[$j]['RESULT_ALL_USER'] ) == 0 ){
                      unset($data['result'][$j]);
                  }else{

                    for($n=0; $n < sizeof($result_dept[$j]['RESULT_ALL_USER']); $n++){

                        if(sizeof( $result_dept[$j]['RESULT_ALL_USER'][$n]['user_self_assessment_result'] ) == 0 ){
                            unset($data['result'][$j]);
                        }
                        
                    }


                  }

              }


            return $data ;
          
            // return array(  'status' => "true" , 'result' => $result_dept);
        }else{
            return array(  'status' => "false" , 'result' => "request dept_code");
        }

    }

    public function Doctor_Get_self_assessment_by_user_ad($result){
        if(isset($result['self_assessment_id'])){
            if(isset($result['user_ad_code'])){

                $user_ad_code = $result['user_ad_code'];
                $self_assessment_id = $result['self_assessment_id'];

                $get_user_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`INNER JOIN `cv_user` 
                    ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_code = '$user_ad_code'")
                ->result_array();

                $result_self_assessment = $this->db  
                ->query("SELECT * FROM `cv_self_assessment` WHERE `self_assessment_id` = '$self_assessment_id' AND `user_ad_code` = '$user_ad_code' ")
                ->result_array();
                
                for($i=0; $i < sizeof($result_self_assessment); $i++){

                    $self_assessment_result = $result_self_assessment[$i]["self_assessment_result"];
                    $self_assessment_result_specific = $result_self_assessment[$i]["self_assessment_result_specific"];
                    
                    $result_self_assessment[$i]['self_assessment_TextNormal'] = $this->db
                    ->query("SELECT * FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                    ->result_array();
    
                    // $result_self_assessment[$i]['self_assessment_TextSpecific'] = $this->db
                    // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    // ->result_array();
                }
                
                $nurse_comment_id = $result_self_assessment[0]['nurse_comment_id'];
            
                if($nurse_comment_id != "0"){

                    $nurse_comment_result = $this->db
                    ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                    ->result_array();

                    $result_self_assessment[0]['nurse_comment_result'] = $nurse_comment_result;

                }else{
                    $result_self_assessment[0]['nurse_comment_result'] = array();

                }

                $doctor_approve_id = $result_self_assessment[0]['doctor_approve_id'];

                if($doctor_approve_id != "0"){

                    $doctor_approve_result = $this->db
                    ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                    ->result_array();

                    $result_self_assessment[0]['doctor_approve_result'] = $doctor_approve_result;

                }else{
                    $result_self_assessment[0]['doctor_approve_result'] = array();

                }


                $data = array( 
                    'chief_result' => array(json_decode($this->Get_id_chief_by_user_ad_dapt_code($get_user_result['0']['user_ad_dept_code']), true)),
                    'user_result' => $get_user_result,
                    'detail_self_assessment' => $result_self_assessment
                    
                );     
    
                $result = array( 
                    'status' => "true",
                    'result' => $data
                        
                );     
    
                return  $result;


            }else{
                return array(  'status' => "false" , 'result' => "request user_ad_code");
            }
        }else{
            return array(  'status' => "false" , 'result' => "request self_assessment_id");
        }

    }
   

    // public function Doctor_approve_status_wfh($result){

    //     if(isset($result['doctor_approve_by_id'])){

    //         if(isset($result['user_ad_code'])){

    //             if(isset($result['doctor_approve_detail'])){

    //                 if(isset($result['doctor_approve_status_wfh'])){

    //                     if(isset($result['doctor_approve_wfh_date_start'])){

    //                         if(isset($result['doctor_approve_wfh_date_end'])){

    //                             if(isset($result['self_assessment_id'])){


    //                                 $doctor_approve_by_id = $result['doctor_approve_by_id'];
    //                                 $user_ad_code = $result['user_ad_code'];
    //                                 $doctor_approve_detail = $result['doctor_approve_detail'];
    //                                 $doctor_approve_status_wfh = $result['doctor_approve_status_wfh'];
    //                                 $doctor_approve_wfh_date_start = $result['doctor_approve_wfh_date_start'];
    //                                 $doctor_approve_wfh_date_end = $result['doctor_approve_wfh_date_end'];
    //                                 $self_assessment_id = $result['self_assessment_id'];
            
            
    //                                 $data = array(
    //                                     'doctor_approve_id' => NULL,
    //                                     'user_ad_code' => $user_ad_code,
    //                                     'doctor_approve_by_id' => $doctor_approve_by_id,
    //                                     'doctor_approve_result' =>  "1",
    //                                     'doctor_approve_datetime_create' => date("Y-m-d h:i:s"),
    //                                     'doctor_approve_detail' =>  $doctor_approve_detail,
    //                                     'doctor_approve_status_wfh' => $doctor_approve_status_wfh,
    //                                     'doctor_approve_wfh_date_start' => $doctor_approve_wfh_date_start,
    //                                     'doctor_approve_wfh_date_end' => $doctor_approve_wfh_date_end
    //                                 );
    
    //                                 $this->db->insert('cv_doctor_approve', $data);
    //                                 if(($this->db->affected_rows() != 1) ? false : true){
                                   
    //                                     $doctor_approve_result = $this->db
    //                                     ->query("SELECT * FROM `cv_doctor_approve` WHERE `user_ad_code` = '$user_ad_code' ORDER BY `doctor_approve_id` DESC LIMIT 1")
    //                                     ->result_array();

    //                                     $this->db->trans_begin();
    //                                     $this->db->where('self_assessment_id', $self_assessment_id)
    //                                     ->set(
    //                                         array( 
        
    //                                             'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
    //                                             'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
    //                                             'doctor_approve_status_wfh' => $doctor_approve_result[0]['doctor_approve_status_wfh'],
        
    //                                             )) ->update('cv_self_assessment');
        
                
    //                                     if ($this->db->trans_status() === false) {
    //                                         $this->db->trans_rollback();
    //                                         return  array(  'status' => "false" , 'result' => "update cv_self_assessment_false" );
    //                                     } else {
    //                                         $this->db->trans_commit();

    //                                             if($doctor_approve_result[0]['doctor_approve_status_wfh'] == "2"){

    //                                                 $data = array(
    //                                                     'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
    //                                                     'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
    //                                                     'doctor_approve_status_wfh' => $doctor_approve_result[0]['doctor_approve_status_wfh'],
    //                                                     'self_assessment_sum_result' => "1"
    //                                                 );

    //                                             }else{
    //                                                 $data = array(
    //                                                     'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
    //                                                     'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
    //                                                     'doctor_approve_status_wfh' => $doctor_approve_result[0]['doctor_approve_status_wfh'],
                
    //                                                 );
    //                                             }

    //                                             $this->db->trans_begin();
    //                                             $this->db->where('user_ad_code', $user_ad_code)->set($data)->update('cv_user_latest_status');
                
    //                                             if ($this->db->trans_status() === false) {
    //                                                 $this->db->trans_rollback();
    //                                                 return  array(  'status' => "false" , 'result' => "update cv_user_latest_status" );
    //                                             } else {
    //                                                 $this->db->trans_commit();
    //                                                     return array(  'status' => "true" , 'result' => "doctor_approve_wfh true");

    //                                             }
    //                                     }
                            
    //                                 }
    


    //                             }else{
    //                                 return array(  'status' => "false" , 'result' => "request self_assessment_id");
    //                             }
    //                         }else{
    //                             return array(  'status' => "false" , 'result' => "request doctor_approve_wfh_date_end");
    //                         }
    //                     }else{
    //                         return array(  'status' => "false" , 'result' => "request doctor_approve_wfh_date_start");
    //                     }
    //                 }else{
    //                     return array(  'status' => "false" , 'result' => "request doctor_approve_status_wfh");
    //                 }
    //             }else{
    //                 return array(  'status' => "false" , 'result' => "request doctor_approve_detail");
    //             }
    //         }else{
    //             return array(  'status' => "false" , 'result' => "request user_ad_code");
    //         }
    //     }else{
    //         return array(  'status' => "false" , 'result' => "request doctor_approve_by_id");
    //     }

        
    // }

    public function Doctor_approve_status_wfh($result){

        if(isset($result['doctor_approve_by_id'])){

            if(isset($result['doctor_approve_detail'])){
                        
                if(isset($result['self_assessment_id'])){

                        // return $result['self_assessment_id'][1];
                        // return array(
                        //     'doctor_approve_by_id' => "003599",                
                        //     'doctor_approve_detail' => "text detail",
                        //     'self_assessment_id' => array('517','518','519')
                        // );

                        
                        // return sizeof($result['self_assessment_id']);
                        if( sizeof($result['self_assessment_id']) != 0){


                   

                            for($i=0; $i < sizeof($result['self_assessment_id']); $i++){
                                // print_r($i);

                               
                                    $self_assessment_id = $result['self_assessment_id'][$i];

                                    $doctor_approve_result = $this->db
                                        ->query("SELECT * FROM `cv_self_assessment` WHERE self_assessment_id = '$self_assessment_id'")
                                        ->result_array();
                                   

                                    $user_ad_code =  $doctor_approve_result[0]['user_ad_code'];
                                   
                                    $doctor_approve_by_id = $result['doctor_approve_by_id'];
                                    $doctor_approve_detail = $result['doctor_approve_detail'];


                                    $self_assessment_result = $this->db
                                    ->query(" SELECT * FROM `cv_self_assessment` WHERE self_assessment_id = '$self_assessment_id'")
                                    ->result_array();

                                    // print_r( $self_assessment_result);

                                    $nurse_comment_id = $self_assessment_result[0]['nurse_comment_id'];

                                    $nurese_approve_result = $this->db
                                    ->query(" SELECT * FROM `cv_nurse_comment` WHERE nurse_comment_id = '$nurse_comment_id'")
                                    ->result_array();
                                
                                    // print_r( $nurese_approve_result);


                                    $data = array(
                                        'doctor_approve_id' => NULL,
                                        'user_ad_code' => $user_ad_code,
                                        'doctor_approve_by_id' => $doctor_approve_by_id,
                                        'doctor_approve_result' =>  "1",
                                        'doctor_approve_datetime_create' => date("Y-m-d h:i:s"),
                                        'doctor_approve_detail' =>  $doctor_approve_detail,
                                        'doctor_approve_status_wfh' => $nurese_approve_result[0]['nurse_approve_status_wfh'],
                                        'doctor_approve_wfh_date_start' => $nurese_approve_result[0]['nurse_approve_wfh_date_start'],
                                        'doctor_approve_wfh_date_end' => $nurese_approve_result[0]['nurse_approve_wfh_date_end'],
                            
                                    );
    
                                    $this->db->insert('cv_doctor_approve', $data);
                                    if(($this->db->affected_rows() != 1) ? false : true){
                                   
                                        $doctor_approve_result = $this->db
                                        ->query("SELECT * FROM `cv_doctor_approve` WHERE `user_ad_code` = '$user_ad_code' ORDER BY `doctor_approve_id` DESC LIMIT 1")
                                        ->result_array();

                                        // print_r( $doctor_approve_result);
                                        
                                  

                                        $this->db->trans_begin();
                                        $this->db->where('self_assessment_id', $self_assessment_id)
                                        ->set(
                                            array( 
        
                                                'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
                                                'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
                                                'doctor_approve_status_wfh' => $nurese_approve_result[0]['nurse_approve_status_wfh'],
        
                                                )) ->update('cv_self_assessment');
        
                
                                        if ($this->db->trans_status() === false) {
                                            $this->db->trans_rollback();
                                            // return  array(  'status' => "false" , 'result' => "update cv_self_assessment_false" );
                                        } else {
                                            $this->db->trans_commit();

                                                if($doctor_approve_result[0]['doctor_approve_status_wfh'] == "2"){

                                                    $data = array(
                                                        'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
                                                        'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
                                                        'doctor_approve_status_wfh' => $doctor_approve_result[0]['doctor_approve_status_wfh'],
                                                        'self_assessment_sum_result' => "1"
                                                    );

                                                }else{
                                                    $data = array(
                                                        'doctor_approve_result_check' => $doctor_approve_result[0]['doctor_approve_result'],
                                                        'doctor_approve_id' => $doctor_approve_result[0]['doctor_approve_id'],
                                                        'doctor_approve_status_wfh' => $doctor_approve_result[0]['doctor_approve_status_wfh'],
                
                                                    );

                                                    // insert tp table WFH

                                                    $data_insert = array(
                                                        'WFH_EN_NUMBER' => $user_ad_code ,
                                                        'WFH_ST_DATE'   => $doctor_approve_result[0]['doctor_approve_wfh_date_start'],
                                                        'WFH_ED_DATE'   => $doctor_approve_result[0]['doctor_approve_wfh_date_end'],
                                                        'WFH_REMARK'    => 'FROM DOCTOR - Covid19',
                                                        'APPR_BY'	    => 'DOCTOR',
                                                        'create_by'     => $doctor_approve_by_id,
                                                        'create_date'   => date("Y-m-d h:i:s")
                                                    );

                                                    $this->Insert_user_WFH($data_insert);
                                                }

                                                $this->db->trans_begin();
                                                $this->db->where('user_ad_code', $user_ad_code)->set($data)->update('cv_user_latest_status');
                
                                                if ($this->db->trans_status() === false) {
                                                    $this->db->trans_rollback();
                                                    // return  array(  'status' => "false" , 'result' => "update cv_user_latest_status" );
                                             
                                                } else {
                                                    $this->db->trans_commit();
                                                        // return array(  'status' => "true" , 'result' => "doctor_approve_wfh true");

                                                }
                                        }
                            
                                    }
    
                            }

                      
                                return array(  'status' => "true" , 'result' => "doctor_approve_wfh true");
                       
                           

                        }


                                   


                }else{
                    return array(  'status' => "false" , 'result' => "request self_assessment_id");
                }
            }else{
                return array(  'status' => "false" , 'result' => "request doctor_approve_detail");
            }
        }else{
            return array(  'status' => "false" , 'result' => "request doctor_approve_by_id");
        }

        
    }
    
    public function Insert_user_WFH( $data ){

       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://change.toat.co.th/timeatt/index.php/api/chk_inout/insertWFH');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        $result = curl_exec($ch);
        curl_close($ch);
        return  $result;
    }
   
} 