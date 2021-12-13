<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
<div id="page" class="container-fluid">
      <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <div class="input-group mb-2">
                <div class="input-group-prepend">
                  <div class="input-group-text">從</div>
                </div>
                <input id="dateFrom" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('dateFrom');" v-model="filterData.dateFrom">
              </div>
              <div class="input-group mb-2">
                <div class="input-group-prepend">
                  <div class="input-group-text">到</div>
                </div>
                <input id="dateTo" type="text" autocomplete="off" class="form-control datepicker" onchange="app.changeValue('dateFrom');" v-model="filterData.dateTo">
              </div>
              <div id="taskSearch-btn" class="btn btn-info mb-2" @click="load();">查詢</div>
            </div>
          </div>
        
          <div class="row">
                <div class="col-12 col-md-6 col-lg-6 col-xl-3" >
                    <div class="status-item" style="background: #8892d6;border: none;padding: 10px 25px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-12" style="color:#fff;">
                                <span style="font-size: 10px;width: 100%;float: left;font-weight: bold;"><?php echo date('Y-m-d'); ?> 成交金額/件</span>
                                <span style="font-size: 40px;">{{status.今日成交額}}</span>
                                <span style="padding-right:10px;">元</span>
                                <span style="font-size: 40px;"></span>
                                <span style="padding-right:10px;">{{status.今日成交件}}件 均: {{status.今日均單價}}元</span>
                            </div>                           
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-6 col-xl-3" >
                    <div class="status-item" style="background: #45bbe0;border: none;padding: 10px 25px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-8" style="color:#fff;">
                                <span style="font-size: 10px;width: 100%;float: left;font-weight: bold;">待出貨拋檔件</span>
                                <span style="font-size: 40px;">{{status.待拋檔}}</span>
                                <span style="padding-left:10px;">件</span>
                            </div>
                            <div class="col-md-12 col-lg-12 col-xl-4" style="margin: 10px 0px;">
                                <div style="color: #fff;background: #ffffff52;border-radius: 999px;width: 55px;height: 55px;float:right">
                                    <i class="mdi mdi-insert-drive-file" style="font-size: 35px;margin: 10px;"></i>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-6 col-xl-3" >
                    <div class="status-item" style="background: #fb8b4b;border: none;padding: 10px 25px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-8" style="color:#fff;">
                                <span style="font-size: 10px;width: 100%;float: left;font-weight: bold;">待倉庫回應件</span>
                                <span style="font-size: 40px;">{{status.已拋檔}}</span>
                                <span style="padding-left:10px;">件</span>
                            </div>
                            <div class="col-md-12 col-lg-12 col-xl-4" style="margin: 10px 0px;">
                                <div style="color: #fff;background: #ffffff52;border-radius: 999px;width: 55px;height: 55px;float:right">
                                    <i class="mdi mdi-local-shipping" style="font-size: 35px;margin: 10px;"></i>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-6 col-xl-3" >
                    <div class="status-item" style="background: #f06292;border: none;padding: 10px 25px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-8" style="color:#fff;">
                                <span style="font-size: 10px;width: 100%;float: left;font-weight: bold;">發票異常件</span>
                                <span style="font-size: 40px;">{{status.開立錯誤}}</span>
                                <span style="padding-left:10px;">件</span>
                            </div>
                            <div class="col-md-12 col-lg-12 col-xl-4" style="margin: 10px 0px;">
                                <div style="color: #fff;background: #ffffff52;border-radius: 999px;width: 55px;height: 55px;float:right">
                                    <i class="mdi mdi-warning" style="font-size: 35px;margin: 10px;"></i>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>
          </div>
          <hr>
          <h1 class="h2">待處理訂單統計</h1>
          <div class="row">
            <div class="col-12">
              <small>{{filterData.dateFrom}}~{{filterData.dateTo}}</small>
            </div>
            <div class="col-12 col-md-12" >
              <div id="smartwizard" class="sw-main sw-theme-arrows">
                <ul class="nav nav-tabs step-anchor">
                  <li class="nav-item done"><a href="manage_order?type=unpaid" class="nav-link">待付款 {{status.待付款}} 件<br><small>等待客戶付款中</small></a></li>
                  <li :class="'nav-item '+setClass(status.待拋檔)"><a href="manage_order?type=unexport" class="nav-link">待拋檔 {{status.待拋檔}} 件<br><small>訂單尚未拋至物流處理</small></a></li>
                  <li class="nav-item done"><a href="manage_order?type=unresponse" class="nav-link">已拋待回 {{status.已拋檔}} 件<br><small>訂單已拋至物流，待物流回應</small></a></li>
                  <li class="nav-item active"><a href="manage_order?type=shipdone" class="nav-link">在途中 {{status.在途中}} 件<br><small>物流配送商品途中</small></a></li>
                  <li class="nav-item done"><a href="manage_order?type=shipfinish" class="nav-link">已收貨 {{status.已收貨}} 件<br><small>物流確認客戶已收貨</small></a></li>
                </ul>
              </div>
            </div>
            <div class="col-12 col-md-4" ></div>
            <div class="col-12 col-md-6" >
              <div id="smartwizard" class="sw-main sw-theme-arrows">
                <ul class="nav nav-tabs step-anchor">
                  <li class="nav-item done"><a href="manage_order?type=uninv" class="nav-link">待開立 {{status.待開立}} 件<br><small>等待物流確認後開立發票</small></a></li>
                  <li :class="'nav-item '+setClass(status.開立錯誤)"><a href="manage_order?type=inverr" class="nav-link">開立錯誤 {{status.開立錯誤}} 件<br><small>發票開立失敗，請立即確認異常狀況</small></a></li>
                  <li class="nav-item done"><a href="manage_order?type=invsuccess" class="nav-link">已開立 {{status.已開立}} 件<br><small>發票已完成開立</small></a></li>
                </ul>
              </div>
            </div>
          </div>
          <h1 class="h2">訂單統計</h1>
          <div class="row">
            <div class="col-12">
              <small>{{filterData.dateFrom}}~{{filterData.dateTo}}</small>
              <div class="table-responsive">
                <table class="table table-hover table-sm" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">日期</th>
                      <th scope="col">件數</th>
                      <th scope="col">成交金額</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orderTable">
                      <td>{{order.日期}}</td>
                      <td>{{order.件數}}</td>
                      <td>{{order.成交金額}}</td>
                    </tr>
                    <tr>
                      <td>總計</td>
                      <td>{{orderSum['件數']}}</td>
                      <td>{{orderSum['成交金額']}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
    </div>
</div>
        

 

    

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
        this.load();
      },
      data: {
        status: {},
        orderTable: {},
        orderSum:{},
        filterData:{
          dateFrom:"<?php echo date('Y-m-d',strtotime('-14 day',strtotime(date('Y-m-d')))); ?>",
          dateTo:"<?php echo date('Y-m-d',strtotime('-0 day',strtotime(date('Y-m-d')))); ?>"
        }
      },
      
      methods:{
        load: function (){
          submit('#hide-msg',init.setUrl(window.location.href)+'/load/','json',this.$data['filterData'],'',function(res){
            app.$data['status'] = res.data['status'];
            app.$data['orderTable'] = res.data['orderTable'];
            app.$data['orderSum'] = res.data['orderSum'];
            $('.datepicker').datetimepicker({format:'Y-m-d',timepicker:false});
          });
        },
        setClass: function (num){
          if(num>0){
            return 'danger';
          }else{
            return 'active';
          }
        },
        changeValue:function(type){
          app.$data['filterData'][type] = $('#'+type).val();
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
