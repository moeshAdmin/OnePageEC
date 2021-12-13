<?php
$user_detail=$this->session->all_userdata();
?>
<!-- 選單資訊 (ex. MenuBar, Navigation Bar, ...) -->
<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow" >

      <a class="navbar-brand col-sm-2 col-md-2 mr-0" href="#">HugePlusEC Management</a>
      <ul class="navbar-nav px-3">
        <li class="nav-item">
          <a href="<?php echo base_url(); ?>">
            <i class="mdi mdi-home"></i>
            網站首頁
          </a>
        </li>
      </ul>
      
    <button style="display: none" type="button" class="navbar-toggler" @click="show = !show">
            <span class="navbar-toggler-icon"></span>
    </button>
</nav>
<div id="sys-msg" style="top:50;position: fixed;width:100%;z-index: 999"></div>
<div id="hidden-msg" class="input-group login-input" style="display: none"></div>
