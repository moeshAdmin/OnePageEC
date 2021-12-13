<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
<transition name="container-fade">
<div id="page" class="container-fluid">
      <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">報表中心</h1>
          </div>
          <div class="row">
            <div class="col-md-12 order-md-1">
              <div class="search-area">
                <form id="ship-form" method="post" enctype="multipart/form-data">
                  <div class="form-row align-items-center">
                    <div class="col-lg-12 col-sm-12">
                      <div class="input-group mb-2">
                        <select class="form-control" v-model="search.cate" @change="changeReport">
                          <option value=''>選擇 報表 類型</option>
                          <option value='周報表'>周報表</option>
                          <option value='年報表'>年報表</option>
                          <option value='日報表'>日報表</option>
                          <option value='未轉換商品列表'>未轉換商品列表</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 ">
              <div id="search-result">
                <iframe :src="search.link" style="border:0;width:100%;height:70vh"></iframe>
              </div>
            </div>
          </div>
        </main>
    </div>
</div>
</transition>
    

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
      },
      data: {
        search:{type:'preview',cate:'',link:''},
        historyObject: {},
        smsObject: {}
      },
      
      methods:{
        changeReport: function (e) {
          if(app.$data['search']['cate']=='客單>2000'){
            app.$data['search']['link'] = 'https://ap3.ragic.com/hugePlus/forms/2?embed&sidebar&hidetop&view=106';            
          }else{
            app.$data['search']['link'] = '<?php echo FR_DOMAIN_SSL; ?>/webroot/decision/view/report?viewlet=hugePlusFR%252F'+encodeURIComponent(app.$data['search']['cate'])+'.cpt&op=view';
          }
          
          /*
          submit('#sys-msg',init.setUrl(window.location.href)+'/getSMSSendHistory','json','none','',function(res){
            if(res.code=="200"){
              app.$data.historyObject = res.data;
            }
          });*/
        },
        preview: function (e) {
          /*
          submit('#sys-msg',init.setUrl(window.location.href)+'/getSMSSendHistory','json','none','',function(res){
            if(res.code=="200"){
              app.$data.historyObject = res.data;
            }
          });*/
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
