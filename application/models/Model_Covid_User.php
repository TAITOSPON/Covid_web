<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_Covid_User extends CI_Model
{       


    public function Clear_data($result){
        $user_ad_code = $result['user_ad_code'];


        $this->db->delete('cv_user_policy', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        $this->db->delete('cv_user_latest_status', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        $this->db->delete('cv_self_assessment', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        $this->db->delete('cv_self_assessment_detail', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        
        $this->db->delete('cv_chief_approve', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        $this->db->delete('cv_doctor_approve', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 
        $this->db->delete('cv_nurse_comment', array('user_ad_code' => $user_ad_code)); 
        echo $this->db->last_query(); 

        // return  $this->db->last_query(); 
    }
    
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

    public function Check_user_policy($result){
       
        if(isset($result['user_ad_code'])){
            $user_ad_code = $result['user_ad_code'];

            $query = $this->db
            ->query(" SELECT * FROM `cv_user_policy`  
                WHERE user_ad_code = '$user_ad_code'
                ORDER BY `cv_user_policy`.`user_policy_id`  DESC LIMIT 1")
            ->result_array();
            
            if(sizeof($query)!= 0){
                return  array(  'status' => "true" , 'result' => array('user_ad_code' => $user_ad_code , 'user_policy_status_approve_policy' => $query[0]['user_policy_status_approve_policy'] ) );
            }else{
                return  array(  'status' => "true" , 'result' => array('user_ad_code' => $user_ad_code , 'user_policy_status_approve_policy' =>"" ) );
            }

        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
    }

    public function User_approve_policy($result){
        // INSERT INTO `cv_user_policy` (`user_ad_code`, `user_policy_status_approve_policy`, `user_policy_datetime`) VALUES ('003599', '0', '2021-01-13 10:38:35');

        if(isset($result['user_ad_code'])){
            if(isset($result['user_policy_status_approve_policy'])){
                $user_ad_code = $result['user_ad_code'];
                $user_policy_status_approve_policy = $result['user_policy_status_approve_policy'];


                $data = array(
                    'user_ad_code' => $user_ad_code, 
                    'user_policy_status_approve_policy' => $user_policy_status_approve_policy,
                    'user_policy_datetime' => date("Y-m-d h:i:s"),
                );
            
                $this->db->insert('cv_user_policy', $data);
                if(($this->db->affected_rows() != 1) ? false : true){
                    return  array(  'status' => "true" , 'result' => "user approve polivy true" );
                }else{
                    return  array(  'status' => "false" , 'result' => "user approve polivy false" );
                }
            }else{
                return  array(  'status' => "false" , 'result' => "request user_policy_status_approve_policy" );
            }

        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
        
    }

    public function Check_user_member_type($result){
        if(isset($result['user_ad_code'])){
            $user_ad_code = $result['user_ad_code'];

            $member_rule_result = $this->db
                ->query("SELECT * FROM `cv_member_rule` WHERE user_ad_code ='$user_ad_code'")
                ->result_array();
            
            if(sizeof($member_rule_result) != 0){

                if(sizeof($member_rule_result) > 1){

                    for ($i=0; $i <sizeof($member_rule_result) ; $i++) { 

                        if($member_rule_result[$i]['member_type'] == "doctor"){
                            return array(  'status' => "true" , 'result' => array(array('member_type' => $member_rule_result[$i]['member_type'])));
                        }
            
                    }
                    
                }else{
                    return array(  'status' => "true" , 'result' => array(array('member_type' => $member_rule_result[0]['member_type'])));
                }

             
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
                        'user_ad_tel' => $result['user_ad_tel'],
                        'user_ad_tel_intra' => ""
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
                    'user_ad_tel' => $result['user_ad_tel'],
                    'user_ad_tel_intra' => ""
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
                                                // if( isset( $result['self_assessment_P'] ) ){
                                                    // if( isset( $result['self_assessment_HCW'] ) ){
                                                        // if( isset( $result['self_assessment_C'] ) ){
                                                            if( isset( $result['self_assessment_result'] ) ){
                                                                if( isset( $result['self_assessment_sum_color'] ) ){    
                                                                    if( isset( $result['self_assessment_sum_result'] ) ){
                                                                        // if( isset( $result['colorSpecific'] ) ){
                                                                            return array( 'status' => "true" , 'result' => "ok" );
                                                                        // }else{
                                                                        //     return  array(  'status' => "false" , 'result' => "request colorSpecific" );
                                                                        // }
                                                                    }else{
                                                                        return  array(  'status' => "false" , 'result' => "request self_assessment_sum_result" );
                                                                    }
                                                                } return  array(  'status' => "false" , 'result' => "request self_assessment_sum_color" );
                                                            }else{
                                                                return  array(  'status' => "false" , 'result' => "request self_assessment_result" );
                                                            }
                                                //         }else{
                                                //             return  array(  'status' => "false" , 'result' => "request self_assessment_C" );
                                                //         }
                                                //     }else{
                                                //         return  array(  'status' => "false" , 'result' => "request self_assessment_HCW" );
                                                //     }
                                                // }else{
                                                //     return  array(  'status' => "false" , 'result' => "request self_assessment_P" );
                                                // }
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

        // if($result['self_assessment_result_specific'] == "1"){
        //     $case = array( 
        //         'self_assessment_sum_result' =>$self_assessment_result[0]['self_assessment_sum_result'],
        //         'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
        //         'normal_case' => $this->db
        //             ->where('self_assessment_criterion_id',$result["self_assessment_result"])
        //             ->get('cv_self_assessment_criterion')
        //             ->result_array() ,
        //         'specific_case' => array('status_specific_case'=> "true" ,'result_specific_case' => $this->db
        //             ->where('self_assessment_criterion_id',"12")
        //             ->get('cv_self_assessment_criterion')
        //             ->result_array()
        //          ) 
        //     );     
        //     $status = array( 
        //         'status' => "true" ,
        //         'result' => $case
        //     );
    
        //     return  $status;    
        // }else{
            $case = array( 
                'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
                'self_assessment_sum_result' => $self_assessment_result[0]['self_assessment_sum_result'],
                'self_assessment_sum_color' => $self_assessment_result[0]['self_assessment_sum_color'],
                'self_assessment_result' => $self_assessment_result[0]['self_assessment_result'],
                'normal_case' => $this->db
                    ->where('self_assessment_criterion_id',$result["self_assessment_result"])
                    ->get('cv_self_assessment_criterion')
                    ->result_array() ,
                // 'specific_case' =>  array('status_specific_case'=> "false" ,'result_specific_case' =>$this->db
                // ->where('self_assessment_criterion_id',"11")
                // ->get('cv_self_assessment_criterion')
                // ->result_array())
            );     
            $status = array( 
                'status' => "true" ,
                'result' => $case
            );
    
            return  $status;    
        // }
        
     
       
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
            // 'self_assessment_P' =>  $result['self_assessment_P'], 
            // 'self_assessment_HCW' =>  $result['self_assessment_HCW'], 
            // 'self_assessment_C' =>  $result['self_assessment_C'], 

            'self_assessment_P' =>  "0", 
            'self_assessment_HCW' =>  "0", 
            'self_assessment_C' =>  "0", 
            
            'self_assessment_result' =>  $result['self_assessment_result'], 
            'self_assessment_colorNormal' => $result['self_assessment_sum_color'], 

            // 'self_assessment_result_specific' =>  "", 
            // 'self_assessment_colorSpecific' => "", 

            'self_assessment_date_time' =>  date("Y-m-d h:i:s"),

            "self_assessment_sum_result" => $result['self_assessment_sum_result'],
            "self_assessment_sum_color" => $result['self_assessment_sum_color'],

        );

        // if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
        //     $data['self_assessment_sum_result'] = "3" ;
        //     $data['self_assessment_sum_color'] = "danger" ;
        //     // alert to boss
        // }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
        //     $data['self_assessment_sum_result'] = "2" ;
        //     $data['self_assessment_sum_color'] = "warning" ;
        //     // alert to boss
        // }else if ($result['colorNormal'] == "success" || $result['colorSpecific'] == "success"){
        //     $data['self_assessment_sum_result'] = "1" ;
        //     $data['self_assessment_sum_color'] = "success" ;
        // }


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
                           
                            $self_assessment_result = $this->db->query("SELECT * FROM `cv_self_assessment`  WHERE self_assessment_id = '$self_assessment_id'")->result_array();

                            $form = "2";
                            $this->Alert_to_Chief($form,$user_ad_code,$self_assessment_result);

                            
                            $self_assessment_detail_result = array(json_decode($result_self_assessment_detail[0]['self_assessment_detail_result'], true));

                            $self_assessment_detail_sum_result = $self_assessment_detail_result[0]['self_assessment_detail_sum_result'];

                            $self_assessment_detail_criterion = $this->db->query("SELECT * FROM `cv_self_assessment_detail_criterion` WHERE self_assessment_detail_criterion_id = '$self_assessment_detail_sum_result'")->result_array();

                            $data = array(
                                'self_assessment_detail_sum_result' => $self_assessment_detail_result[0]['self_assessment_detail_sum_result'],
                                'self_assessment_detail_sum_color' => $self_assessment_detail_result[0]['self_assessment_detail_sum_color'],
                                'self_assessment_detail_criterion' => $self_assessment_detail_criterion
                            );

                            // $data = array();
                    
                            // $data['self_assessment_detail_sum_result'] = "0";
                            // $data['self_assessment_detail_sum_color'] = "success";
                            // $data['1_'] = array('status' => "1");
                            // $data['2_s'] = array( 'status'=>"1");
                            // $data['3_n'] = array('status' => "1");
                            // $data['4_'] = array('status' => "1");
                            // $data['5_'] = array('status' => "1");
                            
                            // $result = array(
                            //     'user_ad_code' => "003599",
                            //     'self_assessment_id' => "503",
                            //     'self_assessment_detail_result' => $data
                            // );
                            // return $result;
             
                         
                            return  array(  'status' => "true" , 'result' => $data );

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
      
    }


    public function Set_self_assessment_timeline($result){
        if(isset($result['user_ad_code'])){
            if(isset($result['self_assessment_id'])){
                if(isset($result['assessment_timeline_datetime_input'])){
                    if(isset($result['assessment_timeline_result'])){

            // INSERT INTO `cv_self_assessment_timeline` (`self_assessment_timeline_id`, `user_ad_code`, `assessment_timeline_datetime_input`, `assessment_timeline_result`, `assessment_timeline_datetime_create`)
            //  VALUES (NULL, '003599', '2021-01-19 14:38:16', 'dsghj', '2021-01-19 14:38:16');


                        // $data = array();

                        // $data['body_temp'] = array('value' => "35.5");
                        // $data['symptom'] = array(
                        //     'status_1'=> "1",
                        //     'status_2'=> "1",
                        //     'status_3'=> "1",
                        //     'status_4'=> "1",
                        //     'status_5'=> "1"
                        // );

                        // $data['note'] = array('value' => "dsfghsfghsfghsfghsfghsfghsfghgf");
                    
                        // $result = array(
                        //     'user_ad_code' => "003599",
                        //     'assessment_timeline_datetime_input' => date("Y-m-d h:i:s"),
                        //     'assessment_timeline_result' => $data
                        // );
                        // return $result;


                        $user_ad_code = $result['user_ad_code'];
    

                        $data = array(
                            'self_assessment_timeline_id' => NULL,
                            'user_ad_code' => $user_ad_code,
                            'self_assessment_id' => $result['self_assessment_id'],
                            'assessment_timeline_datetime_input' => $result['assessment_timeline_datetime_input'],
                            'assessment_timeline_result' =>json_encode($result['assessment_timeline_result']),
                            'assessment_timeline_datetime_create' => date("Y-m-d h:i:s")
                        );

                        $this->db->insert('cv_self_assessment_timeline', $data);
                        if(($this->db->affected_rows() != 1) ? false : true){
                            return  array(  'status' => "true" , 'result' => "insert cv_self_assessment_timeline true" );
                        }

                    
                    }else{
                        return  array(  'status' => "false" , 'result' => "request assessment_timeline_result" ); 
                    }
                
                }else{
                    return  array(  'status' => "false" , 'result' => "request assessment_timeline_datetime_input" ); 
                }
            }else{
                return  array(  'status' => "false" , 'result' => "request self_assessment_id" );
            }

        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }

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
            
                    
                    $self_assessment_id = $self_assessment_result[0]['self_assessment_id'];

                    $self_assessment_detail_result = $this->db
                    ->query("SELECT * FROM `cv_self_assessment_detail` WHERE self_assessment_id = '$self_assessment_id' AND user_ad_code = '$user_ad_code'
                    ORDER BY `cv_self_assessment_detail`.`self_assessment_detail_id` DESC LIMIT 1")
                    ->result_array();
 
                    // print_r($self_assessment_detail_result); exit();

                    if(sizeof($self_assessment_detail_result) != 0){

                            // $self_assessment_detail_sum_result = $self_assessment_detail_result
                            // return $self_assessment_detail_result[0]['self_assessment_detail_result'];

                            if($self_assessment_result[0]['chief_approve_result_check'] != "1" ){


                                if($latest_status_result[0]['doctor_approve_status_wfh'] == "0" ){


                                    $self_assessment_detail_result = array(json_decode($self_assessment_detail_result[0]['self_assessment_detail_result'], true));

                                    if($self_assessment_detail_result[0]['self_assessment_detail_sum_result'] == "1"){
                                    
                                        return  array(  'status' => "true" , 'result' => array( 'status'=>"false" ,  'self_assessment_id' => $self_assessment_id  ));

                                    }else{
                                        return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                    }


                                }else if($latest_status_result[0]['doctor_approve_status_wfh'] == "1"){

                                    $doctor_approve_id = $latest_status_result[0]['doctor_approve_id'];

                                    $cv_doctor_approve = $this->db
                                    ->query("SELECT * FROM `cv_doctor_approve`WHERE doctor_approve_id  = '$doctor_approve_id'")
                                    ->result_array();
                                   
                                    if(sizeof($cv_doctor_approve) != 0){

                                        $doctor_approve_wfh_date_end = $cv_doctor_approve[0]['doctor_approve_wfh_date_end'];
                                        $datenow = date("Y-m-d h:i:s");

                                        if($doctor_approve_wfh_date_end > $datenow){
                                            return  array(  'status' => "true" , 'result' => array( 'status'=>"false" ,  'self_assessment_id' => $self_assessment_id  ));
                                        }else{
                                            return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                        }

                                    }else{
                                        return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                    }

                               

                                }else if($latest_status_result[0]['doctor_approve_status_wfh'] == "2"){
                                    return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                }



                            }else{
                                return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                            }

                    }else{

                        // print_r($latest_status_result); exit();
                        if($latest_status_result[0]['self_assessment_sum_result'] == "5"){

                            if($self_assessment_result[0]['chief_approve_result_check'] != "1" ){

                                if($latest_status_result[0]['doctor_approve_status_wfh'] == "0" ){

                                    return  array(  'status' => "true" , 'result' => array( 'status'=>"false" ,  'self_assessment_id' => $self_assessment_id  ));

                                }else if($latest_status_result[0]['doctor_approve_status_wfh'] == "1"){

                                    $nurse_comment_id = $self_assessment_result[0]['nurse_comment_id'];

                                    $nurse_comment_result = $this->db
                                    ->query("SELECT * FROM `cv_nurse_comment`WHERE nurse_comment_id = '$nurse_comment_id'")
                                    ->result_array();
                                   
                                    if(sizeof($nurse_comment_result) != 0){

                                        $nurse_approve_wfh_date_end = $nurse_comment_result[0]['nurse_approve_wfh_date_end'];
                                        $datenow = date("Y-m-d h:i:s");

                                        if($nurse_approve_wfh_date_end > $datenow){
                                            return  array(  'status' => "true" , 'result' => array( 'status'=>"false" ,  'self_assessment_id' => $self_assessment_id  ));
                                        }else{
                                            return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                        }

                                    }else{
                                        return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                    }

                               

                                }else if($latest_status_result[0]['doctor_approve_status_wfh'] == "2"){
                                    return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                                }


                               


                            }else{
                                return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                            }

                       
                        }else{
                            return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                        }
                    
                    }

                                          

                    // if($latest_status_result[0]['self_assessment_sum_result'] == "2" || $latest_status_result[0]['self_assessment_sum_result'] == "3"){
                    //     if($latest_status_result[0]['chief_approve_result_check'] == "0" || $latest_status_result[0]['chief_approve_result_check'] == "2"){
        
                    //         for($i=0; $i < sizeof($self_assessment_result); $i++){
        
                    //             $self_assessment_result_Normal = $self_assessment_result[$i]["self_assessment_result"];
                    //             // $self_assessment_result_specific = $self_assessment_result[$i]["self_assessment_result_specific"];
                            
                    //             $self_assessment_result[$i]['self_assessment_TextNormal'] = $this->db
                    //             ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_Normal'")
                    //             ->result_array();
                
                    //             // $self_assessment_result[$i]['self_assessment_TextSpecific'] = $this->db
                    //             // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    //             // ->result_array();
                    //         }
                
                            
                    //         // work from 2 only
                    //         return  array(  'status' => "true" , 'result' =>
                    //         array(
                    //             'status'=>"false" , 
                    //             'self_assessment_id' => $self_assessment_result[0]['self_assessment_id'],
                    //             "self_assessment_result" => $self_assessment_result,
                    //             ));
        
                                
        
        
                    //     }else{
                    //             return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                    //     }
                    // }else{
                    //     return  array(  'status' => "true" , 'result' => array('status'=>"true" , 'self_assessment_id' => "work from 1 normally"));
                    // }

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
            'self_assessment_colorNormal' => $result['self_assessment_sum_color'], 
            // 'self_assessment_result_specific' =>  $result['self_assessment_result_specific'], 
            // 'self_assessment_colorSpecific' => $result['colorSpecific']

            // "self_assessment_sum_result" => $result['self_assessment_sum_result'],
            // "self_assessment_sum_color" => $result['self_assessment_sum_color'],
        );

      

        if($count == "0"){

            // if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
            //     $data['self_assessment_sum_result'] = "3" ;
            //     $data['self_assessment_sum_color'] = "danger" ;
            //     // alert to boss
            // }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
            //     $data['self_assessment_sum_result'] = "2" ;
            //     $data['self_assessment_sum_color'] = "warning" ;
            //     // alert to boss
            // }else if ($result['colorNormal'] == "success" || $result['colorSpecific'] == "success"){
            //     $data['self_assessment_sum_result'] = "1" ;
            //     $data['self_assessment_sum_color'] = "success" ;
            // }
            $data['self_assessment_sum_result'] = $result['self_assessment_sum_result'] ;
            $data['self_assessment_sum_color'] = $result['self_assessment_sum_color'];

            $data['chief_approve_result_check'] = "0";
            $data['chief_approve_id'] = "0";
    
            $this->db->insert('cv_user_latest_status', $data);
            if(($this->db->affected_rows() != 1) ? false : true){

                $form = "1";
                $this->Alert_to_Chief($form,$user_ad_code,$self_assessment_result);

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

            // if($result['colorNormal'] == "danger" || $result['colorSpecific'] == "danger"){
            //     $data['self_assessment_sum_result'] = "3" ;
            //     $data['self_assessment_sum_color'] = "danger" ;
               
            //      // alert to boss
            // }else if($result['colorNormal'] == "warning" || $result['colorSpecific'] == "warning"){
            //     $data['self_assessment_sum_result'] = "2" ;
            //     $data['self_assessment_sum_color'] = "warning" ;
            //     // alert to boss
            // }
            if($result['self_assessment_sum_result'] != "1"){
                $data['self_assessment_sum_result'] = $result['self_assessment_sum_result'] ;
                $data['self_assessment_sum_color'] = $result['self_assessment_sum_color'];
            }



            $this->db->trans_begin();
            $this->db->where('user_ad_code', $user_ad_code)->set($data)->update('cv_user_latest_status');
                
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                // return $status;
            } else {
                $this->db->trans_commit();

                $form = "1";
                $this->Alert_to_Chief($form,$user_ad_code,$self_assessment_result);
            }
        }

    }


    public function Get_Sum_Status(){

        $query_all = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status`')
            // ->query('SELECT count(*) FROM `cv_user`')
            ->result_array();

        $query_green = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result = "1"')
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
        ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE self_assessment_sum_result != "1" and doctor_approve_status_wfh !=1')
        ->result_array();


        
        
        $yellow_user_count = $this->db
            ->query('SELECT count(*) FROM `cv_user_latest_status` WHERE  doctor_approve_status_wfh =1')
            ->result_array();
      
        $result_count = array( 
            'all_user_count' => $query_all[0]['count(*)'],
            'green_user_count' => $query_green[0]['count(*)'],
            'yellow_user_count' => $yellow_user_count[0]['count(*)'],
            'red_user_count' => $risk[0]['count(*)'],  
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
                    // $self_assessment_result_specific = $user_self_assessment_result[$index_user_self_assessment_result]["self_assessment_result_specific"];
                    
                    $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextNormal'] = $this->db
                    ->query("SELECT * FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                    ->result_array();
    
                    // $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['self_assessment_TextSpecific'] = $this->db
                    // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    // ->result_array();



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


                    $self_assessment_timeline_result = $this->db
                    ->query("SELECT * FROM `cv_self_assessment_timeline` WHERE user_ad_code = '$user_ad_code' AND self_assessment_id = '$self_assessment_id'")
                    ->result_array();


                    if(sizeof($self_assessment_timeline_result) != 0){

                    
                        for($index_self_assessment_timeline_result=0; $index_self_assessment_timeline_result < sizeof($self_assessment_timeline_result); $index_self_assessment_timeline_result++){

                            $assessment_timeline_result = array(json_decode($self_assessment_timeline_result[$index_self_assessment_timeline_result]['assessment_timeline_result'], true)); 
                     

                            $self_assessment_timeline_result[$index_self_assessment_timeline_result]['assessment_timeline_result'] = $assessment_timeline_result; 
                        }

                        $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['user_self_assessment_result_timeline'] =  $self_assessment_timeline_result;
                  

                    }else{
                        $user_result[$index_user_result]['user_self_assessment_result'][$index_user_self_assessment_result]['user_self_assessment_result_timeline'] = array();
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
                // $self_assessment_result_specific = $query_detail[$i]["self_assessment_result_specific"];
               
                $query_detail[$i]['self_assessment_TextNormal'] = $this->db
                ->query("SELECT *  FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                ->result_array();

                // $query_detail[$i]['self_assessment_TextSpecific'] = $this->db
                // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                // ->result_array();
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
                // $self_assessment_result_specific = $query_detail[$i]["self_assessment_result_specific"];
                
                $query_detail[$i]['self_assessment_TextNormal'] = $this->db
                ->query("SELECT * FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result'")
                ->result_array();

                // $query_detail[$i]['self_assessment_TextSpecific'] = $this->db
                // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                // ->result_array();

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
                    // $self_assessment_result_specific = $self_assessment_result[$i]["self_assessment_result_specific"];
                   
                    $self_assessment_result[$i]['self_assessment_TextNormal'] = $this->db
                    ->query("SELECT *  FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_normal'")
                    ->result_array();
    
                    // $self_assessment_result[$i]['self_assessment_TextSpecific'] = $this->db
                    // ->query("SELECT `self_assessment_criterion_data` FROM `cv_self_assessment_criterion` WHERE `self_assessment_criterion_id` = '$self_assessment_result_specific'")
                    // ->result_array();
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


    public function Get_Boss_by_ad($result){

        if(isset($result['user_ad_code'])){

            $user_ad_code = $result['user_ad_code'];

            $chief_result = array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true));

            if(sizeof($chief_result) != 0){

                if( $user_ad_code == $chief_result[0]['PN_NO'] ){

                    $boss_result = $this->Get_Boss_by_ad_Director($user_ad_code);
                    if(sizeof($boss_result) != 0){
                    
                        return  array(  'status' => "true" , 'result' => array('user_ad_boss' => $boss_result[0]['member_ad_boss']) );
                      
                    }

                }else{
                    return  array(  'status' => "true" , 'result' => array('user_ad_boss' => $chief_result[0]['PN_NO']) );
                }
            }

        }else{
            return  array(  'status' => "false" , 'result' => "request user_ad_code" );
        }
      
    }


    public function Alert_to_Chief($form,$user_ad_code,$self_assessment_result){
  
        $detail_user = $this->db
        ->query("SELECT * FROM `cv_user_latest_status`
            INNER JOIN `cv_user` ON `cv_user`.user_ad_code =  `cv_user_latest_status`.user_ad_code  
            WHERE `cv_user`.user_ad_code = '$user_ad_code'")
        ->result_array();

        $chief_result = array(json_decode($this->Get_id_chief_by_dapt_code($user_ad_code), true));

        if(sizeof($detail_user) != 0){
            
            if($form == "1"){
                if($self_assessment_result[0]['self_assessment_sum_result'] == "5" ){ 
                    $this->Alert_text($detail_user,$chief_result,$user_ad_code,$self_assessment_result);  
                }
            }else{
                $this->Alert_text($detail_user,$chief_result,$user_ad_code,$self_assessment_result);  
            }
        }

    }

    public function Alert_text($detail_user,$chief_result,$user_ad_code,$self_assessment_result){

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
                    $user_ad_id_recrive_name =  $boss_result[0]['member_name_boss'];
                   
                    
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

                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://webhook.toat.co.th/linebot/web/index.php/api/Api_LineMessage/Send_Line_Message');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data  ));
                    $result = curl_exec($ch);
                    curl_close($ch);
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
 
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://webhook.toat.co.th/linebot/web/index.php/api/Api_LineMessage/Send_Line_Message');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data  ));
                $result = curl_exec($ch);
                curl_close($ch);
                // return  $result;
        
              
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

        // $line_token = "CedBa3gSQAB8GBN3chetiN9jNUaywR4Xk4hSMzxasRf";
        $line_token = "64JsHmBC8bdTRd27NOUyXhPqZmRRGPJkmq79kmNycLX";
        
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