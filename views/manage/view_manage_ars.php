<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/datatables.min.js';?>"></script>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/DataTables-1.10.18/js/dataTables.bootstrap4.min.js';?>"></script>
  <link rel="stylesheet" href="<?php echo base_url().'assets/datatable/DataTables-1.10.18/css/dataTables.bootstrap4.min.css';?>" />
<transition name="container-fade">
<div id="page" class="container-fluid">
      <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
          <div class="search-area"></div>
          
          <div class="row">
            <div id="right" class="col-md-12">
              <div class="table-responsive">
                <table id="main-table" class="table table-hover" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">管理</th>
                      <th scope="col">合約編號</th>
                      <th scope="col">配送資訊</th>
                      <th scope="col">配送方案</th>
                      <th scope="col">進度</th>
                      <th scope="col">下次配送日</th>
                      <th scope="col">訂單金額</th>
                      <th scope="col">收貨資訊</th>
                      <th scope="col">內部備註</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object" :style="setOrderStyle(value.eaARSStatus)">
                      <td>{{ value.eaSysID }}</td>
                      <td></td>
                      <td>
                        {{value.eaARSOrderNo}}<br>
                        [{{value.eaARSStatus}}]
                        <span><a href="#" @click="getPayData(value.eaARSOrderNoHash)">付款紀錄</a></span>
                      <td>
                        {{value.eaReceiverName}} {{value.eaReceiverPhone}}<br>
                        {{value.eaReceiverPostCode}}{{value.eaReceiverAddr}}<br>
                        {{value.eaReceiverEmail}}
                      </td>
                      <td>
                        {{ value.eaItemName }}<br>
                        {{ value.eaItemType }}
                      </td>
                      <td>
                        第 {{value.eaARSPeriods}} 期/共 {{value.eaARSPeriodsTotal}} 期<br>
                        <span><a href="#" @click="getOrderList(value.detail)">查詢紀錄</a></span>
                      </td>
                      <td>
                        <span v-if="value.eaRequestDeliverDate" style="color:red">客戶要求:{{value.eaRequestDeliverDate}}</span>
                        <span v-else>{{value.eaNextDeliverDate}}</span>
                      </td>
                      <td></td>
                      <td>{{ value.eoReceiverName }}<br>{{ value.eoReceiverEmail }} {{ value.eoReceiverPhone }}<br>{{ value.eoReceiverPostCode }}{{ value.eoReceiverAddr }}
                      </td>
                      <td>
                        <span v-if="value.eaInnerNotes">{{ value.eaInnerNotes }}<br></span>
                        <span><a href="#" @click="editOrder(value.eaARSOrderNoHash)">變更訂單</a></span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
    </div>

  <!-- modal 變更訂單-->
  <div class="modal fade" id="edit-order" tabindex="-1" role="dialog" aria-labelledby="edit-order" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit-order">變更訂單</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="edit-msg"></div>
          <div id="edit-area">
            <form>
              <div class="form-group row">
                <div class="col-md-6">
                  <label>收件人</label>
                  <input type="text" class="form-control" v-model="orderData.eaReceiverName">
                </div>
                <div class="col-md-6">
                  <label>連絡電話</label>
                  <input type="text" class="form-control" v-model="orderData.eaReceiverPhone">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-2">
                  <label>郵遞區號</label>
                  <input type="text" class="form-control" v-model="orderData.eaReceiverPostCode">
                </div>
                <div class="col-md-10">
                  <label>收件地址</label>
                  <input type="text" class="form-control" v-model="orderData.eaReceiverAddr">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>預計下次配送日</label>
                  <input type="text" class="form-control disabled" disabled v-model="orderData.eaNextDeliverDate" >
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>客戶要求下次配送日</label>
                  <input id="eaRequestDeliverDate" type="text" class="form-control datepicker" v-model="orderData.eaRequestDeliverDate" onchange="app.changeValue('eaRequestDeliverDate');">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>內部備註</label>
                  <input type="text" class="form-control" v-model="orderData.eaInnerNotes">
                </div>
              </div>
            </form>
          </div>
          
        </div>
        <div class="modal-footer">
          <div class="btn btn-info btn-sm" @click="manageBtn('editOrder','')">送出</div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
  <!-- modal 查詢紀錄-->
  <div class="modal fade" id="query-order" tabindex="-1" role="dialog" aria-labelledby="query-order" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">查詢紀錄</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="query-area">
            <table class="table table-hover" style="font-size: 10pt;">
              <thead>
                <th>期數</th>
                <th>訂單編號</th>
                <th>訂單狀態</th>
                <th>發票狀態</th>
                <th>配送日期</th>
                <th>配送資訊</th>
              </thead>
              <tbody>
                <tr v-for="data in queryData">
                  <td>{{data.eoARSPeriods}}</td>
                  <td>{{data.eoOrderNo}}</td>
                  <td>{{data.eoOrderStatus}}</td>
                  <td>{{data.eoInvoiceStatus}}</td>
                  <td>{{data.eoPlainShipDate}}</td>
                  <td>
                    {{data.eoReceiverName}} {{data.eoReceiverPhone}}<br>{{data.eoReceiverPostCode}}{{data.eoReceiverAddr}}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
  <!-- modal 查詢金流-->
  <div class="modal fade" id="pay-order" tabindex="-1" role="dialog" aria-labelledby="pay-order" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">付款紀錄</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="pay-area">
            卡號: {{payData.card6no}}******{{payData.card4no}}<br>
            總授權金額: {{payData.TotalSuccessAmount}}<br>
            授權次數: {{payData.TotalSuccessTimes}}<br>
            <table class="table table-hover" style="font-size: 10pt;">
              <thead>
                <th>授權結果</th>
                <th>綠界編號</th>
                <th>金額</th>
                <th>日期</th>
              </thead>
              <tbody>
                <tr v-for="data in payData.ExecLog">
                  <td>{{data.RtnMsg}}</td>
                  <td>{{data.TradeNo}}</td>
                  <td>{{data.amount}}</td>
                  <td v-html="data.process_date.replace('%20', ' ')"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
