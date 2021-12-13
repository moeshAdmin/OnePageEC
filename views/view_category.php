<header><h1 style="position: absolute;top:0;color: #fff;"><?php echo $title; ?></h1></header>
<style>
<?php if(LANG=='JP'){echo 'body,a,h1,h2,h3,h4,h5,h6,p,.top-announce-title>h2,.top-announce-title>h3,.top-announce-title>h1{font-family: Meiryo,sf-ui-display,微軟正黑體;}';} ?>
</style>
<div id="pg-main">
  <front-banner :data="parentCate"></front-banner>
  <div class="album animated fadeIn delay-2s">
      <div class="container main ">
            <div class="row">
              <div :class="setClass('main')">
                <div class="card shadow-sm">
                  <div class="article-breadcrumb d-none d-md-block" v-html="breadcrumb"></div>
                  <div style="overflow:hidden;max-height: 223px;" v-show="showBanner">
                    <img v-bind:src="banner" class="card-img-top" width="223px">
                  </div>
                  <!--分類清單-->
                  <article class="card-body article-text">
                        <div class="row" v-for="cateContent in leftmenu">
                            <!--ITEM清單-->
                            <div class="col-12 col-md-4" v-if="cateContent.name==title&&template=='cate-list'" v-for="contentItem in cateContent.children">
                              <section class="card mb-4 product-list" style="height:auto;">
                                <div class="card-img border">
                                  <a v-bind:href="contentItem.url"><img style="max-height: 100px" v-bind:src="contentItem.iconurl"></a>
                                </div>
                                <div class="card-body" style="text-align: center;font-weight: bold;padding:5px">
                                  <a v-bind:href="contentItem.url">{{contentItem.name}}</a>
                                </div>
                              </section>
                            </div>
                            <!--產品線清單 product-line-list-->
                            <div class="col-12" v-if="cateContent.name==title&&template=='product-line-list'" v-for="contentCate in cateContent.children">
                              <product-line-list :data="contentCate" ></product-line-list>
                            </div>
                            <div class="col-12" v-if="template=='product-line-list-no-menu'" v-for="contentCate in cateContent.children">
                              <product-line-list-no-menu :data="contentCate" ></product-line-list-no-menu>
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-4" v-if="template=='product-line-no-menu-banner-only'" v-for="contentCate in cateContent.children">
                              <product-line-no-menu-banner-only :data="contentCate" ></product-line-no-menu-banner-only>
                            </div>
                            <!--產品清單 product-list-->
                            <div v-if="cateContent.name==title&&template=='product-list'" >
                              <product-list :data="cateContent" ></product-list>
                            </div>
                            
                            <!--新聞清單 news-list-->
                            <div class="col-12" v-if="cateContent.name==title&&template=='news-list'">
                              <news-list :data="cateContent" ></news-list>
                            </div>
                        </div>
                  </article>
                </div>
              </div>
              <div :class="setClass('menu')" v-show="showMenu">
                <div class="card border-light shadow-sm p-3 mb-5 rounded">
                  <div class="card-header cate-header active" v-if="parentCate.parentID!=0">{{title}}</div>
                  <aside><left-menu :data="leftmenu" :type="type"></left-menu></aside>
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
      this.callAjax(this.$data);
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
      template:null,
      showMenu:true,
      showBanner:true,
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
              app.setInput(res);
              setTimeout(function(){
                if($(window).width()<991){
                  init.menuCollapse();
                }else{
                  $('#expand-btn').hide();
                }
              }, 50);
            }
            if(app.$data['template'].indexOf("no-menu")>0){
              app.$data['showMenu'] = false;
            }
            if(app.$data['parentCate']['imgurl']==app.$data['banner']||app.$data['banner']==''){
              app.$data['showBanner'] = false;
            }
          });
      },
      setClass: function (type){
        main = 'order-last order-lg-1 article-container ';
        menu = 'order-lg-2 article-menu ';
        if(this.$data['showMenu']){
          if(type=='main'){
            return main+'col-12 col-sm-12 col-md-12 col-lg-9 col-xl-9';
          }else{
            return menu+'col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3';
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
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      }
    }
  });
</script>

