<?php
class Page extends CI_Model{
   
    function __construct() {
        parent::__construct();
    }
   function page_title($page_id,$type){
         //各頁面名稱
         $detail = $this->Api_common->getDataCustom('cmName,cmTargetURL,cmTargetType,cmTemplate','cms_menu','cmMenuType = "manage" AND cmTargetType = "'.$page_id.'"');
         if($detail){
            $page_title = $detail[0]['cmName'];
            $page = $detail[0]['cmTargetType'];
            $page_c = $detail[0]['cmName'];
            $url = $detail[0]['cmTargetURL'];
         }else{
            $page_title = $page_id;
            $page = "";
            $page_c = "";
         }

         if($type=="title"){
   		   return "<title>".$page_title."</title>";
         }else if($type=="h1"){
            return $page_title;
         }else if($type=="detail"){
            $page_detail[0] = $page_title;
            $page_detail[1] = $page;
            $page_detail[2] = $page_c;
            $page_detail[3] = $url;
            $page_detail[4] = $icon;
            return $page_detail;
         }

   }

   function page_bread($page_id){
      $page_detail = $this->page_title($page_id,"detail");
      $page_bl = "<a href=".site_url().">首頁</a>"." > "."<a href=".site_url()."/pages/".$page_detail[1].">".$page_detail[2]."</a>"." > ".$page_detail[0];
      return '<div id="menuBread">'.$page_bl.'</div>';
   }

   function manage_sideMenu($page_id,$server=null){
      $user_detail=$this->session->all_userdata();
      $this->load->helper('file');
      $fileAry = get_dir_file_info(APPPATH.'/files/cache');
      //$menu = $this->Api_common->getDataCustom('cmName,cmTargetURL,cmTargetType,cmTemplate','cms_menu','cmMenuType = "manage"','cmOrder');
      if(!preg_match('/管理者/', $user_detail['actor'])){
        $user_detail['actor'] .= ';儀錶板';
        $menu = $this->Api_common->getDataInCustom('cmName,cmTargetURL,cmTargetType,cmTemplate','cms_menu','cmTargetType',explode(';', $user_detail['actor']),'cmOrder ASC','in');
      }else{
        $menu = $this->Api_common->getDataCustom('cmName,cmTargetURL,cmTargetType,cmTemplate','cms_menu','cmMenuType = "manage"','cmOrder');
      }

      $return .= '<div id="leftmenu" name="slide-fade" style="font-size:10pt">
                    <nav class="col-md-1 col-lg-1 d-none d-md-block bg-light sidebar">
                              <div class="sidebar-sticky">
                                <ul class="nav flex-column">';
      foreach ($menu as $key => $value) {
        $menuAry[$value['cmTargetType']][$key] = $value;
      }
      foreach ($menuAry as $type => $menu) {
        $list = '';
        $collapse = '';
        foreach ($menu as $key => $value) {
          if($menu[$key]['cmTargetURL']==$_SERVER['REQUEST_URI'].'?type='.$_GET['type']){
            $currentMeta = 'active';
            $collapse = 'collapse show';
            $title = $menu[$key]['cmName'];
          }else if(!$_GET['type']&&$menu[$key]['cmTargetURL']==$_SERVER['REQUEST_URI']){
            $currentMeta = 'active';
            $collapse = 'collapse show';
            $title = $menu[$key]['cmName'];
          }else{
            $currentMeta = '';
          }
          if($menu[$key]['cmTargetType']=='儀錶板'){
            $collapse = 'collapse show';
          }

           $list .= '<li class="nav-item">
                          <a class="nav-link '.$currentMeta.'" href="'.SUB_SITE_PATH.$menu[$key]['cmTargetURL'].'">
                             <i class="mdi '.$menu[$key]['cmTemplate'].'"></i>
                             '.$menu[$key]['cmName'].'
                          </a>
                       </li>';
        }
        $return .= '<h4 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted" data-toggle="collapse" href="#'.$type.'" role="button" aria-expanded="false" aria-controls="'.$type.'" style="cursor:pointer"><span>'.$type.'</span></h4>
        <div class="collapse multi-collapse '.$collapse.'" id="'.$type.'">'.$list.'</div>';

      }
      /*
      foreach (LANG_ARRAY as $langKey => $langName) {
        $lang .= '<option value="'.$langKey.'">'.$langName.'</option>';
      }
      
      $return .= '<li class="nav-item nav-link">
                    <div class="form-group"><label>編輯語系</label> 
                      <select class="form-control" @change="chgLang($event)" v-model="lang">
                        '.$lang.'
                      </select>
                    </div>
                  </li>';*/
      $return .= '<a href="/logout"><li class="nav-item nav-link">登出</li></a>';
      $return .= '<li class="nav-item nav-link">
                    <div class="form-group"><label>帳號資訊</label> '.
                      '<p>'.$user_detail['username'].'('.$user_detail['account'].')</p>'.
                  ' </div>
                  </li>';
      $return .= '<li class="nav-item nav-link">
                    <label>快取數: '.count($fileAry).'</label>
                    <div class="btn btn-info btn-sm btn-block" onclick=submit("#sys-msg","'.base_url().'manage/manage_menu/rebuild","json")>重建快取</div>
                  </li>';        
      $return .= '</ul>
                              </div>
                    </nav>
                  </div>';
      //$return .= '<script type="text/javascript" src="../assets/vue/vueMenu.js"></script>';
      $dt['menu'] = $return;
      $dt['title'] = $title;
      return $dt;
   }
}
?>