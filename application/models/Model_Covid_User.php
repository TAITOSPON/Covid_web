<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_Covid_User extends CI_Model
{       
    
    public function Get_user_detail($user_ad_code){
   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://webhook.toat.co.th/linebot/web/index.php/api/Api_Member/Member_User_Profile_withAD');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( array('user_ad_code' => $user_ad_code)) );
        $result = curl_exec($ch);
        curl_close($ch);
        return  $result;

    }


    public function Get_id_chief_by_dapt_code($user_ad_code){

        $user_detail = json_decode($this->Get_user_detail($user_ad_code), true);

        $user_ad_dept_code = $user_detail['result']['personal']['DepartmentCode'] ;
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


    public function date_thai_to_eng($date){
        $month_name = [
            'มกราคม',
            'กุมภาพันธ์',
            'มีนาคม',
            'เมษายน',
            'พฤษภาคม',
            'มิถุนายน',
            'กรกฎาคม',
            'สิงหาคม',
            'กันยายน',
            'ตุลาคม',
            'พฤศจิกายน',
            'ธันวาคม',
        ];
        $month_num = [
            '01',
            '02',
            '03',
            '04',
            '05',
            '06',
            '07',
            '08',
            '09',
            '10',
            '11',
            '12',
        ];

       
        $date = explode(" ",str_replace($month_name, $month_num, $date));
        $date = ((int)$date[2]-543)."-".$date[1]."-".$date[0];
        return $date;
    }


    public function Check_user_member_type($result){
        if(isset($result['user_ad_code'])){
            $user_ad_code = $result['user_ad_code'];

            $member_rule_result = $this->db
                ->query("SELECT * FROM `cv_member_rule` WHERE user_ad_code ='$user_ad_code'")
                ->result_array();
            
            if(sizeof($member_rule_result) != 0){
                return array(  'status' => "true" , 'result' => array(array('member_type' => $member_rule_result[0]['member_type'])));
            }else{
                return array(  'status' => "true" , 'result' => array(array('member_type' => "user")));
            }

           
      
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
    }

    public function Get_List_Underline_by_user_ad_boss($result){

        if(isset($result['user_ad_code'])){
            $user_ad_code = $result['user_ad_code'];

            $member_boss = $this->db->query("SELECT * FROM `cv_member_rule` WHERE member_ad_boss = $user_ad_code")
            ->result_array();  

            if(sizeof($member_boss) != 0){

                return  array(  'status' => "true" , 'result' => array(  'status_boss' => "true" , 'result' => $member_boss ) );
            }else{
                return  array(  'status' => "true" , 'result' => array(  'status_boss' => "false" , 'result' => "not_boss" ) ); 
            }

        
        
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }




    }

    public function Get_Boss_by_ad_Director($user_ad_code){

        $member_boss = $this->db->query("SELECT * FROM `cv_member_rule` WHERE user_ad_code = $user_ad_code")
        ->result_array();  

        return $member_boss;
    }

    public function Set_user_ad($result) {

        $status_data = $this->CheckData($result);
        // echo json_encode($status_data,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
        if($status_data["status"]=="true"){


            $status = array( 
                'status' => "false" ,
                'result' => "error"
            );

            $user_ad_code = $result['user_ad_code'];
            $data = $this->db->query("SELECT COUNT(user_ad_code)FROM cv_user WHERE user_ad_code = '$user_ad_code'")->result_array();
            
            $count = $data[0]["COUNT(user_ad_code)"];

            if($count == "0"){
        
                $user_detail = json_decode($this->Get_user_detail($user_ad_code), true);
                // echo $result['result'];
        
                $data = array(
                        'user_ad_code' => $user_ad_code, 
                        'user_ad_name' => $user_detail['result']['personal']['PersonalName'],
                        'user_ad_dept_code' => $user_detail['result']['personal']['DepartmentCode'],
                        'user_ad_dept_name' => $user_detail['result']['personal']['Department'],
                        'user_ad_birth_date' => $this->date_thai_to_eng($user_detail['result']['personal']['BirthDate']),
                        'user_ad_sex' => $user_detail['result']['personal']['Sex'],
                        'user_ad_tel' => $result['user_ad_tel']
                    );
                
                $this->db->insert('cv_user', $data);
                return $this->Set_self_assessment($result); 
                // return $this->Get_User_case($result);

            }else if($count == "1"){

                
                $user_detail = json_decode($this->Get_user_detail($user_ad_code), true);
                $data = array(
                    'user_ad_code' => $user_ad_code, 
                    'user_ad_name' => $user_detail['result']['personal']['PersonalName'],
                    'user_ad_dept_code' => $user_detail['result']['personal']['DepartmentCode'],
                    'user_ad_dept_name' => $user_detail['result']['personal']['Department'],
                    'user_ad_birth_date' => $this->date_thai_to_eng($user_detail['result']['personal']['BirthDate']),
                    'user_ad_sex' => $user_detail['result']['personal']['Sex'],
                    'user_ad_tel' => $result['user_ad_tel']
                );

                $this->db->trans_begin();
                $this->db->where('user_ad_code', $user_ad_code)->set($data)->update('cv_user');
                    
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return $status;
                } else {
                    $this->db->trans_commit();
                    return $this->Set_self_assessment($result);
                    // return $this->Get_User_case($result);
                   
                }
            }
        }else{
            return $status_data ;  
        }
                                
 
    }


    public function CheckData($result){

        if( isset( $result['user_ad_code'] ) ){
            if( isset( $result['user_ad_tel'] ) ){
                if( isset( $result['user_ad_create_by'] ) ){
                    if( isset( $result['self_assessment_F'] ) ){
                        if( isset( $result['self_assessment_R'] ) ){
                            if( isset( $result['self_assessment_HR1'] ) ){
                                if( isset( $result['self_assessment_HR2'] ) ){
                                    if( isset( $result['self_assessment_HR3'] ) ){
                                        if( isset( $result['self_assessment_HR4'] ) ){
                                            if( isset( $result['self_assessment_LR'] ) ){
                                                if( isset( $result['self_assessment_P'] ) ){
                                                    if( isset( $result['self_assessment_HCW'] ) ){
                                                        if( isset( $result['self_assessment_C'] ) ){
                                                            if( isset( $result['self_assessment_result'] ) ){
                                                                if( isset( $result['colorNormal'] ) ){    
                                                                    if( isset( $result['self_assessment_result_specific'] ) ){
                                                                        if( isset( $result['colorSpecific'] ) ){
                                                                            return array( 'status' => "true" , 'result' => "ok" );
                                                                        }else{
                                                                            return  array(  'status' => "false" , 'result' => "request colorSpecific" );
                                                                        }
                                                                    }else{
                                                                        return  array(  'status' => "false" , 'result' => "request self_assessment_result_specific" );
                                                                    }
                                                                } return  array(  'status' => "false" , 'result' => "request colorNormal" );
                                                            }else{
                                                                return  array(  'status' => "false" , 'result' => "request self_assessment_result" );
                                                            }
                                                        }else{
                                                            return  array(  'status' => "false" , 'result' => "request self_assessment_C" );
                                                        }
                                                    }else{
                                                        return  array(  'status' => "false" , 'result' => "request self_assessment_HCW" );
                                                    }
                                                }else{
                                                    return  array(  'status' => "false" , 'result' => "request self_assessment_P" );
                                                }
                                            }else{
                                                return  array(  'status' => "false" , 'result' => "request self_assessment_LR" );
                                            }
                                        }else{
                                            return  array(  'status' => "false" , 'result' => "request self_assessment_HR4" );
                                        }
                                    }else{
                                        return  array(  'status' => "false" , 'result' => "request self_assessment_HR3" );
                                    }
                                }else{
                                    return  array(  'status' => "false" , 'result' => "request self_assessment_HR2" );
                                }
                            }else{
                                return  array(  'status' => "false" , 'result' => "request self_assessment_HR1" );
                            }
                        }else{
                            return  array(  'status' => "false" , 'result' => "request self_assessment_R" );
                        }
                    }else{
                        return  array(  'status' => "false" , 'result' => "request self_assessment_F" );
                    }
                }else{
                    return  array(  'status' => "false" , 'result' => "request user_ad_create_by" );
                }               
            }else{
                return  array(  'status' => "false" , 'result' => "request user_ad_tel" );
            }
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }

    }
   
    public function Get_User_case($result,$self_assessment_result){

        if($result['self_assessment_result_specific'] == "1"){
            $case = array( 
                'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
                'normal_case' => $this->db
                    ->where('self_assessment_criterion_id',$result["self_assessment_result"])
                    ->get('cv_self_assessment_criterion')
                    ->result_array() ,
                'specific_case' => array('status_specific_case'=> "true" ,'result_specific_case' => $this->db
                    ->where('self_assessment_criterion_id',"12")
                    ->get('cv_self_assessment_criterion')
                    ->result_array()
                 ) 
            );     
            $status = array( 
                'status' => "true" ,
                'result' => $case
            );
    
            return  $status;    
        }else{
            $case = array( 
                'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
                'normal_case' => $this->db
                    ->where('self_assessment_criterion_id',$result["self_assessment_result"])
                    ->get('cv_self_assessment_criterion')
                    ->result_array() ,
                'specific_case' =>  array('status_specific_case'=> "false" ,'result_specific_case' =>$this->db
                ->where('self_assessment_criterion_id',"11")
                ->get('cv_self_assessment_criterion')
                ->result_array())
            );     
            $status = array( 
                'status' => "true" ,
                'result' => $case
            );
    
            return  $status;    
        }
        
     
       
    }

   

    
    public function Set_self_assessment($result) {
   
        $data = array(
            'self_assessment_id' => NULL,
            'user_ad_code' => $result['user_ad_code'],
            'user_ad_create_by' => $result['user_ad_create_by'],
            'self_assessment_F' =>  $result['self_assessment_F'],
            'self_assessment_R' =>  $result['self_assessment_R'], 
            'self_assessment_HR1' =>  $result['self_assessment_HR1'], 
            'self_assessment_HR2' =>  $result['self_assessment_HR2'], 
            'self_assessment_HR3' =>  $result['self_assessment_HR3'],
            'self_assessment_HR4' =>  $result['self_assessment_HR4'], 
            'self_assessment_LR' =>  $result['self_assessment_LR'], 
            'self_assessment_P' =>  $result['self_assessment_P'], 
            'self_assessment_HCW' =>  $result['self_assessment_HCW'], 
            'self_assessment_C' =>  $result['self_assessment_C'], 
            'self_assessment_result' =>  $result['self_assessment_result'], 
            'self_assessment_colorNormal' => $result['colorNormal'], 
            'self_assessment_result_specific' =>  $result['self_assessment_result_specific'], 
            'self_assessment_colorSpecific' => $result['colorSpecific'], 
            'self_assessment_date_time' =>  date("Y-m-d h:i:s"),
            'chief_approve_result_check' => 0
        );

        if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
            $data['self_assessment_sum_result'] = "3" ;
            $data['self_assessment_sum_color'] = "danger" ;
            // alert to boss
        }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
            $data['self_assessment_sum_result'] = "2" ;
            $data['self_assessment_sum_color'] = "warning" ;
            // alert to boss
        }else if ($result['colorNormal'] == "success" || $result['colorSpecific'] == "success"){
            $data['self_assessment_sum_result'] = "1" ;
            $data['self_assessment_sum_color'] = "success" ;
        }

        $this->db->insert('cv_self_assessment', $data);
        if(($this->db->affected_rows() != 1) ? false : true){

            $user_ad_code = $result['user_ad_code'];
            $self_assessment_result = $this->db
            ->query("SELECT * FROM `cv_self_assessment` 
                WHERE user_ad_code = '$user_ad_code'
                ORDER BY `self_assessment_id`  DESC LIMIT 1")
            ->result_array();
            
            $this->Update_User_latest_status($result,$self_assessment_result);
            return $this->Get_User_case($result,$self_assessment_result);
         
        }

    
     
    }




    public function Set_self_assessment_detail($result){


        if(isset($result['user_ad_code'])){
            if(isset($result['self_assessment_id'])){
                if(isset($result['self_assessment_detail_result'])){

                    // INSERT INTO `cv_self_assessment_detail` (`self_assessment_detail_id`, `self_assessment_detail_result`, `self_assessment_detail_date_time`) VALUES (NULL, 'se', current_timestamp());
                   
                    $user_ad_code = $result['user_ad_code'];
                    $self_assessment_id = $result['self_assessment_id'];

                    $data = array(
                        'self_assessment_detail_id' => NULL,
                        'self_assessment_id' => $self_assessment_id,
                        'user_ad_code' => $user_ad_code,
                        'self_assessment_detail_result' => json_encode($result['self_assessment_detail_result']),
                        'self_assessment_detail_date_time' => date("Y-m-d h:i:s")
                    );

                    $this->db->insert('cv_self_assessment_detail', $data);
                    if(($this->db->affected_rows() != 1) ? false : true){

                        // $self_assessment_id = $result['self_assessment_id'];
                        $result_self_assessment_detail = $this->db
                        ->query("SELECT * FROM `cv_self_assessment_detail` 
                            WHERE user_ad_code ='$user_ad_code' 
                            ORDER BY `self_assessment_detail_id` DESC LIMIT 1")
                        ->result_array();

                        // print_r($result_self_assessment_detail[0]['self_assessment_detail_id']); exit();

                        $data = array(
                            'self_assessment_detail_id' =>  $result_self_assessment_detail[0]['self_assessment_detail_id'],
                        );


                        $this->db->trans_begin();
                        $this->db->where('self_assessment_id', $result['self_assessment_id'])->set($data)->update('cv_self_assessment');
                            
                        if ($this->db->trans_status() === false) {
                            $this->db->trans_rollback();
                            return  array(  'status' => "false" , 'result' => "trans_rollback" );
                        } else {
                            $this->db->trans_commit();
            
                            return  array(  'status' => "true" , 'result' => "Update Form 2 True" );
                        
                        }

                    }

                    
                }else{
                    return  array(  'status' => "false" , 'result' => "request self_assessment_detail_result" );
                }

            }else{
                return  array(  'status' => "false" , 'result' => "request self_assessment_id" );
            }
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }


        // $data = array();
        // $data['1_Have_Fever'] = array('status' => "1");

        // $data['2_Have_symptoms'] = array(  'status'=>"1",'detail'=>"textdetaillll");
        // $data['3_Physician'] = array('status' => "1");
        // $data['4_Close_up_people_risk'] = array('status' => "1");
        // $data['5_Close_up_people_colds'] = array('status' => "1");
        // $data['6_Close_up_people_covid'] = array('status' => "1");

        // $data['7_Transport'] =  array(
        //     'status_one' => "1",
        //     'status_two' => "1",
        //     'status_detail' => array('status'=>"1",'detail'=>"textdetaillll"),
        // );
        // $data['8_Protect'] = array(
        //     'status_one' => "1",
        //     'status_two' => "1",      
        // );

        // $data['9_Protect'] = array( 'status' => "1", );

        // $data['10_Activity_risk'] = array(
        //     'status_one' => "1",
        //     'status_two' => "1",
        //     'status_three' => "1",
        //     'status_four' => "1",
        //     'status_detail' => array('status'=>"1",'detail'=>"textdetaillll"),
        // );
        

        // $data['11_Time_line'] = array(
        //     'status_one' => "1",
        //     'status_two' => "1",
        //     'status_three' => "1",
           
        // );

        // $result = array(
        //     'user_ad_code' => "003599",
        //     'self_assessment_id' => "264",
        //     'self_assessment_detail_result' => $data

        // );

        // return $result;



    }


    public function Check_self_assessment_latest_with_ad_code($result){

        if(isset($result['user_ad_code'])){
            $user_ad_code = $result['user_ad_code'];

            $self_assessment_result = $this->db
            ->query("SELECT * FROM `cv_self_assessment`  
                WHERE user_ad_code ='$user_ad_code'
                ORDER BY `cv_self_assessment`.`self_assessment_id`  DESC
                LIMIT 1")
            ->result_array();

            if(sizeof($self_assessment_result) != 0){
                // print_r(sizeof($self_assessment_result)); exit();
      
                $latest_status_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status` WHERE user_ad_code = '$user_ad_code'")
                ->result_array();

                if(sizeof($latest_status_result) != 0){
            
        
                    if($latest_status_result[0]['self_assessment_sum_result'] == "2" || $latest_status_result[0]['self_assessment_sum_result'] == "3"){
                        if($latest_status_result[0]['chief_approve_result_check'] == "0" || $latest_status_result[0]['chief_approve_result_check'] == "2"){
        
                            for($i=0; $i < sizeof($self_assessment_result); $i++){
        
                                $self_assessment_result_Normal = $self_assessment_result[$i]["self_assessment_result"];
                                $self_assessment_result_specific = $self_assessment_result[$i]["self_assessment_result_specific"];
                            
                                $self_assessment_result[$i]['self_assessment_TextNormal'] = $this->db
                                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_Normal'")
                                ->result_array();
                
                                $self_assessment_result[$i]['self_assessment_TextSpecific'] = $this->db
                                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                                ->result_array();
                            }
                
                            
                            // work from 2 only
                            return  array(  'status' => "true" , 'result' =>
                            array(
                                'status'=>"false" , 
                                'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
                                "self_assessment_result" => $self_assessment_result,
                                ));
        
                                
        
        
                        }else{
                                return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                        }
                    }else{
                        return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                    }

                }else{
                    return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                }
    
            }else{
                return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
            }

           
       
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }

       
    }




    public function Update_User_latest_status($result,$self_assessment_result){

      
        $user_ad_code =  $result['user_ad_code'];

        $data = $this->db->query("SELECT COUNT(user_ad_code)FROM cv_user_latest_status WHERE user_ad_code = '$user_ad_code'")->result_array();
        
        $count = $data[0]["COUNT(user_ad_code)"];

        $data = array(
            'user_ad_code' => $user_ad_code, 
            'self_assessment_result' =>  $result['self_assessment_result'], 
            'self_assessment_colorNormal' => $result['colorNormal'], 
            'self_assessment_result_specific' =>  $result['self_assessment_result_specific'], 
            'self_assessment_colorSpecific' => $result['colorSpecific']
        );

      

        if($count == "0"){

            if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
                $data['self_assessment_sum_result'] = "3" ;
                $data['self_assessment_sum_color'] = "danger" ;
                // alert to boss
            }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
                $data['self_assessment_sum_result'] = "2" ;
                $data['self_assessment_sum_color'] = "warning" ;
                // alert to boss
            }else if ($result['colorNormal'] == "success" || $result['colorSpecific'] == "success"){
                $data['self_assessment_sum_result'] = "1" ;
                $data['self_assessment_sum_color'] = "success" ;
            }
            $data['chief_approve_result_check'] = "0";
            $data['chief_approve_id'] = "0";
    
            $this->db->insert('cv_user_latest_status', $data);
            if(($this->db->affected_rows() != 1) ? false : true){

                $this->Alert_to_Chief($user_ad_code,$self_assessment_result);

            }

        }else if($count == "1"){

            $detail_user = $this->db->query("SELECT * FROM cv_user_latest_status WHERE user_ad_code = '$user_ad_code'")->result_array();
         
            if($detail_user[0]['chief_approve_result_check'] == "2"){
                // $data['chief_approve_result_check'] = "0";
                // $data['chief_approve_id'] = "0";
            }else{
                $data['chief_approve_result_check'] = "0";
                $data['chief_approve_id'] = "0";
            }

            if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
                $data['self_assessment_sum_result'] = "3" ;
                $data['self_assessment_sum_color'] = "danger" ;
               
                 // alert to boss
            }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
                $data['self_assessment_sum_result'] = "2" ;
                $data['self_assessment_sum_color'] = "warning" ;
                // alert to boss
            }
         

            $this->db->trans_begin();
            $this->db->where('user_ad_code', $user_ad_code)->set($data)->update('cv_user_latest_status');
                
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                // return $status;
            } else {
                $this->db->trans_commit();

                $this->Alert_to_Chief($user_ad_code,$self_assessment_result);
            }
        }

    }


    public function Get_Sum_Status(){

        $query_all = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status`')
            ->result_array();

        $query_green = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "1"')
            ->result_array();

        $query_yellow = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "2"')
            ->result_array();

        $query_red = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "3"')
            ->result_array();
     
        $result_count = array( 
            'all_user_count' => $query_all[0]['count(*)'],
            'green_user_count' => $query_green[0]['count(*)'],
            'yellow_user_count' => $query_yellow[0]['count(*)'],
            'red_user_count' => $query_red[0]['count(*)'],  
        );     

        $result = array( 
            'status' => "true",
            'result' => $result_count
             
        );     
        

        return $result;
    }


    public function User_get_history_all_form($result){
        if(isset($result['user_ad_code'])){

            $user_ad_code = $result['user_ad_code'];

            $user_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`
                    INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_code  = '$user_ad_code'")
                ->result_array();

            // print_r($user_result); exit(); 


           
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
                    $self_assessment_result_specific = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result_specific"];
                    
                    $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextNormal'] = $this->db
                    ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                    ->result_array();
    
                    $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextSpecific'] = $this->db
                    ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    ->result_array();



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

 
                    $self_assessment_id = $user_self_assessment_result[$index_user_self_assessment_result]['self_assessment_id'];
                    $user_self_assessment_result_detail = $this->db
                    ->query("SELECT * FROM `cv_self_assessment_detail` WHERE self_assessment_id = '$self_assessment_id'")
                    ->result_array();

          
                    if(sizeof($user_self_assessment_result_detail) != 0){

        

                        for($index_user_self_assessment_result_detail=0; $index_user_self_assessment_result_detail < sizeof($user_self_assessment_result_detail); $index_user_self_assessment_result_detail++){
                          
                            $self_assessment_detail_result = array(json_decode($user_self_assessment_result_detail[$index_user_self_assessment_result_detail]['self_assessment_detail_result'], true));  

                            $user_self_assessment_result_detail[$index_user_self_assessment_result_detail]['self_assessment_detail_result'] =  $self_assessment_detail_result ;

                        }
                        
                        $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['user_self_assessment_result_detail'] = $user_self_assessment_result_detail;
                    }else{
                        $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['user_self_assessment_result_detail'] = array();
                    }
                   
                   

                }
            }

            return  array(  'status' => "true" , 'result' => $user_result);

        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
        

    }


    public function Get_user_latest_status_by_dept_code($result){


        if(isset($result['dept_code'])){
           
            $dept_code = $result['dept_code'];
            if($dept_code == "0000" ){

                $query_user = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`
                    INNER JOIN `cv_user` ON `cv_user`.user_ad_code = `cv_user_latest_status`.user_ad_code " )
                ->result_array();
                // print_r($this->db->last_query());  exit();
               
                for($i=0; $i < sizeof($query_user); $i++){
    
                    $user_ad_code = $query_user[$i]['user_ad_code'];
                    // if($query_user[$i]['chief_approve_result_check']!="2"){
                      
                        $user_self_assessment_result = $this->db
                        ->query("SELECT * FROM `cv_self_assessment`
                            WHERE `user_ad_code` =  '$user_ad_code'
                            ORDER BY `self_assessment_id` DESC LIMIT 100")
                        ->result_array();
                    // }else{
                    //     $query_user[$i]['user_self_assessment_result'] = array();
                    // }

                    
                    $query_user[$i]['user_self_assessment_result']  = $user_self_assessment_result;

                    for($index_user_self_assessment_result=0; $index_user_self_assessment_result < sizeof($user_self_assessment_result); $index_user_self_assessment_result++){

                        $nurse_comment_id = $user_self_assessment_result[$index_user_self_assessment_result]['nurse_comment_id'];
                    
                        if($nurse_comment_id != "0"){

                            $nurse_comment_result = $this->db
                            ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = $nurse_comment_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = array();

                        }

                        $chief_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['chief_approve_id'];

                        if($chief_approve_id != "0"){

                            $chief_approve_result = $this->db
                            ->query("SELECT * FROM `cv_chief_approve` WHERE `chief_approve_id` =  '$chief_approve_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = $chief_approve_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = array();

                        }


                        $doctor_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['doctor_approve_id'];

                        if($doctor_approve_id != "0"){

                            $doctor_approve_result = $this->db
                            ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = $doctor_approve_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = array();

                        }


                    }
        
        
                }

                return array(  'status' => "true" , 'result' => $query_user);

             
            }else{
                $query_user = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`
                    INNER JOIN `cv_user`  ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_dept_code LIKE '$dept_code%' "  )
                ->result_array();
                // print_r($this->db->last_query());  exit();
              

                for($i=0; $i < sizeof($query_user); $i++){
                    
                    $user_ad_code = $query_user[$i]['user_ad_code'];
                    
                    // if($query_user[$i]['chief_approve_result_check']!="2"){
                        $user_self_assessment_result = $this->db
                        ->query("SELECT * FROM `cv_self_assessment`
                            WHERE `user_ad_code` =  '$user_ad_code'
                            ORDER BY `self_assessment_id` DESC LIMIT 100")
                        ->result_array();
                    // }else{
                    //     $query_user[$i]['user_self_assessment_result'] = array();
                    // }
                        
                    $query_user[$i]['user_self_assessment_result']  = $user_self_assessment_result;

                    for($index_user_self_assessment_result=0; $index_user_self_assessment_result < sizeof($user_self_assessment_result); $index_user_self_assessment_result++){

                        $nurse_comment_id = $user_self_assessment_result[$index_user_self_assessment_result]['nurse_comment_id'];
                    
                        if($nurse_comment_id != "0"){

                            $nurse_comment_result = $this->db
                            ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = $nurse_comment_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['nurse_comment_result'] = array();

                        }

                        $chief_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['chief_approve_id'];

                        if($chief_approve_id != "0"){

                            $chief_approve_result = $this->db
                            ->query("SELECT * FROM `cv_chief_approve` WHERE `chief_approve_id` =  '$chief_approve_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = $chief_approve_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['chief_approve_result'] = array();

                        }


                        $doctor_approve_id = $user_self_assessment_result[$index_user_self_assessment_result]['doctor_approve_id'];

                        if($doctor_approve_id != "0"){

                            $doctor_approve_result = $this->db
                            ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                            ->result_array();

                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = $doctor_approve_result;

                        }else{
                            $query_user[$i]['user_self_assessment_result'][$index_user_self_assessment_result]['doctor_approve_result'] = array();

                        }


                    }
                  
        
                }

                return array(  'status' => "true" , 'result' => $query_user);
            }
           
            
        }else{
            return  array(  'status' => "false" , 'result' => "request dept_code" );
        }


       

    }


    public function Get_detail_self_assessment_history_with_id($result){
        if(isset($result['user_ad_code'])){

            
            $user_ad_code = $result['user_ad_code'];

            $get_user_result = $this->db
            ->query("SELECT * FROM `cv_user_latest_status`INNER JOIN `cv_user` 
                ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
                WHERE `cv_user`.user_ad_code = '$user_ad_code'  
                -- AND `cv_user_latest_status`.`chief_approve_result_check` = 1 
                -- AND `cv_user_latest_status`.`chief_approve_id` = 0 
                AND `cv_user_latest_status`.`self_assessment_sum_result` = 4")
            ->result_array();

            $query_detail = $this->db
            ->query("SELECT * FROM `cv_self_assessment`
                WHERE `user_ad_code` = '$user_ad_code'
                AND `chief_approve_result_check` IS NOT NULL 
                ORDER BY `self_assessment_id` DESC ")
            ->result_array();
            // print_r($this->db->last_query());  exit();

    
            for($i=0; $i < sizeof($query_detail); $i++){

                $self_assessment_result = $query_detail[$i]["self_assessment_result"];
                $self_assessment_result_specific = $query_detail[$i]["self_assessment_result_specific"];
               
                $query_detail[$i]['self_assessment_TextNormal'] = $this->db
                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                ->result_array();

                $query_detail[$i]['self_assessment_TextSpecific'] = $this->db
                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                ->result_array();
            }

        

            $data = array( 
                'chief_result' => array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true)),
                'user_result' => $get_user_result,
                'query_detail_risk_at_latest' => array(),
                'detail_self_assessment' => $query_detail
               
            );     

            $result = array( 
                'status' => "true",
                'result' => $data
                    
            );     
            
           return $result;
            
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
    }

    public function Get_detail_self_assessment_with_id($result){
        if(isset($result['user_ad_code'])){
            if(isset($result['self_assessment_id'])){
             
            
            $user_ad_code = $result['user_ad_code'];
            $self_assessment_id = $result['self_assessment_id'];

            $get_user_result = $this->db
                    ->query("SELECT * FROM `cv_user_latest_status`INNER JOIN `cv_user` 
                        ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
                        WHERE `cv_user`.user_ad_code = '$user_ad_code'")
                    ->result_array();
                        // print_r($this->db->last_query());  exit();
          
            $query_detail = $this->db->query(" SELECT * FROM `cv_self_assessment` where self_assessment_id = '$self_assessment_id'")->result_array();
           
            for($i=0; $i < sizeof($query_detail); $i++){

                $self_assessment_result = $query_detail[$i]["self_assessment_result"];
                $self_assessment_result_specific = $query_detail[$i]["self_assessment_result_specific"];
                
                $query_detail[$i]['self_assessment_TextNormal'] = $this->db
                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                ->result_array();

                $query_detail[$i]['self_assessment_TextSpecific'] = $this->db
                ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                ->result_array();

                $nurse_comment_id = $query_detail[$i]['nurse_comment_id'];
                        
                if($nurse_comment_id != "0"){

                    $nurse_comment_result = $this->db
                    ->query("SELECT * FROM `cv_nurse_comment` WHERE `nurse_comment_id` =  '$nurse_comment_id'")
                    ->result_array();


                }else{
                    $nurse_comment_result = array();

                }

                $chief_approve_id = $query_detail[$i]['chief_approve_id'];

                if($chief_approve_id != "0"){

                    $chief_approve_result = $this->db
                    ->query("SELECT * FROM `cv_chief_approve` WHERE `chief_approve_id` =  '$chief_approve_id'")
                    ->result_array();

                }else{
                    $chief_approve_result = array();

                }

                $doctor_approve_id = $query_detail[$i]['doctor_approve_id'];

                if($doctor_approve_id != "0"){

                    $doctor_approve_result = $this->db
                    ->query("SELECT * FROM `cv_doctor_approve` WHERE `doctor_approve_id` =  '$doctor_approve_id'")
                    ->result_array();

                }else{
                    $doctor_approve_result = array();

                }
                
            }

            
            $data = array( 
                'chief_result' => array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true)),
                'user_result' => $get_user_result,
                'detail_self_assessment' => $query_detail,
                'nurse_comment_result' => $nurse_comment_result,
                'chief_approve_result' => $chief_approve_result,
                'doctor_approve_result' => $doctor_approve_result
                
            );     

            $result = array( 
                'status' => "true",
                'result' => $data
                    
            );     

            return  $result;
            

            }else{
                return  array(  'status' => "false" , 'result' => "request self_assessment_id" );
            }
            
        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
    }

    public function Get_detail_self_assessment_with_id_and_check_boss($result){
        if(isset($result['self_assessment_id'])){


              
            $self_assessment_id = $result['self_assessment_id'];

            $self_assessment_result = $this->db
            ->query("SELECT * FROM `cv_self_assessment` WHERE  self_assessment_id= '$self_assessment_id'")
            ->result_array();
            // print_r($this->db->last_query());  exit();

            if(sizeof($self_assessment_result) != 0){

                $user_ad_code =  $self_assessment_result[0]['user_ad_code']; 
               
                $get_user_result = $this->db
                ->query("SELECT * FROM `cv_user_latest_status`INNER JOIN `cv_user` 
                    ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
                    WHERE `cv_user`.user_ad_code = '$user_ad_code' ")
                ->result_array();
        
               
                for($i=0; $i < sizeof($self_assessment_result); $i++){
    
                    $self_assessment_result_normal = $self_assessment_result[$i]["self_assessment_result"];
                    $self_assessment_result_specific = $self_assessment_result[$i]["self_assessment_result_specific"];
                   
                    $self_assessment_result[$i]['self_assessment_TextNormal'] = $this->db
                    ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_normal'")
                    ->result_array();
    
                    $self_assessment_result[$i]['self_assessment_TextSpecific'] = $this->db
                    ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    ->result_array();
                }
    
                $result_boss = array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true));
       

                if($user_ad_code == $result_boss[0]['PN_NO'] ){
                    $get_high_level =  $this->db
                    ->query("SELECT * FROM `cv_member_rule` WHERE user_ad_code = '$user_ad_code'")
                    ->result_array();
                    $ad_boss = array(array('user_ad_boss' => $get_high_level[0]['member_ad_boss'] , 'user_ad_boss_name' => $get_high_level[0]['member_name_boss']));
                   
                }else{
                   
                    $ad_boss = array(array('user_ad_boss' => $result_boss[0]['PN_NO']  , 'user_ad_boss_name' => $result_boss[0]['FULL_NAME']));
                }
    
                $data = array( 
                    'chief_result' => $ad_boss,
                    'user_result' => $get_user_result,
                    'detail_self_assessment' => $self_assessment_result
                   
                );     
    
                $result = array( 
                    'status' => "true",
                    'result' => $data
                        
                );     
                
               return $result;

            }else{
                return  array(  'status' => "false" , 'result' => "self_assessment_result null" );
            }
          

        }else{
            return  array(  'status' => "false" , 'result' => "request self_assessment_id" );
        }
    }


    public function Chief_approve($result){
        // INSERT INTO `cv_chief_approve` (`chief_approve_id`, `chief_approve_by_id`, `chief_approve_datetime`, `chief_approve_result_check`) VALUES (NULL, '003599', '2020-12-30 13:51:29', '1');
        if(isset($result['chief_approve_by_id'])){
            if(isset($result['user_ad_code'])){
                if(isset($result['chief_approve_result_check'])){
                    if(isset($result['chief_approve_detail'])){
                        if(isset($result['self_assessment_id'])){

                  
                            $chief_approve_by_id = $result['chief_approve_by_id'];
                            $user_ad_code = $result['user_ad_code'];    
                            $chief_approve_result_check = $result['chief_approve_result_check'];        
                            $chief_approve_detail = $result['chief_approve_detail'];
                            $self_assessment_id =  $result['self_assessment_id'];
                                
                            $data = array(
                                'chief_approve_id' => NULL,
                                'user_ad_code' => $user_ad_code,
                                'chief_approve_by_id' => $chief_approve_by_id,
                                'chief_approve_result_check' =>  $chief_approve_result_check,
                                'chief_approve_detail' => $chief_approve_detail,
                                'chief_approve_datetime' =>  date("Y-m-d h:i:s")
                            );

                            $this->db->insert('cv_chief_approve', $data);
                            if(($this->db->affected_rows() != 1) ? false : true){
                    
                                $query = $this->db
                                ->query("SELECT * FROM `cv_chief_approve` WHERE `user_ad_code` = '$user_ad_code' ORDER BY `chief_approve_id` DESC LIMIT 1")
                                ->result_array();
                                
                                // update cv_user_latest_status
                                $this->db->trans_begin();
                                $this->db->where('user_ad_code', $user_ad_code)
                                ->set(
                                    array( 

                                        'chief_approve_result_check' => $query[0]['chief_approve_result_check'],
                                        'chief_approve_id' => $query[0]['chief_approve_id'] 

                                        )) ->update('cv_user_latest_status');

                                        // print_r($this->db->last_query());  exit();
        
                                if ($this->db->trans_status() === false) {
                                    $this->db->trans_rollback();
                                    return  array(  'status' => "false" , 'result' => "update cv_user_latest_status_false" );
                                } else {
                                    $this->db->trans_commit();
                    

                                    //update cv_self_assessment
                                    $this->db->trans_begin();
                                    // array('self_assessment_sum_result' => "",'chief_approve_result_check'=> 0)
                                    $this->db->where('self_assessment_id', $self_assessment_id)
                                    ->set( array(
                                        'chief_approve_result_check' => $query[0]['chief_approve_result_check'] ,
                                        'chief_approve_id' =>  $query[0]['chief_approve_id'] 
                                        ))
                                    ->update('cv_self_assessment');
        
                                
                                    if ($this->db->trans_status() === false) {
                                        $this->db->trans_rollback();
                                        return  array(  'status' => "false" , 'result' => "update cv_user_latest_status_false" );
                                    } else {
                                        $this->db->trans_commit();

                                        if($query[0]['chief_approve_result_check'] == "2"){
                                  
                                            $this->Alert_to_Doctor($self_assessment_id);
                                           
                                        }
        
                                        //update cv_user_latest_status
                                        // print_r($query[0]); exit();
                                        if($query[0]['chief_approve_result_check'] == "1"){
                                            //update to success green
                                    
                                            $this->db->trans_begin();
                                            $this->db->where('user_ad_code', $user_ad_code)->set(
                                                array( 
                                                    'chief_approve_result_check' => $query[0]['chief_approve_result_check'],
                                                    'self_assessment_sum_result' => "4",
                                                    'self_assessment_sum_color' => "light"
                                                    
                                            ))->update('cv_user_latest_status');
                                                
                                            if ($this->db->trans_status() === false) {
                                                $this->db->trans_rollback();
                                                return  array(  'status' => "false" , 'result' => "update cv_user_latest_status" );
                                            } else {
                                                $this->db->trans_commit();
                                
                                                return  array(  'status' => "true" , 'result' => "chief_approve_true" );
                                            }
                                    
                                        }else{
                                            return  array(  'status' => "true" , 'result' => "chief_approve_true" );
                                        }
                        
                    
                                    }
                                
                                
                                }
                            }
                        }else{
                            return  array(  'status' => "false" , 'result' => "request self_assessment_id" );
                        }   
                       
                    }else{
                        return  array(  'status' => "false" , 'result' => "request chief_approve_detail" );
                    }

                }else{
                    return  array(  'status' => "false" , 'result' => "request chief_approve_result_check" );
                }
           
                
            }else{
                return  array(  'status' => "false" , 'result' => "request user_ad_code" );
            }

        }else{
            return  array(  'status' => "false" , 'result' => "request chief_approve_by_id" );
        }


    }



    public function Alert_to_Chief($user_ad_code,$self_assessment_result){
  
        $detail_user = $this->db
        ->query("SELECT * FROM `cv_user_latest_status`
            INNER JOIN `cv_user` ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
            WHERE `cv_user`.user_ad_code = '$user_ad_code'")
        ->result_array();

        $chief_result = array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true));

        if(sizeof($detail_user) != 0){
            
            if($detail_user[0]['self_assessment_sum_result'] == "2" || $detail_user[0]['self_assessment_sum_result'] == "3"){ 
                
            
                if(sizeof($chief_result) != 0){

                    $user_ad_name = $detail_user[0]['user_ad_name'];
                    $user_ad_tel = $detail_user[0]['user_ad_tel'];
                    $user_ad_dept_name = $detail_user[0]['user_ad_dept_name'];
                    $self_assessment_id = $self_assessment_result[0]['self_assessment_id'];
                    // $self_assessment_id = "264";
      
                    $liff = "line://app/1655109480-2XKglnaX?liff.state=";
                    $path = "Covid19_boss/".$self_assessment_id;
                    $url = $liff.urlencode($path);

                    //ALERT TO NURSE ============================================================================
                    $text_nurse = "แจ้งเตือน คุณพยาบาลและคณะ".
                    "\n\nผลการประเมิน Covid-19 \nของ ".$user_ad_name.
                    "\n".$user_ad_dept_name.
                    "\nโทร. ".$user_ad_tel.
                    "\n\nเข้าเกณฑ์มีความเสี่ยง \nกรุณาตรวจสอบข้อมูลจากเว็บไซต์\nhttps://change.toat.co.th/covid19/index.php\n";

                    $this->Alert_to_Nurse($text_nurse);
    
                    if( $user_ad_code == $chief_result[0]['PN_NO'] ){
    
                        // user is Director
                        $boss_result = $this->Get_Boss_by_ad_Director($user_ad_code);
                
                        if(sizeof($boss_result) != 0){

                            // $user_ad_id_recrive = "003599";
                            $user_ad_id_recrive = $boss_result[0]['member_ad_boss'];
                            $user_ad_id_recrive_name =  $chief_result[0]['member_name_boss'];
                           
                            
                            $text = "แจ้งเตือน ".$user_ad_id_recrive_name.
                            "\n\nผลการประเมิน Covid-19 \nของ ".$user_ad_name.
                            "\n".$user_ad_dept_name.
                            "\nโทร. ".$user_ad_tel.
                            "\n\nเข้าเกณฑ์มีความเสี่ยง \nกรุณาตรวจสอบข้อมูลจาก\n".$url."\n\nหรือจากเว็บไซต์\nhttps://change.toat.co.th/covid19/index.php\n";

                            $data = array(
                                'header' => array(array("User-Agent" => "back_end_Covid")),
                                'body' => array(array("user_ad_id_recrive" => $user_ad_id_recrive , 'text' => $text )),
                                'detail' => "sdfgsdfgsdfgsdfgsdfgsdfg",
                
                            );
                            // return $data;
                            
                            // $ch = curl_init();
                            // curl_setopt($ch, CURLOPT_URL, 'https://webhook.toat.co.th/linebot/web/index.php/api/Api_LineMessage/Send_Line_Message');
                            // curl_setopt($ch, CURLOPT_POST, true);
                            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data  ));
                            // $result = curl_exec($ch);
                            // curl_close($ch);
                            // return  $result;


               
                          
                        }
    
                    }else{

                        // $user_ad_id_recrive = "003599";
                        $user_ad_id_recrive = $chief_result[0]['PN_NO'];
                        $user_ad_id_recrive_name =  $chief_result[0]['FULL_NAME'];
                        $user_ad_id_recrive_dept_name = $chief_result[0]['DEPT_NAME'];

                    
                        $text = "แจ้งเตือน ".$user_ad_id_recrive_name.
                        "\n".$user_ad_id_recrive_dept_name.
                        "\n\nผลการประเมิน Covid-19 \nของ ".$user_ad_name.
                        "\n".$user_ad_dept_name.
                        "\nโทร. ".$user_ad_tel.
                        "\n\nเข้าเกณฑ์มีความเสี่ยง \nกรุณาตรวจสอบข้อมูลจาก\n".$url."\n\nหรือจากเว็บไซต์\nhttps://change.toat.co.th/covid19/index.php\n";


                        $data = array(
                            'header' => array(array("User-Agent" => "back_end_Covid")),
                            'body' => array(array("user_ad_id_recrive" => $user_ad_id_recrive , 'text' => $text )),
                            'detail' => "alert",
            
                        );

                        // return $data;
                        
            
                        
                        // $ch = curl_init();
                        // curl_setopt($ch, CURLOPT_URL, 'https://webhook.toat.co.th/linebot/web/index.php/api/Api_LineMessage/Send_Line_Message');
                        // curl_setopt($ch, CURLOPT_POST, true);
                        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data  ));
                        // $result = curl_exec($ch);
                        // curl_close($ch);
                        // return  $result;
                
                      
                    }         
                }
    
            }
        }

    }


    public function Alert_to_Doctor($self_assessment_id){

        // $self_assessment_id = "360";
        
        $self_assessment_result = $this->db
        ->query("SELECT * FROM `cv_self_assessment`WHERE self_assessment_id = '$self_assessment_id'")
        ->result_array();

      
        $user_ad_code = $self_assessment_result[0]['user_ad_code'];

        $detail_user = $this->db
        ->query("SELECT * FROM `cv_user_latest_status`
            INNER JOIN `cv_user` ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
            WHERE `cv_user`.user_ad_code = '$user_ad_code'")
        ->result_array();

        $chief_approve_id = $self_assessment_result[0]['chief_approve_id'];

        $chief_approve_result = $this->db
        ->query("SELECT * FROM `cv_chief_approve` WHERE chief_approve_id = '$chief_approve_id'")
        ->result_array();


        $chief_approve_detail = json_decode($this->Get_user_detail($chief_approve_result[0]['chief_approve_by_id']), true);

        $chief_approve_name = $chief_approve_detail['result']['personal']['PersonalName']; 
        $chief_approve_dept_name = $chief_approve_detail['result']['personal']['Department']; 

        
        $user_ad_name = $detail_user[0]['user_ad_name'];
        $user_ad_tel = $detail_user[0]['user_ad_tel'];
        $user_ad_dept_name = $detail_user[0]['user_ad_dept_name'];
    
        $text_nurse = "แจ้งเตือน คุณหมอ".
        "\n\nผลการประเมิน Covid-19 \nของ ".$user_ad_name.
        "\n".$user_ad_dept_name.
        "\nโทร. ".$user_ad_tel.
        "\n\nเข้าเกณฑ์มีความเสี่ยง \nยืนยันข้อมูลจาก ".$chief_approve_name."\n".$chief_approve_dept_name."\n\nกรุณาตรวจสอบข้อมูลจากเว็บไซต์\nhttps://change.toat.co.th/covid19/index.php\n";

        $this->Alert_to_Nurse($text_nurse);
        // print_r($text_nurse); exit();
         
        
    }

    public function Alert_to_Nurse($text){

        $line_token = "CedBa3gSQAB8GBN3chetiN9jNUaywR4Xk4hSMzxasRf";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/notify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded' , 'Authorization: Bearer '.$line_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  "message=".$text );
        $result = curl_exec($ch);
        curl_close($ch);
        return  $result;
    }



   
} 