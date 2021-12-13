<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/datatables.min.js';?>"></script>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/DataTables-1.10.18/js/dataTables.bootstrap4.min.js';?>"></script>
  <link rel="stylesheet" href="<?php echo base_url().'assets/datatable/DataTables-1.10.18/css/dataTables.bootstrap4.min.css';?>" />
<transition name="container-fade">
<div class="container-fluid">
      <div id="home" class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $this->page->page_title($page_id,"h1"); ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
          <!--
          <div class="row">
            <div class="col-md-12">
              <h5>最近瀏覽</h5>
              <div class="table-responsive">
                <table class="table table-hover table-sm" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">時間</th>
                      <th scope="col">來源</th>
                      <th scope="col">瀏覽器</th>
                      <th scope="col">目標頁面</th>
                      <th scope="col">來源</th>
                      <th scope="col"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="tableData in visitor">
                      <td>{{tableData.DTime}}</td>
                      <td>{{tableData.from}}</td>
                      <td>{{tableData.browser}}</td>
                      <td>
                        <a target="blank" v-if="tableData.pageID!=''" v-bind:href="tableData.url">{{tableData.pageID}}</a>
                        <a target="blank" v-else="" v-bind:href="tableData.url">{{tableData.urlName}}</a>
                      </td>
                      <td>{{tableData.refer}}</td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-12">
              <h5>最近接收表單</h5>
              <div class="table-responsive">
                <table class="table table-hover table-sm" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">SysID</th>
                      <th scope="col">Type</th>
                      <th scope="col">Name</th>
                      <th scope="col">Email</th>
                      <th scope="col">Company</th>
                      <th scope="col">FromIP</th>
                      <th scope="col">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="tableData in form">
                      <td>{{tableData.csSysID}}</td>
                      <td>{{tableData.csType}}</td>
                      <td>{{tableData.csName}}</td>
                      <td>{{tableData.csEmail}}</td>
                      <td>{{tableData.csCompany}}</td>
                      <td>{{tableData.csFromIP}}</td>
                      <td>{{tableData.csCreateDTime}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-12">
              <h5>最近編輯</h5>
              <div class="table-responsive">
                <table class="table table-hover table-sm" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">時間</th>
                      <th scope="col">人員</th>
                      <th scope="col">來源</th>
                      <th scope="col">模組</th>
                      <th scope="col">目標</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="tableData in manager">
                      <td>{{tableData.DTime}}</td>
                      <td>{{tableData.name}}</td>
                      <td>{{tableData.from}}</td>
                      <td>{{tableData.model}}</td>
                      <td>{{tableData.param}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
            -->
        </main>
    </div>
</div>
</transition>

<script>

  var app = new Vue({
    el: '#home',
    created(){
      //this.callAjax(this.$data);
    },
    data: {
      visitor:null,
      manager:null,
      form:null
    },
    
    methods:{
      submit: function (e) {
      },
      callAjax: function (data){
        submit('#hide-msg',window.location.href+'/accessLog','json',data,'',function(res){
          app.setInput(res);
            if ($.fn.DataTable.isDataTable('.table')) {
              $('.table').DataTable().destroy();
            }
            setTimeout(function(){
              var tableObject = 
              $('.table').DataTable({
                "order": [[ 0, "desc" ]],
                "bLengthChange": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false,"searching":true
              });
              init.tableConfig(tableObject);
            }, 500); 
        });
      },
      setInput: function (res){
        $.each(res.data, function(index) {
            app.$data[index] = res.data[index];
        });
      }
    }
  });
</script>

