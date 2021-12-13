<head>
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimal-ui"/>
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="yes"/>
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/4.1.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/4.1.3/css/material-icons-min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/4.1.3/css/custom-login.css" />
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/vue.js"></script>
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/vue-resource.js"></script>
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/vueInit.js"></script>
</head>

<div id="sys-msg" class="pop-msg">
    <div id="pop-msg-div" :class="'alert alert-dismissable '+attr.popMsg.type" :style="attr.popMsg.css"><button style="display: none;" type="button" class="close" data-dismiss="alert" aria-label="close" @click="closePop()">×</button><img :style="attr.popMsg.loading+'height:20px;float: left;padding-right: 10px;'" src="<?php echo base_url(); ?>assets/images/loading.gif">{{attr.popMsg.text}}</div>
</div>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/vueMain.js"></script>

<div class="container d-flex h-100" >
  <div id="main-front" class="row login-form align-items-center justify-content-center align-self-center col-12" :style="'opacity: '+attr.ui.opacity+';'">
    <div class="container login-form-container col h-100 align-items-center justify-content-center align-self-center" style="padding:30px;">
      <div class="row justify-content-between login-logo">
        <div class="col-auto mr-auto login-text">
          <span>{{title}}</span>
        </div>
        <div class="col-auto" style="padding-right:0">
          <img src="<?php echo SITE_IMAGE; ?>">
        </div>
      </div>
      <div class="row">
        <transition mode="out-in">
        <div class="col">
          <form>
            <div v-if="fcs=='login'">
              <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" aria-describedby="emailHelp" placeholder="Enter email" v-model="login.email">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" class="form-control" placeholder="Password" v-model="login.password">
              </div>
              <div class="form-group form-check">
                <input type="checkbox" class="form-check-input">
                <label class="form-check-label" for="exampleCheck1">Check me out</label>
              </div>
              <div class="btn btn-info btn-sm btn-block" @click="btn('submit-login')">Login</div>
              <div class="form-group">
                <small class="form-text text-muted"><a href="#" @click="btn('signup')">Sign Up</a> | <a href="#" @click="btn('forget')">Forget Password?</a></small>
              </div>
            </div>

            <div v-else-if="fcs=='signup'">
              <div class="row">
                <div class="form-group col-12 col-md-6">
                  <label for="exampleInputEmail1">Name</label>
                  <input type="text" class="form-control" placeholder="Name">
                </div>
                <div class="form-group col-12 col-md-6">
                  <label for="exampleInputEmail1">Email address</label>
                  <input type="email" class="form-control" aria-describedby="emailHelp" placeholder="Enter email">
                </div>
              </div>
              <div class="row">
                <div class="form-group col-12 col-md-6">
                  <label for="exampleInputPassword1">Password</label>
                  <input type="password" class="form-control" placeholder="Password">
                </div>
                <div class="form-group col-12 col-md-6">
                  <label for="exampleInputPassword1">Confirm Password</label>
                  <input type="password" class="form-control" placeholder="Password">
                </div>
              </div>
              <div class="btn btn-info btn-sm btn-block">Sign Up</div>
              <div class="form-group">
                <small class="form-text text-muted"><a href="#" @click="btn('login')">Login</a> | <a href="#" @click="btn('forget')">Forget Password?</a></small>
              </div>
            </div>
            <div v-else-if="fcs=='forget'">
              <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" aria-describedby="emailHelp" placeholder="Enter email">
              </div>
              <div class="btn btn-info btn-sm btn-block">Send Reset Email</div>
              <div class="form-group">
                <small class="form-text text-muted"><a href="#" @click="btn('login')">Login</a> | <a href="#" @click="btn('signup')">Sign Up</a></small>
              </div>
            </div>
            <div v-else-if="fcs=='sso'">
              <div class="btn btn-info btn-block" @click="btn('sso')">使用管理帳號登入</div>
            </div>
            <hr>
            <small class="form-text text-muted">This site is protected by reCAPTCHA and the Google Privacy Policy and Terms of Service apply.</small>
          </form>
        </div>
        </transition>
      </div>
    </div>
    <div class="col h-100 d-none d-lg-block" style="background-image: url(<?php echo URL_API.'/images/login-bg.png' ?>);background-position: bottom;">
      
    </div>
  </div>
</div>

<script type="text/javascript">
  var app = new Vue({
    el: '#main-front',
    data: {
      title:'',
      fcs:'',
      login:{},
      attr:{ui:{opacity:0}}
    },
    created: function (){
      
    },
    methods:{

      type: function () {
        self = this;
        let uri = window.location.href.split('?');
        if (uri.length == 2){
          let param = init.getParam();
          fuc = param['fuc'];
        }else{
          fuc = 'sso';
        }

        if(fuc=='signup'){
          self.$data['title'] = 'Sign Up';
          self.$data['fcs'] =fuc;
        }else if(fuc=='login'){
          self.$data['title'] = 'Login';
          self.$data['fcs'] = fuc;
        }else if(fuc=='sso'){
          self.$data['title'] = 'SSO';
          self.$data['fcs'] = fuc;
        }
      },
      btn: function(type){
        if(type=='signup'){
          self.$data['title'] = 'Sign Up';
          self.$data['fcs'] = type;
        }else if(type=='login'){
          self.$data['title'] = 'Login';
          self.$data['fcs'] = type;
        }else if(type=='forget'){
          self.$data['title'] = 'Forget';
          self.$data['fcs'] = type;
        }else if(type=='submit-login'){
          self.$data['fcs'] = 'login';
          main.showPop('alert-info','載入中...');
          init.callAjax('login',self.$data['login'],{unlock:false},function(res){
            if(res.code==200){
              main.closePop('alert-info','登入成功');
              window.location.href = '<?php echo base_url(); ?>'+'manage/manage_dashboard';
            }
            //
          });
        }else if(type=='sso'){
          window.location.href = "<?php echo base_url().'auth/sso/oauth?url='.$_GET['url'] ?>";
        }
        
      }
    },
    components: {
    },
    mounted: function () {
      this.type();
      setTimeout(() => this.$data.attr.ui.opacity = 1, 100);
    }
  });


</script>