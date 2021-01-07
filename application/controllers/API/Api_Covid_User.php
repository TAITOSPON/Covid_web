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
       public function User_self_assessment_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_User->Set_user_ad($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);

       }
  

       public function GetSumStatus_get(){
              $result = $this->Model_Covid_User->Get_Sum_Status();  
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
              $result = $this->Model_Covid_Nurse->Nures_Get_All_User_by_dept_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);

       }
       // FOR Nurse +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     






       // FOR Doctor +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       public function Doctor_All_User_by_dept_code_post(){

              $data = json_decode(file_get_contents('php://input'), true);
              $result = $this->Model_Covid_Doctor->Doctor_Get_All_User_by_dept_code($data);  
              echo json_encode($result,JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);
   
       }
       // FOR Doctor +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



   

     

}
