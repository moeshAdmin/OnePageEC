<?php $user_detail=$this->session->all_userdata(); ?>
<header><h1 style="position: absolute;top:0;color: #fff;display: none;"><?php echo $title.' > '.$cate; ?></h1></header>
<style>
  .btn{margin-top:10px;}
</style>

<div id="pg-main">
  <div class="container">
    <div class="row">
      <h2><?php echo $title; ?></h2>
    </div>
    <!--未登入會員-->
    <div v-show="'<?php echo $user_detail["m_email"]; ?>'==''" class="row" style="display: none;">
      <!--會員註冊-->
      <div v-if="type=='regiest'"  class="col-12 col-md-12">
        <div class="col-12 col-md-12">
            <div class="form-group row">
              <label for="em">請輸入聯絡信箱</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="請輸入您的信箱" v-model="regiest.email">
              </div>
              <small class="form-text" style="color:red">系統會寄發認證信，請務必填寫正確資料</small>
            </div>
            <div class="form-group row">
              <label for="em">請輸入密碼</label>
              <div class="input-group">
                <input type="password" class="form-control" placeholder="請輸入密碼" v-model="regiest.password">
              </div>
            </div>
            <div class="form-group row">
              <label for="em">再次確認密碼</label>
              <div class="input-group">
                <input type="password" class="form-control" placeholder="再次確認密碼" v-model="regiest.password2">
              </div>
            </div>
            <div class="form-group row">
              <div class="btn btn-info btn-block btn2" @click="regiestSubmit()">啟動會員</div>
            </div>
            <div class="form-group row" style="margin-top:10px;">
              <ul>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=login">會員登入</a></li>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=reset">忘記密碼?</a></li>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=active">重寄啟用信件</a></li>
                <li><a href="<?php echo base_url(); ?>pages/article/privacy">隱私權聲明</a></li>
              </ul>
            </div>
        </div>
      </div>
      <!--會員登入-->
      <div v-else-if="type=='login'"  class="col-12 col-md-6">
        <div class="col-12 col-md-12" v-on:keyup.enter="loginSubmit()">
            <div class="form-group row">
              <label for="em">請輸入聯絡信箱</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="請輸入您的信箱" v-model="request.email">
              </div>
            </div>
            <div class="form-group row">
              <label for="em">請輸入密碼</label>
              <div class="input-group">
                <input type="password" class="form-control" placeholder="請輸入密碼" v-model="request.password">
              </div>
            </div>
            <div class="form-group row">
              <div class="btn btn-info btn-block btn2" @click="loginSubmit()">登入</div>
            </div>
            <div class="form-group row" style="margin-top:10px;">
              <ul>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=regiest">還不是會員? 按這裡加入會員!</a></li>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=reset">忘記密碼?</a></li>
                <li><a href="<?php echo base_url(); ?>ec/EC_Member?type=active">重寄啟用信件</a></li>
                <li><a href="<?php echo base_url(); ?>pages/article/privacy">隱私權聲明</a></li>
              </ul>
            </div>
        </div>
      </div>
      <!--重設密碼-->
      <div v-else-if="type=='reset'"  class="col-12 col-md-6">
        <div class="col-12 col-md-12" v-on:keyup.enter="resetSubmit()">
            <div class="form-group row">
              <label for="em">請輸入您註冊時使用的信箱</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="請輸入您的信箱" v-model="request.email">
              </div>
            </div>
            <div class="form-group row">
              <div class="btn btn-info btn-block btn2" @click="resetSubmit()">發送重設密碼信件</div>
            </div>
        </div>
      </div>
      <!--重寄啟用信-->
      <div v-else-if="type=='active'"  class="col-12 col-md-6">
        <div class="col-12 col-md-12" v-on:keyup.enter="activeSubmit()">
            <div class="form-group row">
              <label for="em">請輸入您註冊時使用的信箱</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="請輸入您的信箱" v-model="request.email">
              </div>
            </div>
            <div class="form-group row">
              <div class="btn btn-info btn-block btn2" @click="activeSubmit()">發送啟用信件</div>
            </div>
        </div>
      </div>
      <!--社群登入-->
      <div v-if="type=='login'" class="col-12 col-md-6">
        <div class="form-group row" >
          <label for="em">或使用您的常用帳號快速登入</label>
          <div class="btn btn-lg btn-primary btn-block btn-social" style="background: #4267b2;border:1px solid #4267b2;" @click="SocialLogin('FB')">
            <div class="btn-social-icon" style="background: none;">
              <img width="24" src="<?php echo base_url(); ?>assets/images/fb-login.png">
            </div>
            <div class="btn-social-text">使用 Facebook 帳號 登入</div>
          </div>
          <div class="btn btn-lg btn-block btn-social" style="background: #00b900;border:1px solid #00b900;color:#fff" @click="SocialLogin('Line')">
            <div class="btn-social-text">使用 Line 帳號 登入</div>
          </div>
          <div id="my-signin2" class="g-signin2 btn-block"></div>
        </div>
      </div>
      <div class="col-12">
        <hr>
          <div class="form-group row" style="border: 1px solid rgb(255, 156, 152);background: rgb(255, 226, 223);padding: 5px 20px 10px 20px;">
            <h4 style="color:#ff0000;">注意事項</h4>
            <div class="col-sm-12">
              <?php echo NOTICE_META; ?>
              <br>本網站採用 reCAPTCHA 識別技術且遵循 Google
                <a href="https://policies.google.com/privacy">Privacy Policy</a> 與
                <a href="https://policies.google.com/terms">Terms of Service</a> 協議。
            </div>
          </div>
      </div>
    </div>   
    <!--已登入會員-->
    <div class="row" v-show="'<?php echo $user_detail["m_email"]; ?>'!=''" style="display: none;">
      <div class="col-12 col-md-2"></div>
      <div class="col-12 col-md-8">
        <form>
          <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">電子信箱</label>
            <div class="col-sm-10">
              <input type="email" class="form-control" v-model="request.email" disabled readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="inputPassword3" class="col-sm-2 col-form-label">姓名</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" v-model="request.name">
            </div>
          </div>
          <div class="form-group row" v-if="'<?php echo $isSSO; ?>'=='Y'">
            <label class="col-sm-2 col-form-label">社群綁定</label>
            <div class="col-sm-10">
              <div class="form-control" disabled readonly><?php echo $ssoType; ?></div>
            </div>
          </div>
          <div class="form-group row" v-if="'<?php echo $isSSO; ?>'=='N'">
            <label class="col-sm-2 col-form-label">是否要重設密碼?</label>
            <div class="col-sm-10">
              <select class="form-control" v-model="request.reset">
                <option value="N">否</option>
                <option value="Y">是</option>
              </select>
            </div>
          </div>
          <div class="form-group row" v-if="request.reset=='Y'">
            <label class="col-sm-2 col-form-label">密碼</label>
            <div class="col-sm-10">
              <input type="password" class="form-control" placeholder="請輸入新密碼" v-model="request.password">
            </div>
          </div>
          <div class="form-group row" v-if="request.reset=='Y'">
            <label class="col-sm-2 col-form-label">再次輸入密碼</label>
            <div class="col-sm-10">
              <input type="password" class="form-control" placeholder="再次輸入密碼" v-model="request.password2">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-sm-10">
              <div class="btn btn-info" @click="memberSubmit()">修改</div>
            </div>
          </div>
        </form>
      </div>
      <div class="col-12 col-md-2"></div>
    </div> 
  </div>

  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>