</div>
</transition>

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
        setTimeout(function(){
          app.load(app.$data,'');
        }, 50); 
      },
      data: {
        msgData:{},
        filterData:{
          dateFrom:'<?php echo date('Y-m-d',strtotime('-14 days',strtotime(date('Ymd')))); ?>',
          dateTo:'<?php echo date('Y-m-d',strtotime('-0 days',strtotime(date('Ymd')))); ?>',
          arsOrderID:'<?php echo $_GET["hash"]; ?>',
          export:''
        },
        textMsg:'',
        outputTable:'',
        queryData:{},
        payData:{},
        hash: '',
        object: {},
        orderData: {}
      },
      
      methods:{
        load: function (data,type){
          this.$data['filterData']['export'] = '';
          submit('hidden',window.location.pathname+'/load/','json',this.$data['filterData'],'',function(res){
              app.setInput(res);
              if ($.fn.DataTable.isDataTable('#main-table')) {
                $('#main-table').DataTable().destroy();
              }
              setTimeout(function(){
                var tableObject = 
                $('#main-table').DataTable({
                    "order": [[ 2, "desc" ],[ 0, "desc" ]],
                    "bFilter": true,
                    "bInfo": false,
                    "bAutoWidth": false,"searching":true
                });
                init.tableConfig(tableObject);
                $('.datepicker').datetimepicker({format:'Y-m-d',timepicker:false});
              }, 50); 
          }); 
        },
        setInput: function (res){
          $.each(res.data, function(index) {
              app.$data[index] = res.data[index];
          });
        },
        editOrder: function(hash){
          app.$data['hash'] = hash;
          submit('hidden',window.location.pathname+'/load/'+hash,'json','none','',function(res){
            app.setInput(res);
            $('#edit-order').modal('toggle');
          });
        },
        getOrderList: function(detail){
          app.$data['queryData'] = detail;
          $('#query-order').modal('toggle');
        },
        getPayData: function(hash){
          submit('#sys-msg','manage_ars/queryARS','json',{'hash':hash},'',function(res){
            if(res.code=='200'){
              app.$data['payData'] = res.data;
              console.log(res.data);
              $('#pay-order').modal('toggle');
            }
          }); 
        },
        manageBtn: function(type,hash){
          if(hash){
            app.$data['hash'] = hash;
          }
          if(type=='editOrder'){
            var txt;
            var r = confirm("即將變更訂單內容，請確認?");
            if (r == true) {
              submit('#sys-msg','manage_ars/editOrder','json',app.$data['orderData'],'',function(res){
                if(res.code=='200'){
                  app.$data['hash'] = '';
                  app.load(app.$data,'');
                  $('#edit-order').modal('toggle');
                }else{
                  alert(res.msg);
                }
              }); 
            } else {
              txt = "取消發送";
            }
          }
        },
        setOrderStyle:function(status){
          if(status=='待出貨'){
            return 'background:#fcc';
          }else if(status=='已取消'){
            return 'opacity: 0.4';
          }
        },
        FormatNumber:function(num){
          var parts = num.toString().split('.');
          parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
          return parts.join('.'); 
        },
        changeValue:function(type){
          if(type=='eaRequestDeliverDate'){
            app.$data['orderData']['eaRequestDeliverDate'] = $('#eaRequestDeliverDate').val();
          }else{
            app.$data['filterData'][type] = $('#'+type).val();
          }
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
