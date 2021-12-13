<?php
$user_detail=$this->session->all_userdata();
$this->load->helper('cookie');

if(!LANG){
  foreach (LANG_ARRAY as $langKey => $langStr) {
    if($this->input->cookie("lang")==$langKey){
      $lang = $this->input->cookie("lang");
    }
  }
  if(!$lang){
    $lang = 'TW';
  }
}else{
  $lang = LANG;
}
$logoURL = SITE_IMAGE;
?>
<!-- 選單資訊 (ex. MenuBar, Navigation Bar, ...) -->
<div id="topMenu" class="container animated fadeIn">
  <div class="d-none d-lg-block" style="text-align: center">
    <a class="navbar-brand" href="<?php echo base_url(); ?>"><img height="80px" src="<?php echo $logoURL; ?>"></a>
  </div>
  <nav class="navbar navbar-expand-lg navbar-light rounded">
    <a class="navbar-brand d-lg-none" href="<?php echo base_url(); ?>"><img height="80px" src="<?php echo $logoURL; ?>"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#tm" aria-controls="tm" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-md-center" id="tm">
      <ul class="navbar-nav">
        <?php 
          foreach ($topMenu as $key => $value) {
            if($value['name']=='會員專區'&&!$user_detail['m_name']){$value['name'] = '會員登入';}
            echo 
            '<li class="nav-item dropdown">
                <a class="nav-link" href="'.$value['url'].'">'.$value['name'].'</a>
             </li>';
          }
          if($user_detail['m_name']){
            echo 
            '<li class="nav-item dropdown">
                <a class="nav-link" href="'.base_url().'/ec/EC_Member/logout">登出</a>
             </li>';
          }
        ?>
      </ul>
    </div>
  </nav>
</div> 
<div id="topMenuButtom"></div>

<div id="sys-msg" style="top:0;position: fixed;width:100%;z-index: 999"></div>
<div id="hidden-msg" class="input-group login-input" style="display: none"></div>
<!-- Success messages -->
