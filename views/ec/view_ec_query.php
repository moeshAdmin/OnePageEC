<?php $user_detail=$this->session->all_userdata(); ?>
<header><h1 style="position: absolute;top:0;color: #fff;display: none;"><?php echo $title.' > '.$cate; ?></h1></header>
<div id="pg-main">
  <div class="container">
    <div class="row">
      <h2><?php echo $title; ?></h2>
    </div>
    <div class="row">
      <div class="col-12">
          <div class="form-group">
            <label for="em">您訂購商品所留下的聯絡信箱</label>
            <input type="email" class="form-control" id="em" aria-describedby="eh" placeholder="請輸入您的信箱" v-model="query.email">
            <small id="eh" class="form-text text-muted">系統會寄發查詢網址至您當初訂購產品的聯絡信箱</small>
          </div>
          <div class="form-group row">
            <div class="col-sm-12" style="text-align: center;">
                <div class="btn btn-info" @click="submit()">送出</div>
            </div>
          </div>

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
  </div>

  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>

</div>
<hr>




<script>
  var app = new Vue({
    el: '#pg-main',
    created: function (){
      setTimeout(function(){
        app.socialStateCheck('fb');
      }, 1000);
    },
    data: {
      query:{email:'',token:''},
      lock:false
    },
    
    methods:{
      
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      },
      submit:function(){
        if(app.$data['lock']==true){
          return;
        }
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['query']['token'] = token;
              submit('#sys-msg','EC_Query/submit','json',app.$data['query'],'',function(res){
                alert(res.msg);
                app.$data['query'] = {};
                app.$data['lock'] = false;
                $('.btn2').removeClass( "disabled" );
              });
            });
        });
      }
    }
  });
</script>

<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_CLIENT; ?>"></script>
<style type="text/css">
      .grecaptcha-badge{
        display: none;
      }
</style>

