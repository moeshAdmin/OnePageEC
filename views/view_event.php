<div id="pg-main">
  <div class="album animated fadeIn delay-2s">
    <title>{{title}}</title>
      <div class="container main">
        <?php echo $html; ?>
      </div>
  </div>
  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>
</div>

<script>
  var app = new Vue({
    el: '#pg-main',
    created: function (){
      //this.callAjax(this.$data);
    },
    data: {
      breadcrumb: null,
      title:null,
      leftmenu:null,
      parentCate:null,
      topbanner:null,
      banner:null,
      articleData:null,
      type:null,
      form:{product:null,name:null,email:null,company:null,area:null,specify:null,business:[],others:null,request:null,token:null},
      
      ctn:'<?php echo $this->Api_common->stringHash("encrypt", date("YmdHi"));?>'
    },
    
    methods:{
      submit: function (e) {
        //submit('#sys-msg',window.location.href+'/portal/test','json',app.$data);
        //e.preventDefault();
      },
      callAjax: function (data){
        if(window.location.href.indexOf("event")>0){
          data['type'] = 'event';
        }
        submit('#hide-msg',window.location.href.replace(/event/g,'data'),'json',data,'',function(res){
            if(res.code!=200){
              window.location.href = "<?php echo base_url(); ?>";
            }else{
              app.$data['form']['product'] = app.$data['title'];
              app.setInput(res);
              setTimeout(function(){     
                if($(window).width()<991){
                  init.menuCollapse();
                }else{
                  $('#expand-btn').hide();
                }
                if(app.articleData[0]['slider']){
                  $("#article-slider").camRollSlider();
                }
                
              }, 50);
            }
          });
      },
      setInput: function (res){
        $.each(res.data, function(index) {
            app.$data[index] = res.data[index];
        });
      },
      submitForm:function(){
        grecaptcha.ready(function() {
            grecaptcha.execute('6Lf-xqkUAAAAAGHRiN5jkCJ-04lV4rLWDxp5cagU', {action: 'social'}).then(function(token) {
              app.$data['form']['token'] = token;
              submit('#form-msg','//<?php echo base_url(); ?>/pages/submit','json',app.$data['form'],'',function(res){
              });
            });
        });
      },
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      }
    }
  });
</script>

<script src="https://www.google.com/recaptcha/api.js?render=6Lf-xqkUAAAAAGHRiN5jkCJ-04lV4rLWDxp5cagU"></script>
<style type="text/css">
  .grecaptcha-badge{display: none;}
  #topMenu{display: none;}
</style>