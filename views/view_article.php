<?php $user_detail=$this->session->all_userdata();?>
<div id="pg-main">
  <div class="album animated fadeIn delay-2s">
      <div class="container main">
            <div class="row">
              <div :class="setClass('menu')" v-if="showMenu">
                <div class="card border-light p-3 rounded">
                  <aside>
                        <?php                         
                          foreach ($contain['leftmenu'] as $key => $cateMenu) {
                            
                            if($cateMenu['active']&&$cateMenu['template']=='none'){
                              //<!-- 不開放連結分類 -->
                              echo '<div class="card-header cate-header active">'.$cateMenu['name'].'</div>';
                            }else if($cateMenu['active']&&$cateMenu['template']=='brand-logo'){
                              //<!-- 顯示品牌logo -->
                              echo '<div class="card-header flex-center" style="background: #fff;text-align: center"></div>';
                            }else if($cateMenu['active']){
                               //<!-- 其他分類 -->
                              echo '<a class="cate-header-link" href="'.$cateMenu['url'].'" title="'.$cateMenu['name'].'"><div class="card-header cate-header active">'.$cateMenu['name'].'</div></a>';
                            }
                            if(!$cateMenu['active']&&$cateMenu['parentID']!=0){
                              echo '<a class="sub-menu cate-header-link" :href="'.$cateMenu['url'].'"><div class="card-header cate-header" title="'.$cateMenu['name'].'">'.$cateMenu['name'].'</div></a>';
                            }
                            if($cateMenu['active']&&$cateMenu['children']){
                              echo '<ul class="list-group">';
                              foreach ($cateMenu['children'] as $key => $cateList) {
                                if($cateList['active']){
                                  echo '<li class="list-group-item active"><a href="'.$cateList['url'].'" title="'.$cateList['name'].'">'.$cateList['name'].'</a></li>';
                                }else{
                                  echo '<li class="list-group-item"><a href="'.$cateList['url'].'" title="'.$cateList['name'].'">'.$cateList['name'].'</a></li>';
                                }
                                
                              }
                              echo '</ul>';
                            }                          
                            
                          }
                        ?>
                  </aside>
                </div>
              </div>
              <div :class="setClass('main')">
                <div class="card">
                  <div class="card-body article-text">
                    <!--文章內容-->
                    <div class="tab-content">
                      <section class="tab-pane fade show active">
                        <h2 class="card-title"><?php echo $contain['articleData'][0]['title']; ?></h2>
                        <p class="card-text">
                          <?php echo $contain['articleData'][0]['content']; ?>
                        </p>
                      </section>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>

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
      desc:null,
      showMenu:<?php if(!$contain['articleData'][0]['meta']['showMenu']){echo true;}else{echo $contain['articleData'][0]['meta']['showMenu'];} ?>,
      form:{product:null,name:null,email:null,company:null,area:null,specify:null,business:[],others:null,request:null,token:null,type:'company',otherschk:false},
      
      ctn:'<?php echo $this->Api_common->stringHash("encrypt", date("YmdHi"));?>'
    },
    
    methods:{
      submit: function (e) {
        //submit('#sys-msg',window.location.href+'/portal/test','json',app.$data);
        //e.preventDefault();
      },
      callAjax: function (data){
        if(window.location.href.indexOf("article")>0){
          data['type'] = 'article';
        }else if(window.location.href.indexOf("category")>0){
          data['type'] = 'category';
        }
        submit('#hide-msg',window.location.href.replace(/article|category/g,'data'),'json',data,'',function(res){
            if(res.code!=200){
              window.location.href = "<?php echo base_url(); ?>";
            }else{
              app.$data['form']['product'] = "<?php echo $title.' > '.$cate; ?>";
              app.setInput(res);
              setTimeout(function(){     
                if(app.articleData[0]['slider']){
                  $("#article-slider").camRollSlider();
                }
              }, 50);
              
              if(app.articleData[0]['type']=='product'){
                app.$data['form']['request'] = "<?php echo $title.' > '.$cate; ?>";
              }
              if(app.$data['articleData'][0]['meta']['showMenu']=='false'){
                app.$data['showMenu'] = false;
              }
            }

          });
      },
      setClass: function (type){
        main = 'order-last order-lg-2 article-container ';
        menu = 'order-lg-1 article-menu ';
        if(this.$data['showMenu']){
          if(type=='main'){
            return main+'col-12 col-sm-12 col-md-12 col-lg-10 col-xl-10';
          }else{
            return menu+'col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2';
          }
        }else{
          if(type=='main'){
            return main+'col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12';
          }
        }
      },
      setInput: function (res){
        $.each(res.data, function(index) {
            app.$data[index] = res.data[index];
        });
      },
      setLeft: function (res){
        $.each(app.$data['leftmenu'], function(index) {
          year = app.$data['leftmenu'][index]['name'];
          app.$data['leftmenu'][year] = app.$data['leftmenu'][index];
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
      .grecaptcha-badge{
        display: none;
      }
</style>