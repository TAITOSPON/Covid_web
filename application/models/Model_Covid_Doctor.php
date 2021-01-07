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
                            AND `cv_user_latest_status`.chief_approve_result_check = 2")
                        ->result_array();
                            // print_r($this->db->last_query());  exit();
                    // print_r($); exit();
                    for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                        $user_ad_code = $user_result[$index_user_result]['user_ad_code'];
    
                        $user_result[$index_user_result]['user_self_assessment_result'] = $this->db  
                        ->query("SELECT * FROM `cv_self_assessment`
                            WHERE `user_ad_code` =  '$user_ad_code'
                            ORDER BY `self_assessment_id` DESC LIMIT 100")
                        ->result_array();
    
                    }
    
                    $result_dept[$i]["RESULT_ALL_USER"] =  $user_result ;
    
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
                    AND `cv_user_latest_status`.chief_approve_result_check = 2")
                ->result_array();

                for($index_user_result=0; $index_user_result < sizeof($user_result); $index_user_result++){
                    
                    $user_ad_code = $user_result[$index_user_result]['user_ad_code'];

                    $user_result[$index_user_result]['user_self_assessment_result'] = $this->db  
                    ->query("SELECT * FROM `cv_self_assessment`
                        WHERE `user_ad_code` =  '$user_ad_code'
                        ORDER BY `self_assessment_id` DESC LIMIT 100")
                    ->result_array();

                }

                // return array(  'status' => "true" , 'result' => $user_result);
                $result_dept[0]['RESULT_ALL_USER'] = $user_result;
              
            }
          
            return array(  'status' => "true" , 'result' => $result_dept);
        }else{
            return array(  'status' => "false" , 'result' => "request dept_code");
        }

    }

   
} 