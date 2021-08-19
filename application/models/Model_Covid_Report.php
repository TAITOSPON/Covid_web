<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_Covid_Report extends CI_Model {    
    
    
    public function Get_Sum_Status(){

        $query_all = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status`')
            // ->query('SELECT count(*) FROM `cv_user`')
            ->result_array();

        $query_green = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "1" and self_assessment_status_covid IS NULL')
            // ->query('SELECT count(*) FROM `cv_user`')
            ->result_array();

        // $blue = $this->db
        //     ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "2" and doctor_approve_status_wfh !=1')
        //     ->result_array();

        // $yellow = $this->db
        //     ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "3" and doctor_approve_status_wfh !=1')
        //     ->result_array();

        // $orange = $this->db
        //     ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "4" and doctor_approve_status_wfh !=1')
        //     ->result_array();

        // $red = $this->db
        //     ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "5" and doctor_approve_status_wfh !=1')
        //     ->result_array();
            
        // $risk =  ((int)$blue)+((int)$yellow)+((int)$orange)+((int)$red);

        $risk = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result != "1" and doctor_approve_status_wfh !=1 and self_assessment_status_covid IS NULL')
            ->result_array();


        
        
        $yellow_user_count = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE  doctor_approve_status_wfh =1 and self_assessment_status_covid IS NULL')
            ->result_array();

        $have_covid_user_count = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE  self_assessment_status_covid = 1')
            ->result_array();
        
        $get_well_covid_user_count = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE  self_assessment_status_covid = 0')
            ->result_array();

      
        $result_count = array( 
            'all_user_count' => $query_all[0]['count(*)'],
            'green_user_count' => $query_green[0]['count(*)'],
            'yellow_user_count' => $yellow_user_count[0]['count(*)'],
            'red_user_count' => $risk[0]['count(*)'],  
            'have_covid_count' => $have_covid_user_count[0]['count(*)'],
            'get_well_covid_count' => $get_well_covid_user_count[0]['count(*)'],
        );     



        $result = array( 
            'status' => "true",
            'result' => $result_count
             
        );     
        

        return $result;
    }


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


    public function Get_All_Dept_Name($query){

        $user = $this->db->query($query)->result_array();

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
                
            // $data_DEPT[$i]['DEPT_CODE'] = $data_DEPT_CODE[$i];   
            // $data_DEPT[$i]['DEPT_NAME'] = $data_DEPT_NAME[$i];   

            $data_DEPT[$i]['DEPT_CODE'] = $data_DEPT_CODE[$i];   
            $data_DEPT[$i]['DEPT_NAME'] = $data_DEPT_CODE[$i];  
          
        }
        return array(  'status' => "true" , 'result' => $data_DEPT);
        // print_r($data_DEPT); exit();
        
    }



    public function Get_All_User_Quarantine($data ){

        if(isset($data['dept_code'])){

            $dept_code = $data['dept_code'];
            $result_count = array( 

                'user_quarantine_detial' => $this->Get_Sum_Status_Yellow_Detail($dept_code),
                
            );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     

        }else{

            $result_count = array( 

                'user_quarantine_detial' => $this->Get_Sum_Status_Yellow_Detail("0000"),
                
            );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     

        }
       
        

        return $result;
    }

    public function Get_Sum_Status_Detail($data){

        if(isset($data['dept_code'])){
            $result_count = array( 

                'yellow_user_detail' => $this->Get_Sum_Status_Yellow_Detail($data['dept_code']),
                'red_user_detail' => $this->Get_Sum_Status_Red_Detail($data['dept_code']),
                
            );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;

        }else{

            $result_count = array( 

                'yellow_user_detail' => $this->Get_Sum_Status_Yellow_Detail("0000"),
                'red_user_detail' => $this->Get_Sum_Status_Red_Detail("0000"),
                
            );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;
            
        }
       

    }


    // yellow = กักตัว
    public function Get_Sum_Status_Yellow_Detail($dept_code){

            if($dept_code == "0000"){

                $dept_query_yellow = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE  `cv_user_latest_status`.doctor_approve_status_wfh = 1";
            
            }else{

                $dept_query_yellow = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE  `cv_user_latest_status`.doctor_approve_status_wfh = 1
                AND `cv_user`.`user_ad_dept_code` LIKE '$dept_code%' ";
            }
        


            $result_dept = $this->Get_All_Dept_Name($dept_query_yellow);
            $result_dept =  $result_dept['result'];
        
            for($i=0; $i < sizeof($result_dept); $i++){

                $dept_code = $result_dept[$i]['DEPT_CODE'] ;
                $dept_code = substr($dept_code, 0 ,-4); 

            
                $user_result = $this->db
                    ->query("SELECT * FROM `cv_user_latest_status`
                        INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                        WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                        AND `cv_user_latest_status`.`doctor_approve_status_wfh` =1
                
                        ")
                    ->result_array();

                for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                
                    $user_ad_code = $user_result[$index_user_result]['user_ad_code'];

            
                    $user_self_assessment_result = $this->db
                    ->query("SELECT * FROM `cv_self_assessment`
                        WHERE `user_ad_code` =  '$user_ad_code'
                        AND `doctor_approve_id` != '0'
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

                $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
            }

            return $result_dept;
    }

    public function Get_Sum_Status_Red_Detail($dept_code){
           
            if($dept_code == "0000"){

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_sum_result != 1 
                AND `cv_user_latest_status`.doctor_approve_status_wfh !=1 ";

            }else{

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_sum_result != 1 
                AND `cv_user_latest_status`.doctor_approve_status_wfh !=1 
                AND `cv_user`.`user_ad_dept_code` LIKE '$dept_code%' ";
            }
     
                            

            $result_dept = $this->Get_All_Dept_Name($dept_query_red);
            $result_dept =  $result_dept['result'];

            for($i=0; $i < sizeof($result_dept); $i++){

                $dept_code = $result_dept[$i]['DEPT_CODE'] ;
                $dept_code = substr($dept_code, 0 ,-4); 

            
                $user_result = $this->db
                    ->query("SELECT * FROM `cv_user_latest_status`
                        INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                        WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                        AND `cv_user_latest_status`.self_assessment_sum_result != 1 
                        AND `cv_user_latest_status`.doctor_approve_status_wfh !=1 
                
                        ")
                    ->result_array();

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

                $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
            }


        return $result_dept;
    }

   

   
    public function Get_All_user_have_covid($data){
        if(isset($data['dept_code'])){
            $result_count = array(  'user_have_covid' => $this->Get_user_have_covid_Detail($data['dept_code']), );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;

        }else{

            $result_count = array(   'user_have_covid' => $this->Get_user_have_covid_Detail("0000"),  );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;
            
        }
       
    }

    public function Get_user_have_covid_Detail($dept_code){
           
            if($dept_code == "0000"){

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_status_covid = 1";

            }else{

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_status_covid = 1
                AND `cv_user`.`user_ad_dept_code` LIKE '$dept_code%' ";
            }
     
                            

            $result_dept = $this->Get_All_Dept_Name($dept_query_red);
            $result_dept =  $result_dept['result'];

            for($i=0; $i < sizeof($result_dept); $i++){

                $dept_code = $result_dept[$i]['DEPT_CODE'] ;
                $dept_code = substr($dept_code, 0 ,-4); 

            
                $user_result = $this->db
                    ->query("SELECT * FROM `cv_user_latest_status`
                        INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                        WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                        AND `cv_user_latest_status`.self_assessment_status_covid = 1
                        ")
                    ->result_array();

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

                $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
            }


        return $result_dept;
    }



    public function Get_dept_heve_covid($data){
        if(isset($data['dept_code'])){
            $result_count = array( 
                    'Get_dept_heve_covid_top_five' => $this->Get_dept_heve_covid_top_five($data['dept_code']),
                    'Get_dept_get_well_covid_top_five' => $this->Get_dept_get_well_covid_top_five($data['dept_code']),
                );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;

        }else{

            $result_count = array(   
                'Get_dept_heve_covid_top_five' => $this->Get_dept_heve_covid_top_five("0000"),
                'Get_dept_get_well_covid_top_five' => $this->Get_dept_get_well_covid_top_five("0000"),
            );     
    
            $result = array( 
                'status' => "true",
                'result' => $result_count
                 
            );     
            
            return $result;
            
        }
       
    }


    public function Get_dept_heve_covid_top_five($dept_code){
            if($dept_code == "0000"){

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_status_covid = 1 LIMIT 5";

            }else{

                $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
                FROM `cv_user_latest_status` 
                INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
                WHERE `cv_user_latest_status`.self_assessment_status_covid = 1
                AND `cv_user`.`user_ad_dept_code` LIKE '$dept_code%'LIMIT 5 ";
            }
     
                            

            $result_dept = $this->Get_All_Dept_Name($dept_query_red);
            $result_dept =  $result_dept['result'];

            for($i=0; $i < sizeof($result_dept); $i++){

                $dept_code = $result_dept[$i]['DEPT_CODE'] ;
                $dept_code = substr($dept_code, 0 ,-4); 

            
                $user_result = $this->db
                    ->query("SELECT * FROM `cv_user_latest_status`
                        INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                        WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                        AND `cv_user_latest_status`.self_assessment_status_covid = 1
                        ")
                    ->result_array();

            
                
                $result_dept[$i]["RESULT_ALL_USER"] = $user_result ;

                $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
                $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
            }


        return $result_dept;
    }


    public function Get_dept_get_well_covid_top_five($dept_code){
        if($dept_code == "0000"){

            $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
            FROM `cv_user_latest_status` 
            INNER JOIN `cv_user` 
            ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
            WHERE `cv_user_latest_status`.self_assessment_status_covid = 0 LIMIT 5";

        }else{

            $dept_query_red = "SELECT DISTINCT user_ad_dept_code 
            FROM `cv_user_latest_status` 
            INNER JOIN `cv_user` 
            ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code   
            WHERE `cv_user_latest_status`.self_assessment_status_covid = 0
            AND `cv_user`.`user_ad_dept_code` LIKE '$dept_code%'LIMIT 5 ";
        }
 
                        

        $result_dept = $this->Get_All_Dept_Name($dept_query_red);
        $result_dept =  $result_dept['result'];

        for($i=0; $i < sizeof($result_dept); $i++){

            $dept_code = $result_dept[$i]['DEPT_CODE'] ;
            $dept_code = substr($dept_code, 0 ,-4); 

        
            $user_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`
                    INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_dept_code  LIKE '$dept_code%'
                    AND `cv_user_latest_status`.self_assessment_status_covid = 0
                    ")
                ->result_array();

        
            
            $result_dept[$i]["RESULT_ALL_USER"] = $user_result ;

            $result_dept_ = array(json_decode($this->Get_id_chief_by_dapt_code($dept_code), true));
            $result_dept[$i]['DEPT_NAME'] =  $result_dept_[0]['DEPT_NAME'];
        }


        return $result_dept;
    }


}
