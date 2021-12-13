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
            <h1 class="h2">名單下載</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 order-md-1">
              <div class="search-area">
                <form id="ship-form" method="post" enctype="multipart/form-data">
                  <div class="form-row align-items-center">
                    <div class="col-lg-12 col-sm-12">
                      <div class="input-group mb-2">
                        <select class="form-control" name="runType" v-model="search.type" @change="reset">
                          <option value=''>選擇 名單 類型</option>
                          <option value='高質量新客_好菌家'>高質量新客_好菌家</option>
                          <option value='已購提醒回購_晚安大組_好菌家'>已購提醒回購_晚安大組_好菌家</option>
                          <option value='已購提醒回購_晚安小組_好菌家'>已購提醒回購_晚安小組_好菌家</option>
                          <option value='喚醒沉睡客_好菌家'>喚醒沉睡客_好菌家</option>
                          <option value='區間內有下單兩次記錄的客戶'>區間內有下單兩次記錄的客戶</option>
                          <option value='R_客單>2000'>R_客單>2000</option>
                          <option value='R_高含金客戶'>R_高含金客戶</option>
                          <option value='R_流失客戶(僅購買一次、超過90日未購買)'>R_流失客戶(僅購買一次、超過90日未購買)</option>
                          <option value='R_買過晚安30且續購'>R_買過晚安30且續購</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6 col-sm-6" v-show="dateInput">
                      <div class="input-group mb-2">
                        <div class="input-group-prepend">
                          <div class="input-group-text">從</div>
                        </div>
                        <input id="date_from" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('date_from');" v-model="search.date_from">
                      </div>
                    </div>
                    <div class="col-lg-6 col-sm-6" v-show="dateInput">
                      <div class="input-group mb-2">
                        <div class="input-group-prepend">
                          <div class="input-group-text">到</div>
                        </div>
                        <input id="date_to" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('date_to');" v-model="search.date_to">
                      </div>
                    </div>
                  </div>
                </form>
                <div class="row justify-content-md-center">
                  <div class="col col-lg-2"></div>
                  <div class="col-md-auto">
                    <div id="downloadBtn" class="btn btn-info" @click="loadList">名單下載</div>
                  </div>
                  <div class="col col-lg-2"></div>
                </div>
              </div>
            </div>
          </div>
          <hr>
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
        setTimeout(function(){
          app.init();
        }, 1000); 
      },
      data: {
        search:{type:'',link:'',date_from:'<?php echo date('Y-m-d',strtotime('-6 months',strtotime(date('Ymd')))); ?>',date_to:'<?php echo date('Y-m-d',strtotime('-0 days',strtotime(date('Ymd')))); ?>'},
        historyObject: {},
        smsObject: {},
        dateInput: false,
        lock:false
      },
      
      methods:{
        init: function(){
          $('.datepicker').datetimepicker({format:'Y/m/d',timepicker:false});
        },
        loadList: function (e) {
          if(app.$data['lock']){return;}
          app.$data['lock'] = true;
          if(app.$data.search.type=='R_客單>2000'){
            $('#search-result').show();
            app.$data['search']['link'] = 'https://ap3.ragic.com/hugePlus/forms/2?embed&sidebar&hidetop&view=106';
            app.$data['lock'] = false;
          }else if(app.$data.search.type=='R_高含金客戶'){
            $('#search-result').show();
            app.$data['search']['link'] = 'https://ap3.ragic.com/hugePlus/forms/1?embed&sidebar&hidetop&view=108';
            app.$data['lock'] = false;
          }else if(app.$data.search.type=='R_流失客戶(僅購買一次、超過90日未購買)'){
            $('#search-result').show();
            app.$data['search']['link'] = 'https://ap3.ragic.com/hugePlus/forms/1?embed&sidebar&hidetop&view=109';
            app.$data['lock'] = false;
          }else if(app.$data.search.type=='R_買過晚安30且續購'){
            $('#search-result').show();
            app.$data['search']['link'] = 'https://ap3.ragic.com/hugePlus/forms/1?embed&sidebar&hidetop&view=110';
            app.$data['lock'] = false;
          }else{
            $('#downloadBtn').addClass('disabled');
            submit('#sys-msg',init.setUrl(window.location.href)+'/downloadList','json',{type:app.$data['search']['type'],date_from:app.$data['search']['date_from'],date_to:app.$data['search']['date_to']},'',function(res){
              app.$data['lock'] = false;
              $('#downloadBtn').removeClass('disabled');
              if(res.code=="200"){
                setTimeout(function(){
                  location.href = res.data['url'][0];
                }, 50); 
              }
            });
          }
        },
        reset:function(){
          $('#search-result').hide();
          app.$data['search']['link'] = '';
          if(app.$data['search']['type']=='區間內有下單兩次記錄的客戶'){
            app.$data['dateInput'] = true;
          }else{
            app.$data['dateInput'] = false;
          }
        },
        changeValue:function(type){
          app.$data['search'][type] = $('#'+type).val();
          console.log(app.$data['search'][type]);
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
