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
            <h1 class="h2">SMS 簡訊發送</h1>
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
                        <select class="form-control" name="runType" v-model="search.cate">
                          <option value=''>選擇 SMS 類型</option>
                          <option value='hey_care_7'>黑松_7日關懷簡訊</option>
                          <option value='wp_care_7'>好菌家_7日關懷簡訊</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </form>
                <div class="row justify-content-md-center">
                  <div class="col col-lg-2"></div>
                  <div class="col-md-auto">
                    <div id="previewBtn" class="btn btn-info" @click="preview">預覽</div>
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
                <table class="table table-hover" style="width:100%;font-size: 10pt;">
                  <thead><th>發送類型</th><th>內容</th><th>筆數</th><th>狀態</th><th>發送時間</th></thead>
                  <tbody>
                    <tr v-for="data in historyObject">
                      <td>{{data.emSendType}}</td>
                      <td>{{data.emContent}}</td>
                      <td>{{data.count}}</td>
                      <td>{{data.emStatus}}</td>
                      <td>{{data.emSendTime}}</td>
                    </tr>
                  </tbody>
                </table>                
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-12 ">
              <div id="search-result">
                <table class="table table-hover" style="width:100%;font-size: 10pt;">
                  <thead><th>姓名</th><th>訂單</th><th>出貨/收貨日</th><th>內容</th><th>字數</th></thead>
                  <tbody>
                    <tr v-for="data in smsObject">
                      <td>{{data.name}}</td>
                      <td>{{data.orderNo}}</td>
                      <td>{{data.receiveDate}}</td>
                      <td>{{data.text}}</td>
                      <td>{{data.num}}</td>
                    </tr>
                  </tbody>
                </table>
                <div class="row justify-content-md-center">
                  <div class="col col-lg-2"></div>
                  <div class="col-md-auto">
                    <div id="sendBtn" class="btn btn-danger" @click="send" style="display: none;margin-bottom: 100px;">發送簡訊</div>
                  </div>
                  <div class="col col-lg-2"></div>
                </div>
                
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
        this.loadSMSHistory();
      },
      data: {
        search:{type:'preview',cate:''},
        historyObject: {},
        smsObject: {}
      },
      
      methods:{
        loadSMSHistory: function (e) {
          submit('#sys-msg',init.setUrl(window.location.href)+'/getSMSSendHistory','json','none','',function(res){
            if(res.code=="200"){
              app.$data.historyObject = res.data;
            }
          });
        },
        send: function (e) {
          $('#sendBtn').css('display','none');
          submit('#sys-msg',init.setUrl(window.location.href)+'/getSMSData','json',{type:'send',cate:app.$data.search.cate},'',function(res){
            if(res.code=="200"){
              alert('預約完成');
              window.location.reload();
            }
          });
        },
        preview: function (data,hash){
          $('#sendBtn').css('display','none');
          submit('#sys-msg',init.setUrl(window.location.href)+'/getSMSData','json',app.$data.search,'',function(res){
            if(res.code=="200"){
              app.$data.smsObject = res.data;
              $('#sendBtn').css('display','block');
            }
          });
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
