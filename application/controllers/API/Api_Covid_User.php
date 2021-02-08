<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');

class Api_Covid_User extends REST_Controller{

       public function __construct(){

              parent::__construct();
              $this->load->model('Model_Covid_User');
              $this->load->model('Model_Covid_Nurse');
              $this->load->model('Model_Covid_Doctor');
       }

       public function index_get(){
              echo "HI";
       }

       // FOR USER +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       public function Check_user_policy_post(){
              
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Check_user_policy($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function User_approve_policy_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->User_approve_policy($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
              
       }

       public function Check_user_member_type_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Check_user_member_type($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }


       public function User_self_assessment_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Set_user_ad($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);

       }


       public function User_self_assessment_detail_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Set_self_assessment_detail($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
              // echo $result;
   
       }

       public function User_Set_self_assessment_timeline_post(){
              
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Set_self_assessment_timeline($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Check_self_assessment_latest_with_ad_code_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Check_self_assessment_latest_with_ad_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }
  

       public function Check_Chief_High_Level_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_List_Underline_by_user_ad_boss($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function GetSumStatus_get(){
              $result = $this->Model_Covid_User->Get_Sum_Status();  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }



       public function User_get_history_all_form_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->User_get_history_all_form($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Get_detail_self_assessment_with_id_and_check_boss_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_detail_self_assessment_with_id_and_check_boss($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Alert_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              // $result = $this->Model_Covid_User->Alert_to_Chief($data['user_ad_code']);  
              $result = $this->Model_Covid_User->Alert_to_Doctor();  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Get_Boss_by_ad_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_Boss_by_ad($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Clear_data_user_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Clear_data($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       // FOR USER +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


 


       // FOR Chief +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       public function All_User_by_dept_code_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_user_latest_status_by_dept_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
   
       }

       public function Get_detail_self_assessment_with_id_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_detail_self_assessment_with_id($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       public function Get_detail_self_assessment_history_with_id_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Get_detail_self_assessment_history_with_id($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }

       
       public function Approve_User_by_Chief_post(){
              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Chief_approve($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
       }
       
       // FOR Chief +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    




       // FOR Nurse +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       public function Nurse_All_User_by_dept_code_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Nurse->Nurse_Get_All_User_by_dept_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);

       }

       public function Nurse_Comment_user_with_self_assessment_id_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Nurse->Nurse_Comment_user_with_self_assessment_id($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);

       }

       // FOR Nurse +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     






       // FOR Doctor +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       public function Doctor_All_User_by_dept_code_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Doctor->Doctor_Get_All_User_by_dept_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
   
       }

       public function Doctor_Get_self_assessment_by_user_ad_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Doctor->Doctor_Get_self_assessment_by_user_ad($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
   
       }

       public function Doctor_approve_status_wfh_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Doctor->Doctor_approve_status_wfh($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);    
       }

       
       // FOR Doctor +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



   
       public function test_get(){
              // $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Doctor->Get_All_Dept_Name();  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);    
       }
     

}
