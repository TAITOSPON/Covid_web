<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_Covid_Nurse extends CI_Model
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

        $user = $this->db->query("SELECT DISTINCT user_ad_dept_code FROM `cv_user_latest_status` INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code")->result_array();


        $data = array();

        for($i=0; $i < sizeof($user); $i++){

            $user_ad_dept_code = $user[$i]['user_ad_dept_code'];
            $result_dept = array(json_decode($this->Get_id_chief_by_user_ad_dapt_code($user_ad_dept_code), true));
            $data[$i] = $result_dept[0]['DEPT_CODE'];   
        }

        $result_dept_name['DEPT_CODE'] = array_unique($data);
       
        $data = array();
        for($i=0; $i < sizeof($user); $i++){

            $user_ad_dept_code = $user[$i]['user_ad_dept_code'];
            $result_dept = array(json_decode($this->Get_id_chief_by_user_ad_dapt_code($user_ad_dept_code), true));
            $data[$i] = $result_dept[0]['DEPT_NAME'];   
        }

        $result_dept_name['DEPT_NAME'] = array_unique($data);


        $data_DEPT_NAME = array();
        $data_DEPT_CODE = array();
        foreach ($result_dept_name['DEPT_NAME'] as $value) { array_push( $data_DEPT_NAME, $value); }
        foreach ($result_dept_name['DEPT_CODE'] as $value) { array_push( $data_DEPT_CODE, $value);  }
 
        $data_DEPT = array();

        for($i=0; $i < sizeof($data_DEPT_CODE); $i++){
                
            $data_DEPT[$i]['DEPT_CODE'] = $data_DEPT_CODE[$i];   
            $data_DEPT[$i]['DEPT_NAME'] = $data_DEPT_NAME[$i];   
          
        }
        return array(  'status' => "true" , 'result' => $data_DEPT);
        // print_r($data_DEPT); exit();
        
    }


    public function Nurse_Get_All_User_by_dept_code($result){

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
                            WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'")
                        ->result_array();
                            // print_r($this->db->last_query());  exit();
                    // print_r($); exit();
                    for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                        $user_ad_code = $user_result[$index_user_result]['user_ad_code'];
    
                
                        $user_self_assessment_result = $this->db
                        ->query("SELECT * FROM `cv_self_assessment`
                            WHERE `user_ad_code` =  '$user_ad_code'
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

                            // print_r( $self_assessment_detail );
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
    
                    $result_dept[$i]["RESULT_ALL_USER"] = $user_result ;
    
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
                    WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'")
                ->result_array();

                for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                    $user_ad_code = $user_result[$index_user_result]['user_ad_code'];

                   
                    $user_self_assessment_result= $this->db  
                    ->query("SELECT * FROM `cv_self_assessment`
                        WHERE `user_ad_code` =  '$user_ad_code'
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

                        // print_r( $self_assessment_detail );
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
          
            return array(  'status' => "true" , 'result' => $result_dept);
        }else{
            return array(  'status' => "false" , 'result' => "request dept_code");
        }

    }

    public function Nurse_Comment_user_with_self_assessment_id($result){
        if(isset($result['nurse_comment_ad_id'])){
            if(isset($result['user_ad_code'])){
                if(isset($result['nurse_comment_text'])){
                    if(isset($result['self_assessment_id'])){
                        if(isset($result['nurse_approve_status_wfh'])){
                            if(isset($result['nurse_approve_wfh_date_start'])){
                                if(isset($result['nurse_approve_wfh_date_end'])){


                        $nurse_comment_ad_id    = $result['nurse_comment_ad_id'];
                        $user_ad_code           = $result['user_ad_code'];
                        $nurse_comment_text     = $result['nurse_comment_text'];
                        $self_assessment_id     = $result['self_assessment_id'];

                        $nurse_approve_status_wfh = $result['nurse_approve_status_wfh'];
                        $nurse_approve_wfh_date_start = $result['nurse_approve_wfh_date_start'];
                        $nurse_approve_wfh_date_end = $result['nurse_approve_wfh_date_end'];
                        // INSERT INTO `cv_nurse_comment` (`nurse_comment_id`, `nurse_comment_ad_id`, `nurse_comment_date_time`, `nurse_comment_text`, `user_ad_code`) VALUES (NULL, '003599', current_timestamp(), 'fghsfghsfghxfgh', '003599');

                        $data = array(
                            'nurse_comment_id' => NULL,
                            'nurse_comment_ad_id' => $nurse_comment_ad_id,
                            'nurse_comment_date_time' => date("Y-m-d h:i:s"),
                            'nurse_comment_text' =>  $nurse_comment_text,
                            'nurse_approve_status_wfh' => $nurse_approve_status_wfh,
                            'nurse_approve_wfh_date_start' => $nurse_approve_wfh_date_start,
                            'nurse_approve_wfh_date_end' => $nurse_approve_wfh_date_end,
                            'user_ad_code' => $user_ad_code
                          
                        );

                        $this->db->insert('cv_nurse_comment', $data);

                        if(($this->db->affected_rows() != 1) ? false : true){

                            $query = $this->db
                            ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_ad_id` = '$nurse_comment_ad_id' ORDER BY `nurse_comment_id` DESC LIMIT 1")
                            ->result_array();
                            //  return array(  'status' => "false" , 'result' => $query);
                        }

                        $data_update = array( 'nurse_comment_id' => $query[0]['nurse_comment_id']) ;

                        $this->db->trans_begin();
                        $this->db->where('self_assessment_id', $self_assessment_id)->set($data_update)->update('cv_self_assessment');
                            
                        if ($this->db->trans_status() === false) {
                            $this->db->trans_rollback();
                            return array(  'status' => "false" , 'result' => "trans_rollback");
                        } else {
                            $this->db->trans_commit();
                         
                            return array(  'status' => "true" , 'result' => "Nurse_Comment_True");
                        }




                                }else{
                                    return array(  'status' => "false" , 'result' => "request nurse_approve_wfh_date_end");
                                }
                            }else{
                                return array(  'status' => "false" , 'result' => "request nurse_approve_wfh_date_start");
                            }
                        }else{
                            return array(  'status' => "false" , 'result' => "request nurse_approve_status_wfh");
                        }
                    }else{
                        return array(  'status' => "false" , 'result' => "request self_assessment_id");
                    }
                }else{
                    return array(  'status' => "false" , 'result' => "request nurse_comment_text");
                }
            }else{
                return array(  'status' => "false" , 'result' => "request user_ad_code");
            }
        }else{
            return array(  'status' => "false" , 'result' => "request nurse_comment_ad_id");
        }


    }

}