</div>
<hr>


<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_CLIENT; ?>"></script>
<style type="text/css">.grecaptcha-badge{display: none;}</style>

<script>window.fbAsyncInit = function() {FB.init({appId:'<?php echo APP_ID; ?>',autoLogAppEvents : true,xfbml: true,version:'v9.0'});};</script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<meta name="google-signin-scope" content="profile email">
<meta name="google-signin-client_id" content="<?php echo OAUTH_CLIENT_ID_CUSTOMER; ?>">
<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>


    <script>
      var loginType = '';
      function onSignIn(googleUser,text) {
        if(loginType=='onload'){loginType = '';return;}
        var profile = googleUser.getBasicProfile();        
        submit('#sys-msg','EC_Member/socialLogin/google','json',{id_token:googleUser.getAuthResponse().id_token},'',function(res){
          if(res.code==200){
            setTimeout(function(){
              if('<?php echo $_GET['itemID']; ?>'!=''){
                location.href= "<?php echo base_url();?>ec/EC_Order?itemID=<?php echo $_GET['itemID']; ?>";
              }else{
                location.href= "<?php echo base_url();?>ec/EC_Member_Query";
              }
            }, 200);
          }
        });
      }
      function onFailure(error) {
      }
      function renderButton() {
        loginType = 'onload';
        gapi.signin2.render('my-signin2', {
            'scope': 'profile email',
            'width': 'auto',
            'height': 50,
            'theme': 'dark',
            'longtitle': true, 
            'onsuccess': onSignIn,
            'onfailure': onFailure
        });
      }
    </script>
