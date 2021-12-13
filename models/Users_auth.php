<?php
class Users_auth extends CI_Model{
   
    function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Api_common');
        $this->load->library('user_agent');
        //檢查是否封鎖IP
        $this->Api_common->chkBlockIP();

        //取得目前的session
        $user_detail=$this->session->all_userdata();
        
        //識別目前所在頁面寫入session
        $temp = explode('?',$_SERVER['REQUEST_URI']);
            $_SERVER['REQUEST_URI'] = $temp[0];
            $nowPage = explode('/', $_SERVER['REQUEST_URI']);
            $user_detail['now_page'] = strtolower($nowPage[2]);
        $this->session->set_userdata($user_detail);
        $user_detail=$this->session->all_userdata();
        $nowPagePreg = '/'.strtolower($user_detail['now_page']).'/';
        $this->Api_common->browserLog($user_detail,$nowPage);
        
        session_write_close();
        if(isset($_SERVER['argv'])){
            $batRunKey = $_SERVER['argv'][count($_SERVER['argv'])-1];
            $serverRun = 'Y';
        }
        if($batRunKey==BACKEND_KEY&&$serverRun=='Y'){
            return;
        }else if(empty($user_detail["account"])&&strpos($_SERVER['REQUEST_URI'], 'manage/manage')>0){
            //管理後台驗證
            if(count($this->input->post())>0){
                echo $this->Api_common->setFrontReturnMsg('901','登入狀態已失效，請重新登入','');
                exit; 
            }else{
                echo $this->Api_common->setFrontReturnMsg('401','','');
                redirect(base_url());
                exit();
            }
        }else if(empty($user_detail["account"])){
            //前台CSRF驗證
            if($_POST['ctn']&&date("YmdHi")==$this->Api_common->stringHash("decrypt", $_POST['ctn'])){
            }else{
                echo $this->Api_common->setFrontReturnMsg('401','','');
                exit;
            }
        }

        $menu = $this->Api_common->getDataCustom('cmName,cmTargetURL,cmTargetType,cmTemplate','cms_menu','cmMenuType = "manage" AND cmTargetURL LIKE "'.$_SERVER['REQUEST_URI'].'%"','cmOrder');
        
        if(!preg_match("/".$menu[0]['cmTargetType']."/", $user_detail["actor"])&&
           !preg_match('/管理者/', $user_detail["actor"])
          ){
            redirect(base_url());
            exit();
        }
    }

    function saveMsg($result,$name,$type){
        $fp = fopen(DIR_SITE_FILE."temp/".$name."Msg.json",$type);
        if(!$fp){
            echo "System Error";
            exit();
        }else{
            $pp = $result;
            //$pp = json_encode($pp);
            fwrite($fp,$pp);
            fclose($fp);
        }
    }

   
}
?>