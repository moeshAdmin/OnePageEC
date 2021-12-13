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
          <div class="search-area">
            <form class="form-block">
              <div class="form-row align-items-center">
                <div class="col-lg-5 col-sm-12">
                  <div id="filter">
                    <div class="form-row">
                      <div class="col-md-2">
                        <label>出貨狀態</label>
                      </div>
                      <div class="col-md-10">
                        <div class="custom-control custom-checkbox custom-control-inline">
                          <input type="checkbox" id="shipStatus1" name="shipStatus" class="custom-control-input" value="待出貨" v-model="filterData.shipStatus" checked="checked">
                          <label class="custom-control-label" for="shipStatus1">待出貨</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                          <input type="checkbox" id="shipStatus2" name="shipStatus" class="custom-control-input" value="已出貨" v-model="filterData.shipStatus">
                          <label class="custom-control-label" for="shipStatus2">已出貨</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                          <input type="checkbox" id="shipStatus3" name="shipStatus" class="custom-control-input" value="已取消" v-model="filterData.shipStatus">
                          <label class="custom-control-label" for="shipStatus3">已取消</label>
                        </div>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col-md-2">
                        <label>付款狀態</label>
                      </div>
                      <div class="col-md-10">
                        <div class="custom-control custom-checkbox custom-control-inline">
                          <input type="checkbox" id="payStatus1" name="payStatus" class="custom-control-input" value="待付款" v-model="filterData.payStatus">
                          <label class="custom-control-label" for="payStatus1">待付款</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                          <input type="checkbox" id="payStatus2" name="payStatus" class="custom-control-input" value="已付款" v-model="filterData.payStatus">
                          <label class="custom-control-label" for="payStatus2">已付款</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-2 col-sm-12">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text">商品</div>
                    </div>
                      <select class="form-control" v-model="filterData.itemID">
                        <option value="all">全部商品</option>
                        <?php 
                          foreach ($itemData as $key => $value) {
                            echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
                          }
                        ?>
                      </select>
                  </div>
                </div>
                <div class="col-lg-2 col-sm-12">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text">從</div>
                    </div>
                    <input id="dateFrom" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('dateFrom');" v-model="filterData.dateFrom">
                  </div>
                </div>
                <div class="col-lg-2 col-sm-12">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text">到</div>
                    </div>
                    <input id="dateTo" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('dateTo');" v-model="filterData.dateTo">
                  </div>
                </div>
                <div class="col-lg-1 col-sm-12">
                    <div class="btn btn-info mb-2 btn-block" @click="load('','')">查詢</div>
                </div>
              </div>
            </form>
          </div>
          
          <div class="row">
            <div id="right" class="col-md-12">
              <div v-if="filterData.shipFileStatus=='未拋檔'" v-html="outputTable"></div>
              <div class="btn btn-info mb-2 btn-sm" @click="exportData('excel')" v-if="'<?php echo $_GET['type'] ?>'==''">下載 Excel</div>
              <div class="btn btn-info mb-2 btn-sm" @click="exportData('shipExcel')" v-if="filterData.shipFileStatus=='未拋檔'">下載 [峰潮] 出貨檔</div>              
              <div class="btn btn-info mb-2 btn-sm" @click="manageBtn('confirmInvoice','')" v-if="filterData.invStatus=='未開立'">整批開立發票</div>
              <div class="table-responsive">
                <table id="main-table" class="table table-hover" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">管理</th>
                      <th scope="col">訂單狀態</th>
                      <th scope="col">付款狀態</th>
                      <th scope="col">預計出貨日</th>
                      <th scope="col">購買商品/件數</th>
                      <th scope="col">訂單金額</th>
                      <th scope="col">收貨資訊</th>
                      <th scope="col" v-if="1==2">訊息</th>
                      <th scope="col" v-if="filterData.invStatus">發票資訊</th>
                      <th scope="col" v-if="filterData.shipFileStatus||'<?php echo $_GET['type'] ?>'=='undeliver'||'<?php echo $_GET['type'] ?>'=='shipdone'">物流資訊</th>
                      <th scope="col">內部備註</th>
                      <th v-if="filterData.invStatus!='開立錯誤'">取消</th>
                      <th v-if="filterData.invStatus=='開立錯誤'">重設發票</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object" :style="setOrderStyle(value.eoOrderStatus)">
                      <td>{{ value.eoSysID }}</td>
                      <td>
                        <div v-show="chkShow('待出貨',value)" type="submit" class="btn btn-primary btn-sm" @click="manageBtn('confirmShipping',value.hash)">出貨</div>
                        <div v-if="chkShow('確認付款',value)" type="submit" class="btn btn-primary btn-sm" @click="manageBtn('confirmPaid',value.hash)">確認付款</div>
                      </td>
                      <td>
                        <span v-if="value.eoOrderStatus=='待出貨'" style="color:red">{{ value.eoOrderStatus }}</span>
                        <span v-else>{{ value.eoOrderStatus }}</span>
                        <br><a target="blank" :href="'<?php echo base_url(); ?>ec/EC_Cart/status?orderNo='+value.eoOrderNoHash">{{ value.eoOrderNo }}-{{ value.eoPayRand }}</a>
                        <br>{{ value.eoDate }}
                          <span v-if="value.eoDeliverCode"><br>{{ value.eoDeliverName }}<br>{{ value.eoDeliverCode }}</span>
                          <span v-if="value.eoIsARS=='Y'"><br>第{{ value.eoARSPeriods }}期/共{{ value.eoARSPeriodsTotal }}期 <a target="blank" :href="'<?php echo base_url(); ?>manage/manage_ars?hash='+value.eoARSOrderNoHash">[查看合約]</span>
                        </td>
                      <td>{{ value.eoPayType }}<br>{{ value.eoPayStatus }}<br>
                        <span v-if="value.eoPayAmount>0"><b v-html="'$'+FormatNumber(value.eoPayAmount)"></b></span>
                        <span v-if="chkShow('查詢付款狀態',value)"><br><a href="#" @click="manageBtn('chkOrderIsPay',value.hash)">查詢付款狀態</a></span>
                      </td>
                      <td>{{ value.eoPlainShipDate }}</td>
                      <td>
                        <div v-for="items in value.detail">
                          {{ items.eodItemName }} x {{ items.eodItemQty }} 件<br><b>{{ items.eodItemType }}</b>
                        </div>
                      </td>
                      <td v-html="FormatNumber(value.eoOrderAmount)"></td>
                      <td>{{ value.eoReceiverName }}<br>{{ value.eoReceiverEmail }} {{ value.eoReceiverPhone }}<br>{{ value.eoReceiverPostCode }}{{ value.eoReceiverAddr }}
                        <span v-if="value.eoMemberNote"><br><b>{{ value.eoMemberNote }}</b></span>
                        <span v-if="value.eoShipProcess"><br><b style="color:#3f00ff">{{value.eoShipProcess}}</b></span>
                      </td>
                      <td v-if="1==2">
                        <div class="btn btn-primary btn-sm" v-if="value.msg>0" @click="RequireLoad(value.eoOrderNoHash)">
                         訊息 <span class="badge badge-light">{{value.msg}}</span>
                          <span class="sr-only">unread messages</span>
                        </div>
                        <div class="btn btn-secondary btn-sm" v-else @click="RequireLoad(value.eoOrderNoHash)">
                         訊息 <span class="badge badge-light">0</span>
                          <span class="sr-only">unread messages</span>
                        </div>
                      </td>
                      <td v-if="filterData.invStatus">
                        <span v-if="value.eoInvoiceStatus&&(value.eoInvoiceStatus=='已開立'||value.eoInvoiceStatus=='未開立')">
                          {{value.eoInvoiceStatus}}
                        </span>
                        <span v-else @click="manageBtn('queryInvoiceErr',value.hash)" style="color:red;cursor: pointer">開立錯誤<br>{{value.eoInvoiceStatus}}</span>
                        <br>
                        <a href="#" v-if="value.eoInvoiceNo" @click="manageBtn('queryInvoice',value.eoInvoiceNo)">{{ value.eoInvoiceNo }}</a> 
                        {{ value.eoInvoiceMeta }}<br>{{value.eoInvoiceComNo}}{{value.eoInvoiceCom}}
                      </td>
                      <td v-if="filterData.shipFileStatus||'<?php echo $_GET['type'] ?>'=='undeliver'||'<?php echo $_GET['type'] ?>'=='shipdone'">
                        {{value.eoShipProcess}}<br>
                        <b v-if="value.eoDeliverCode">配送編號:{{value.eoDeliverCode}}</b>
                      </td>
                      <td>
                        {{ value.eoInnerNote }}<br>
                        <span><a href="#" @click="editOrder(value.hash)" v-show="chkShow('變更訂單',value)">變更訂單</a></span>
                      </td>                      
                      <td v-if="filterData.invStatus!='開立錯誤'">
                        <div v-show="chkShow('取消',value)" class="btn btn-danger btn-sm" @click="manageBtn('cancelOrder',value.hash)">取消</div>
                        <div v-show="chkShow('退貨',value)" class="btn btn-danger btn-sm" @click="manageBtn('retOrder',value.hash)">退貨</div>
                      </td>
                      <td v-if="filterData.invStatus=='開立錯誤'">
                        <div class="btn btn-danger btn-sm" @click="manageBtn('resetInvoice',value.hash)">重設發票</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
    </div>

  <!-- modal -->
  <div class="modal fade" id="require" tabindex="-1" role="dialog" aria-labelledby="require" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="require">訂單異動要求</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="require-msg"></div>
          <div id="require-area" style="height: 300px;overflow-y: scroll;">
            <div class="msg" v-for="msgData in msgData">
              <div v-if="msgData.type=='require'">
                <div class="author">{{msgData.author}}</div>
                <div class="return-msg" v-html="msgData.text">
                  <div class="msg-time return-time">{{msgData.time}}</div>
                </div>
                
              </div>
              <div v-if="msgData.type=='return'">
                <div class="author">{{msgData.author}}</div>
                <div class="require-msg">{{msgData.text}}
                  <div class="msg-time require-time">{{msgData.time}}</div>
                  <div v-if="msgData.isNotifiy=='N'" class="msg-time require-time" style="margin-top: -25px;margin-left: -143px;"><b>訊息尚未通知客戶</b></div>
                </div>
                
              </div>
            </div>
          </div>
          <hr>
          <form>
            <div class="row">
              <div class="col-10">
                <textarea class="form-control" rows="3" v-model="textMsg" placeholder="在這裡回應客戶需求"></textarea><span style="color:#ccc;font-size: 10pt;">Ctrl+Enter 快速送出</span>
              </div>
              <div class="mt-3 mb-4">
                <div class="btn btn-info btn-sm mb-1" id="submitOrder"  @click="RequireReturnSubmit(requireOrderNo)">送出</div><br>
                <div class="btn btn-info btn-sm" @click="manageBtn('sendNotifiy',requireOrderNo)">Email 通知</div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->

  <!-- modal -->
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
                <div class="col-md-2">
                  <label>收件人</label>
                  <input type="text" class="form-control" v-model="orderData.eoReceiverName">
                </div>
                <div class="col-md-5">
                  <label>連絡電話</label>
                  <input type="text" class="form-control" v-model="orderData.eoReceiverPhone">
                </div>
                <div class="col-md-5">
                  <label>連絡信箱</label>
                  <input type="text" class="form-control" v-model="orderData.eoReceiverEmail">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-2">
                  <label>郵遞區號</label>
                  <input type="text" class="form-control" v-model="orderData.eoReceiverPostCode">
                </div>
                <div class="col-md-10">
                  <label>收件地址</label>
                  <input type="text" class="form-control" v-model="orderData.eoReceiverAddr">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>發票類型</label>
                  <select class="custom-select" v-model="orderData.isComInv">
                    <option value="false">個人電子發票</option>
                    <option value="true">三聯式發票</option>
                  </select>
                </div>                
              </div>
              <div class="form-group row" v-if="orderData.isComInv=='false'">
                <div class="col-md-6">
                  <label>載具類型</label>
                  <select class="custom-select" v-model="orderData.eoInvoiceType">
                    <option value="">會員載具</option>
                    <option value="3J0002">手機條碼</option>
                    <option value="CQ0001">自然人憑證</option>
                  </select>
                </div>
                <div class="col-md-6" v-if="orderData.eoInvoiceType">
                  <label>載具資訊</label>
                  <input type="text" class="form-control" v-model="orderData.eoInvoiceMeta">
                </div>
              </div>
              <div class="form-group row" v-if="orderData.isComInv=='true'">
                <div class="col-md-6">
                  <label>統編</label>
                  <input type="text" class="form-control" v-model="orderData.eoInvoiceComNo">
                </div>
                <div class="col-md-6">
                  <label>公司名稱</label>
                  <input type="text" class="form-control" v-model="orderData.eoInvoiceCom">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>預計出貨日</label>
                  <input id="eoPlainShipDate" type="text" class="form-control datepicker" v-model="orderData.eoPlainShipDate" onchange="app.changeValue('eoPlainShipDate');">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-12">
                  <label>內部備註</label>
                  <input type="text" class="form-control" v-model="orderData.eoInnerNote">
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
</div>
</transition>

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
        this.$data['filterData']['payStatus'] = [''];
        this.$data['filterData']['shipStatus'] = [''];
        this.$data['filterData']['invStatus'] = '';
        this.$data['filterData']['shipFileStatus'] = '';
        if('<?php echo $_GET['type'] ?>'=='unpaid'){
          this.$data['filterData']['payStatus'] = ['待付款'];
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='undeliver'){
          this.$data['filterData']['shipStatus'] = ['待出貨'];
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='shipdone'){
          this.$data['filterData']['shipStatus'] = ['待出貨','已出貨'];
          this.$data['filterData']['shipFileStatus'] = '已出貨';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='uninv'){
          this.$data['filterData']['invStatus'] = '未開立';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='invsuccess'){
          this.$data['filterData']['invStatus'] = '已開立';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='inverr'){
          this.$data['filterData']['invStatus'] = '開立錯誤';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='unexport'){
          this.$data['filterData']['shipFileStatus'] = '未拋檔';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='unresponse'){
          this.$data['filterData']['shipFileStatus'] = '已拋未回應';
          $('#filter').css('display','none');
        }else if('<?php echo $_GET['type'] ?>'=='shipfinish'){
          this.$data['filterData']['shipFileStatus'] = '已收貨';
          $('#filter').css('display','none');
        }
        setTimeout(function(){
          app.load(app.$data,'');
        }, 50); 
      },
      data: {
        msgData:{},
        filterData:{
          dateFrom:'<?php echo date('Y-m-d',strtotime('-14 days',strtotime(date('Ymd')))); ?>',
          dateTo:'<?php echo date('Y-m-d',strtotime('-0 days',strtotime(date('Ymd')))); ?>',
          payStatus:[''],
          shipStatus:[''],
          invStatus:'',
          shipFileStatus:'',
          itemID:'all',
          export:''
        },
        textMsg:'',
        outputTable:'',
        hash: '',
        requireOrderNo:'',
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
          if(this.$data['filterData']['shipFileStatus']=='未拋檔'){
            submit('hidden',window.location.pathname+'/outputTable','json',this.$data['filterData'],'',function(res){
              app.$data['outputTable'] = res.data['table'];
            });
          }
        },
        exportData:function(type){          
          if(type=='shipExcel'){
            setTimeout(function(){
              app.$data['filterData']['export'] = 'shipExcel';
              submit('#sys-msg',window.location.pathname+'/load/','json',app.$data['filterData'],'',function(res){
                if(res.code=='200'&&res.data['url'][0]){
                  location.href = res.data['url'][0];
                }else if(!res.data['url'][0]){
                  alert('一般宅配無資料');
                }else{
                  alert(res.msg);
                }
              });
            }, 50); 
            setTimeout(function(){
              app.$data['filterData']['export'] = 'shipExcel_cvs';
              submit('#sys-msg',window.location.pathname+'/load/','json',app.$data['filterData'],'',function(res){
                if(res.code=='200'&&res.data['url'][0]){
                  setTimeout(function(){
                    location.href = res.data['url'][0];
                    app.load(app.$data,'');
                  }, 1000);
                }else if(!res.data['url'][0]){
                  alert('超取無資料');
                }else{
                  alert(res.msg);
                }
              });
            }, 1000); 
          }else{
            this.$data['filterData']['export'] = type;
            submit('#sys-msg',window.location.pathname+'/load/','json',this.$data['filterData'],'',function(res){
              if(res.code=='200'){
                $.each(res.data['url'], function(index) {
                  setTimeout(function(){
                    if(res.data['url'][index]){
                      location.href = res.data['url'][index];
                    }
                  }, 150*index); 
                  setTimeout(function(){
                    app.load(app.$data,'');
                  }, 1000);
                });
              }else{
                alert(res.msg);
              }
            });
          }
          
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
        manageBtn: function(type,hash){
          if(hash){
            app.$data['hash'] = hash;
          }
          if(type=='cancelOrder'){
            var msg = prompt("請輸入取消原因", "客戶要求取消");
            if (msg == null || msg == "") {
            } else {
              submit('#sys-msg','manage_order/cancelOrder','json',{'hash':app.$data['hash']},'',function(res){
                if(res.code=='200'){
                  setTimeout(function(){
                    app.$data['hash'] = '';
                    app.load(app.$data,'');
                  }, 150); 
                }else{
                  alert(res['showMsg']);
                }
              }); 
              
            }
          }else if(type=='retOrder'){
            var msg = prompt("請輸入退貨原因", "客戶要求退貨");
            if (msg == null || msg == "") {
            } else {
              submit('#sys-msg','manage_order/retOrder','json',{'hash':app.$data['hash']},'',function(res){
                if(res.code=='200'){
                  setTimeout(function(){
                    app.$data['hash'] = '';
                    app.load(app.$data,'');
                  }, 150); 
                }else{
                  alert(res['showMsg']);
                }
              });
            }
          }else if(type=='confirmShipping'){
            var msg = prompt("請輸入託運業者", "郵局");
            if (msg == null || msg == "") {

            } else {
              var msg2 = prompt("請輸入託運單號", "");
              if (msg2 == null || msg2 == "") {
              } else {
                submit('#sys-msg','manage_order/confirmShipping','json',{'hash':app.$data['hash'],'deliverName':msg,'deliverCode':msg2},'',function(res){
                  if(res.code=='200'){
                    app.$data['hash'] = '';
                    app.load(app.$data,'');
                  }
                }); 
              }
            }
          }else if(type=='confirmPaid'){
            var msg = prompt("請輸入付款日期", "<?php echo date('Y-m-d'); ?>");
            if (msg == null || msg == "") {
              alert('請輸入付款日期');
            } else {
              var msg2 = prompt("請輸入付款備註 (末五碼..etc)", "");
                submit('#sys-msg','manage_order/confirmPaid','json',{'hash':app.$data['hash'],'payDate':msg,'payNote':msg2},'',function(res){
                  if(res.code=='200'){
                    app.$data['hash'] = '';
                    app.load(app.$data,'');
                  }
                }); 
            }
          }else if(type=='confirmInvoice'){
            var txt;
            var r = confirm("即將整批開立發票，是否確認?");
            if (r == true) {
              submit('#sys-msg','manage_order/confirmInvoice','json',{'confirm':true},{empID:'<?php echo $user_detail['empID']; ?>',pageID:'invoice'},function(res){
                if(res.code=='200'){
                  app.load(app.$data,'');
                }else{
                  alert(res.msg);
                }
              }); 
            } else {
              txt = "取消";
            }
          }else if(type=='sendNotifiy'){
            var txt;
            var r = confirm("即將發送通知訊息給客戶，請確認是否發送?");
            if (r == true) {
              submit('#sys-msg','manage_order/sendNotifiy','json',{'orderNoHash':app.$data['hash']},'',function(res){
                    if(res.code=='200'){
                      app.load(app.$data,'');
                      app.RequireLoad(app.$data['hash']);
                    }else{
                      alert(res.msg);
                    }
              }); 
            } else {
              txt = "取消發送";
            }
          }else if(type=='editOrder'){
            var txt;
            var r = confirm("即將變更訂單內容，請確認?");
            if (r == true) {
              submit('#sys-msg','manage_order/editOrder','json',app.$data['orderData'],'',function(res){
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
          }else if(type=='chkOrderIsPay'){
            submit('#sys-msg','manage_order/chkOrderIsPay','json',{'hash':app.$data['hash']},'',function(res){
                app.$data['hash'] = '';
                alert(res['showMsg']);
            }); 
          }else if(type=='queryInvoice'){
            submit('#sys-msg','manage_order/queryInvoice/'+hash,'json',{},'',function(res){
                alert(res['showMsg']);
            }); 
          }else if(type=='queryInvoiceErr'){
            submit('#sys-msg','manage_order/queryInvoiceErr','json',{'hash':app.$data['hash']},'',function(res){
                app.$data['hash'] = '';
                alert(res['showMsg']);
            }); 
          }else if(type=='resetInvoice'){
            submit('#sys-msg','manage_order/resetInvoice','json',{'hash':app.$data['hash']},'',function(res){
                app.$data['hash'] = '';
                app.load(app.$data,'');
            }); 
          }
        },
        setOrderStyle:function(status){
          if(status=='待出貨'){
            return 'background:#fcc';
          }else if(status=='已取消'){
            return 'opacity: 0.4';
          }
        },
        chkShow:function(type,value){
          if(type=='取消'){
            if(value.eoOrderStatus=='已取消'||value.eoOrderStatus=='已出貨'||value.eoOrderStatus=='已退貨'){
              return false;
            }
          }else if(type=='待出貨'){
            if(value.eoOrderStatus!='待出貨'){
              return false;
            }
          }else if(type=='退貨'){
            if(value.eoOrderStatus!='已出貨'){
              return false;
            }
          }else if(type=='查詢付款狀態'){
            if(value.eoPayType=='信用卡'){
              return true;
            }else{
              return false;
            }
          }else if(type=='確認付款'){
            if(value.eoPayType=='現金匯款'&&value.eoPayAmount==0&&value.eoOrderStatus=='待付款'){
              return true;
            }else{
              return false;
            }
          }else if(type=='變更訂單'){
            if(value.eoOrderStatus=='待出貨'||value.eoOrderStatus=='待付款'||(value.eoInvoiceStatus!='未開立'&&value.eoInvoiceStatus!='已開立')){
              return true;
            }else{
              return false;
            }
          }
          return true;
        },
        RequireLoad:function(orderNo){
          submit('hidden','manage_order/RequireLoad','json',{'orderNo':orderNo},'',function(res){
            if(res.code==200){
              $('#require').modal('show');
              app.$data['requireOrderNo'] = orderNo;
              app.$data['textMsg'] = '';
              app.$data['msgData'] = res.data['msgData'];
              setTimeout(function(){ 
                document.getElementById('require-area').scrollTop = document.getElementById('require-area').scrollHeight;
              }, 200);

            }else{
              alert(res.msg);
              return;
            }
          });
        },
        RequireReturnSubmit:function(orderNo){
          submit('hidden','manage_order/RequireReturnSubmit','json',{'textMsg':app.$data['textMsg'],'orderNo':orderNo},'',function(res){
            if(res.code==200){
              app.RequireLoad(orderNo);
            }else{
              //alert(res.msg);
              return;
            }
          });
        },
        FormatNumber:function(num){
          var parts = num.toString().split('.');
          parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
          return parts.join('.'); 
        },
        changeValue:function(type){
          if(type=='eoPlainShipDate'){
            app.$data['orderData']['eoPlainShipDate'] = $('#eoPlainShipDate').val();
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