<script>
  var app = new Vue({
    el: '#pg-main',
    created: function (){
      setTimeout(function(){
      }, 1000);
    },
    data: {
      regiest:{email:'',name:'',phone:'',password:'',password2:'',token:'',itemID:'<?php echo $_GET["itemID"]; ?>'},
      login:{email:'',password:'',token:''},
      request:{email:'<?php echo $user_detail['m_email']; ?>',password:'',token:'',name:'<?php echo $user_detail['m_name']; ?>',reset:'<?php if($_GET['reset']=='Y'){echo 'Y';}else{echo 'N';} ?>'},
      type:'<?php echo $_GET['type']; ?>',
      lock:false
    },
    
    methods:{
      SocialLogin:function(type){
        if(type=='FB'){
          FB.login(function(response) {
            if (response.authResponse) {
              submit('#sys-msg','EC_Member/socialLogin/fb','json',response.authResponse,'',function(res){
                if(res.code==200){
                  if('<?php echo $_GET['itemID']; ?>'!=''){
                    location.href= "<?php echo base_url();?>ec/EC_Order?itemID=<?php echo $_GET['itemID']; ?>";
                  }else{
                    location.href= "<?php echo base_url();?>ec/EC_Member_Query";
                  }
                }
              });
            }
          },{scope: 'email'});
        }else if(type=='Line'){
          location.href = 'https://access.line.me/oauth2/v2.1/authorize?response_type=code&scope=profile%20openid%20email&bot_prompt=normal&client_id=<?php echo OAUTH_LINE_CLIENT_ID; ?>&redirect_uri=<?php echo base_url()."ec/EC_Member/socialLogin/line"; ?>&state=<?php echo $this->Api_common->stringHash("encrypt",rand(1000,9999)."_".$_GET["itemID"]); ?>'
        }        
      },
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      },
      regiestSubmit:function(){
        if(app.$data['lock']==true){
          return;
        }
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['regiest']['token'] = token;
              submit('#sys-msg','EC_Member/regiest','json',app.$data['regiest'],'',function(res){
                if(res.code==200){
                  app.$data['regiest'] = {};
                }
                alert(res.msg);
                app.$data['lock'] = false;
                $('.btn2').removeClass( "disabled" );
              });
            });
        });
      },
      loginSubmit:function(){
        if(app.$data['lock']==true){
          return;
        }
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['request']['token'] = token;
              submit('#sys-msg','EC_Member/login','json',app.$data['request'],'',function(res){
                if(res.code==200){
                  location.href= "<?php echo base_url();?>ec/EC_Member_Query";
                }else{
                  alert(res.msg);
                }
                app.$data['lock'] = false;
                $('.btn2').removeClass( "disabled" );
              });
            });
        });
      },
      resetSubmit:function(){
        if(app.$data['lock']==true){
          return;
        }
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['request']['token'] = token;
              submit('#sys-msg','EC_Member/reset','json',app.$data['request'],'',function(res){
                if(res.code==200){
                  alert(res.msg);
                  location.href= "<?php echo base_url();?>ec/EC_Member";
                }else{
                  alert(res.msg);
                }
                app.$data['lock'] = false;
                $('.btn2').removeClass( "disabled" );
              });
            });
        });
      },
      memberSubmit:function(){
        if(app.$data['lock']==true){
          return;
        }
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['request']['token'] = token;
              submit('#sys-msg','EC_Member/memberEdit','json',app.$data['request'],'',function(res){
                if(res.code==200){
                  alert(res.msg);
                }else{
                  alert(res.msg);
                }
                app.$data['lock'] = false;
                $('.btn2').removeClass( "disabled" );
              });
            });
        });
      }
    }
  });
</script>


