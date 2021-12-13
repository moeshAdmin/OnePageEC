<?php 
$user_detail=$this->session->all_userdata(); 
if($itemStatus=='預購中'){
  $status = '<span class="preorder-item">預購商品：</span>';
}else if($itemStatus=='測試商品'){
  $status = '<span style="color:red">測試商品，請勿下單：</span>';
}else{
  $status = '';
}
?>
<header><h1 style="position: absolute;top:0;color: #fff;display: none;"><?php echo $title.' > '.$cate; ?></h1></header>
<div id="pg-main">
  <div class="container animated fadeIn delay-2s">
    <div class="form-group row" v-if="showItemHtml">
      <div class="col-sm-12">
        <?php echo $html; ?> 
      </div>
    </div>
    <div class="form-group row"  @click="showItemHtml = !showItemHtml" >
      <div class="btn btn-info btn-block">
        <span v-if="showItemHtml==false" @click="scrollTop">顯示完整商品介紹</span>
        <span v-if="showItemHtml">隱藏完整商品介紹</span>
      </div>
    </div>
    <div class="row" id="orderForm">
      <h2><?php echo $status; ?><?php echo $title; ?></h2>
      <div class="col-12">
        <div id="article-slider" style="width: 600px;height:600px;color:white;margin: 0 auto;" class="crs-wrap" v-if="'<?php echo $itemImg; ?>'!=''">
            <div class="crs-screen">
              <div class="crs-screen-roll">
                <?php 
                $itemImg = explode(';', $itemImg);
                  foreach ($itemImg as $key => $value) {
                    echo '<div class="crs-screen-item" style="background-image: url('.$value.')"></div>';
                  }
                ?>
              </div>
            </div>
            <div class="crs-bar">
              <div class="crs-bar-roll-current"></div>
                <div class="crs-bar-roll-wrap">
                  <div class="crs-bar-roll">
                    <?php 
                      foreach ($itemImg as $key => $value) {
                        echo '<div class="crs-bar-roll-item" style="background-image: url('.$value.')"></div>';
                      }
                    ?>
                  </div>
                </div>
            </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="form-group row">
        <div class="col-sm-12">
          <?php echo $desc; ?> 
        </div>
      </div>
      <div class="col-12">
        <form>
          <div class="form-group row">
            <h4>商品資訊</h4>
          </div>
          <div class="form-group row">
            <label class="col-sm-2 ">訂購商品</label>
            <div class="col-sm-10">
              <?php echo $title; ?> {{orderDetail.itemTypeName}}
            </div>

          </div>
          <div class="form-group row">
            <label class="col-sm-2 col-form-label">訂購方案</label>
            <div class="col-sm-10">
              <select class="custom-select" v-model="orderDetail.itemType" @change="changePrice">
              <?php 
                  foreach ($itemType as $key => $value) {
                    if($value['disabled']){
                      echo '<option value="'.$value['value'].'" disabled>'.$value['name'].' (售完)</option>';
                    }else{
                      echo '<option value="'.$value['value'].'">'.$value['name'].'</option>';
                    }
                  }

                ?>
              </select>
            </div>
          </div>
          <div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">數量</label>
              <div class="col-sm-10">
                <select class="custom-select" v-model="orderDetail.orderQty">
                  <?php for($i=1;$i<=10;$i++){
                    echo '<option value="'.$i.'">'.$i.' 組</option>';
                  } ?>
                </select>
              </div>
            </div>
            
            <div class="form-group row">
              <label class="col-sm-2"></label>
              <div class="col-sm-10">
                <div style="color:#ccc">原價: <span style="text-decoration: line-through;">{{FormatNumber(orderDetail.itemNPrice)}}</span></div> 
                <span>
                  <span style="background: #f00;color: #fff;padding: 0 15px;" v-if="orderDetail.priceTxt">{{orderDetail.priceTxt}}</span>
                  <span style="background: #f00;color: #fff;padding: 0 15px;" v-else>特價</span>：
                  <span style="font-size: 30px;color: #b00;font-weight: bold;">{{FormatNumber(orderDetail.itemPrice)}}</span> 元</span>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 ">金額小計</label>
              <div class="col-sm-10">
                {{FormatNumber(orderDetail.itemPrice)}} 元 * {{FormatNumber(orderDetail.orderQty)}} 組 = <span>{{FormatNumber(orderDetail.itemPrice*orderDetail.orderQty)}}</span> 元
              </div>
            </div>
          </div>
          <div v-if="'<?php echo $itemSellType; ?>'=='定期配'">
            <div class="form-group row" style="display:none;">
              <label class="col-sm-2 col-form-label">定期配送日</label>
                <div class="col-sm-10 mt-2">
                  <select class="custom-select" v-model="orderDetail.arsDate" @change="changePrice" disabled>
                    <option value="<?php echo date('d'); ?>">每月 <?php echo date('d'); ?> 號</option>
                  </select>
                </div>
            </div>
          </div>
          <hr>
          <div class="form-group row" v-if="'<?php echo $canSale; ?>'==''">
            <div class="col-sm-12" style="text-align: center;" >
              <div class="btn btn-secondary disabled">商品已售完</div>
            </div>
          </div>
          <div class="form-group row" v-else-if="'<?php echo $itemSellType; ?>'=='定期配'&&'<?php echo $isMember; ?>'==''">
            <div class="col-sm-12" style="text-align: center;">
              <a href="<?php echo base_url().'ec/EC_Member?type=regiest&itemID='.$_GET['itemID']; ?>"><div class="btn btn-info">啟動定期配會員</div></a>
            </div>
          </div>
          <div v-else>
            <div id="dt" class="form-group row">
              <h4>配送方式</h4>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">配送方式</label>
                <div class="col-sm-10" v-if="'<?php echo $itemSellType; ?>'!='定期配'">
                  <select class="custom-select" v-model="orderDetail.logisticType">
                    <option value="home">宅配</option>
                    <option value="cvs_pay">超商取貨付款</option>
                    <option value="cvs">超商取貨 (不付款)</option>
                  </select>
                </div>
                <div class="col-sm-10" v-if="'<?php echo $itemSellType; ?>'=='定期配'">
                  <select class="custom-select" v-model="orderDetail.logisticType">
                    <option value="home">宅配</option>
                  </select>
                </div>
            </div>
            
            <div v-if="orderDetail.logisticType=='cvs'||orderDetail.logisticType=='cvs_pay'">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">收件超商</label>
                <div class="col-sm-10" v-if="orderDetail.cvsID">
                  <span style="color:#008ea6;font-weight: bold">{{orderDetail.cvsName}}</span><br>{{orderDetail.cvsAddr}} 
                  <div class="btn btn-info btn-sm" @click="CvsSelect">重新選擇</div>
                  <div style="pointer-events: none;">
                    <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" :src="'https://maps.google.com/maps?q='+orderDetail.cvsName+'&t=&z=18&ie=UTF8&iwloc=B&output=embed'"></iframe>
                  </div>
                </div>
                <div class="col-sm-10" v-else>
                  <div class="btn btn-info btn-sm" @click="CvsSelect">選擇門市</div>
                  <small class="form-text text-muted" style="color:#fd0000!important">*請先選擇好收件門市，再進行資料填寫</small>
                </div>
              </div>
            </div>
            <hr>
            <div v-if="orderDetail.logisticType=='home'||(orderDetail.cvsID)">
              <!--收件資訊-->
              <div class="form-group row">
                <h4>收件資訊</h4>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">收件人</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" placeholder="請輸入收件人姓名"  v-model="orderDetail.name">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">聯絡信箱</label>
                <div class="col-sm-10">
                  <input type="email" class="form-control" placeholder="請輸入聯絡信箱"  v-model="orderDetail.email">
                  <small class="form-text text-muted" style="color:#fd0000!important">*系統會透過Email主動告知訂單狀態，請務必填寫正確</small>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">聯絡電話</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" placeholder="請輸入聯絡電話"  v-model="orderDetail.phone">
                </div>
              </div>
              <div v-if="orderDetail.logisticType=='home'">
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">收件地址</label>
                  <div class="col-sm-2" v-show="false">
                    <input type="text" class="form-control" placeholder="郵遞區號" v-model="orderDetail.postCode">
                  </div>
                  <div class="col-sm-2" v-show="false">
                    <div type="text" class="form-control" v-html="SetZipCode(orderDetail.postCode)"></div>
                  </div>

                  <div class="col-sm-2">
                    <select class="custom-select" v-model="orderDetail.postCode">
                      <option v-for="(data,index,value) in zipCode" :value="index">{{index}} {{data}}</option>
                    </select>
                  </div>
                  <div class="col-sm-8">
                    <input type="text" class="form-control" placeholder="請輸入完整收件地址" v-model="orderDetail.addr">
                  </div>
                </div>
                <div class="form-group row" v-if="1==2">
                  <label class="col-sm-2 col-form-label">收件時段</label>
                  <div class="col-sm-10">
                    <select class="custom-select" v-model="orderDetail.deliverTime">
                      <option value="不指定">不指定</option>
                      <option value="上午">上午 (08:00~12:00)</option>
                      <option value="下午">下午 (12:00~17:00)</option>
                    </select>
                  </div>
                </div>
              </div>          
              <div class="form-group row" v-if="1==2">
                <label class="col-sm-2 col-form-label">訂單備註</label>
                <div class="col-sm-10">
                  <textarea class="form-control" placeholder="有什麼需求嗎?" v-model="orderDetail.note"></textarea>
                </div>
              </div>
              <hr>
              <!--收件資訊-->
              <!--發票資訊-->
              <div class="form-group row">
                <h4>發票資訊</h4>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">載具類型</label>
                <div class="col-sm-10 mt-1">
                  <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="invoice7" name="invoiceType" class="custom-control-input" checked="" v-model="orderDetail.invoiceType" value="會員載具">
                    <label class="custom-control-label" for="invoice7">會員載具</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="invoice5" name="invoiceType" class="custom-control-input" checked="" v-model="orderDetail.invoiceType" value="手機條碼">
                    <label class="custom-control-label" for="invoice5">手機條碼</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="invoice6" name="invoiceType" class="custom-control-input" v-model="orderDetail.invoiceType" value="自然人憑證">
                    <label class="custom-control-label" for="invoice6">自然人憑證</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="invoice8" name="invoiceType" class="custom-control-input" v-model="orderDetail.invoiceType" value="愛心碼">
                    <label class="custom-control-label" for="invoice8">愛心碼</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="invoice9" name="invoiceType" class="custom-control-input" v-model="orderDetail.invoiceType" value="開立統編">
                    <label class="custom-control-label" for="invoice9">開立統編</label>
                  </div>
                </div>
              </div>
              <div class="form-group row" v-if="orderDetail.invoiceType!='開立統編'">
                <label class="col-sm-2 col-form-label">載具資訊</label>
                <div class="col-sm-10">
                  <span v-if="orderDetail.invoiceType=='會員載具'">我們會將發票透過電子信箱寄送給您</span>
                  <input v-if="orderDetail.invoiceType=='手機條碼'" type="text" class="form-control" placeholder="請輸入手機條碼"  v-model="orderDetail.invoiceMeta">
                  <input v-if="orderDetail.invoiceType=='自然人憑證'" type="text" class="form-control" placeholder="請輸入自然人憑證號碼"  v-model="orderDetail.invoiceMeta">
                  <input v-if="orderDetail.invoiceType=='愛心碼'" type="text" class="form-control" placeholder="愛心碼"  v-model="orderDetail.invoiceMeta">
                </div>
              </div>
              <div class="form-group row" v-if="orderDetail.invoiceType=='開立統編'">
                <label class="col-sm-2 col-form-label">開立統編</label>
                <div class="col-sm-2">
                  <input class="form-control" placeholder="統一編號" v-model="orderDetail.invoiceComNo">
                </div>
                <div class="col-sm-8">
                  <input type="text" class="form-control" placeholder="發票抬頭" v-model="orderDetail.invoiceComTitle">
                </div>
              </div>
              <hr>
              <!--發票資訊-->
              <!--付款資訊-->
              <div class="form-group row">
                <h4>付款資訊</h4>
              </div>
              <fieldset class="form-group">
                <!--一般商品付款-->
                <div class="row" v-if="'<?php echo $itemSellType; ?>'!='定期配'">
                  <legend class="col-form-label col-sm-2 pt-0">付款方式</legend>
                  <div class="col-sm-10" v-if="orderDetail.logisticType=='home'||orderDetail.logisticType=='cvs'">
                    <div class="custom-control custom-radio" v-if="'<?php echo in_array('信用卡', $payType); ?>'=='1'">
                      <input type="radio" id="pay1" name="payType"  class="custom-control-input" v-model="orderDetail.payType" value="信用卡">
                      <label class="custom-control-label" for="pay1">信用卡</label>
                      <small class="form-text text-muted" style="color:#fd0000!important">*本系統採綠界科技ECPAY收款，不會保留刷卡資訊，請放心使用</small>
                    </div>
                    <div class="custom-control custom-radio" v-if="orderDetail.logisticType=='home'&&'<?php echo in_array('現金匯款', $payType); ?>'=='1'" style="padding-top: 20px;">
                      <input type="radio" id="pay2" name="payType"  class="custom-control-input" v-model="orderDetail.payType" value="現金匯款">
                      <label class="custom-control-label" for="pay2">現金匯款</label>
                    </div>
                    <div class="custom-control custom-radio" v-if="orderDetail.logisticType=='home'&&'<?php echo in_array('貨到付款', $payType); ?>'=='1'" style="padding-top: 20px;">
                      <input type="radio" id="pay3" name="payType"  class="custom-control-input" v-model="orderDetail.payType" value="貨到付款">
                      <label class="custom-control-label" for="pay3">貨到付款</label>
                    </div>
                  </div>
                  <div class="col-sm-10" v-if="orderDetail.logisticType=='cvs_pay'">
                    <div class="custom-control custom-radio">
                      超商取貨付款
                    </div>
                  </div>
                </div>
                <!--定期配商品-->
                <div class="row" v-if="'<?php echo $itemSellType; ?>'=='定期配'">
                  <legend class="col-form-label col-sm-2 pt-0">付款方式</legend>
                  <div class="col-sm-10">
                    <div class="custom-control custom-radio" v-if="'<?php echo in_array('信用卡', $payType); ?>'=='1'">
                      <input type="radio" id="pay1" name="payType"  class="custom-control-input" v-model="orderDetail.payType" value="信用卡">
                      <label class="custom-control-label" for="pay1">信用卡</label>
                      <small class="form-text text-muted" style="color:#fd0000!important">*本系統採綠界科技ECPAY收款，不會保留刷卡資訊，請放心使用</small>
                    </div>
                  </div>
                </div>
              </fieldset>
              <hr>
              <div class="form-group row" style="display: none;">
                <div class="col-sm-2">付款金額</div>
                <div class="col-sm-10">
                  <span style="font-size: 32pt;color:#008ea6;">{{count}} 元</span>
                </div>
              </div>
              <!--付款資訊-->
            </div>
            <div class="form-group row">
              <div class="col-sm-12" style="text-align: center;">
                <div id="btn1" class="btn btn-info btn1" @click="PreviewOrder()">計算訂單金額</div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-sm-12" style="text-align: center;">
              <small class="form-text text-muted">*本網站採用 reCAPTCHA 識別技術且遵循 Google <a href="https://policies.google.com/privacy">Privacy Policy</a> 與 <a href="https://policies.google.com/terms">Terms of Service</a> 協議。</small>
            </div>
          </div>
          <div id="preview-msg"></div>
        </form>
        
      </div>
    </div>
  </div>

  <div class="scroll-top-btn right-buy-btn">
    <button type="button" class="btn btn-danger btn-sm" @click="scrollToForm">立即<br>訂購</button>
  </div>
  <div class="scroll-top-btn buttom-buy-btn">
    <button type="button" class="btn btn-danger btn-sm" @click="scrollToForm">立即訂購</button>
  </div>
  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>

  <!-- modal -->
  <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="preview" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="preview">訂單確認</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="form-msg"></div>
          請確認以下訂單資訊：
          <table class="table table-hover">
            <tr><td>購買商品</td><td><div id="items"><?php echo $title; ?> {{orderDetail.itemTypeName}}</div></td></tr>
            <tr v-if="'<?php echo $itemSellType; ?>'!='定期配'"><td>金額小計</td><td style="color:red">{{FormatNumber(orderDetail.price)}} 元 * {{orderDetail.orderQty}} 件 + 運費 {{orderDetail.ship}} 元 = {{FormatNumber(orderDetail.price*orderDetail.orderQty+orderDetail.ship)}} 元</td></tr>
            <tr v-if="1==2&&'<?php echo $itemSellType; ?>'!='定期配'"><td>折扣</td><td style="color:red">{{orderDetail.discount}} 元</td></tr>
            <tr v-if="'<?php echo $itemSellType; ?>'!='定期配'"><td>訂單總金額</td><td style="color:red">{{FormatNumber(orderDetail.amount)}} 元</td></tr>
            <tr v-if="'<?php echo $itemSellType; ?>'=='定期配'"><td>每期金額</td><td style="color:red">{{FormatNumber(orderDetail.amount)}} 元</td></tr>
            <tr><td>收件人姓名/信箱</td><td>{{orderDetail.name}} {{orderDetail.email}}</td></tr>
            <tr><td>聯絡電話</td><td>{{orderDetail.phone}}</td></tr>
            <tr><td>收件方式</td>
              <td>
                <span v-if="orderDetail.logisticType=='home'">宅配</span>
                <span v-if="orderDetail.logisticType=='cvs_pay'">超商取貨付款</span>
                <span v-if="orderDetail.logisticType=='cvs'">超商取貨 (不付款)</span>
              </td>
            </tr>
            <tr>
              <td>收件地址</td>
              <td v-if="orderDetail.addr">{{orderDetail.postCode}} {{orderDetail.area}}{{orderDetail.addr}}</td>
              <td v-else>{{orderDetail.cvsName}} {{orderDetail.cvsAddr}}</td>
            </tr>
            <tr><td>發票/付款資訊</td>
              <td>
                <div>{{orderDetail.payType}}</div>
                <div>{{orderDetail.invoiceType}} <div v-if="orderDetail.invoiceType!='會員載具'">{{orderDetail.invoiceMeta}}</div></div>
                <div v-if="orderDetail.invoiceComNo">{{orderDetail.invoiceComNo}} {{orderDetail.invoiceComTitle}}</div>
                <div id="value" style="display: none;">{{orderDetail.amount}}</div>
              </td>
            </tr>
            <tr v-if='1==2'><td>訂單備註</td><td>{{orderDetail.note}}</td></tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">修改訂單</button>
          <button type="button" class="btn btn-info btn2" id="submitOrder"  @click="SubmitOrder()">確認無誤，送出訂單</button>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
</div>




<hr>

<script>
  var app = new Vue({
    el: '#pg-main',
    created: function (){
      if('<?php echo $_GET['logis']; ?>'!=''){this.$data['showItemHtml'] = false;$('body').animate({scrollTop: $("#dt").offset().top}, 600);}
      if('<?php echo $_GET['showItemHtml']; ?>'=='N'){this.$data['showItemHtml'] = false;}
      setTimeout(function(){
        if('<?php echo $_GET['logis']; ?>'){$('body').animate({scrollTop: $("#dt").offset().top}, 600);}
        app.$data['orderDetail'] = {
          itemPrice:'',
          itemNPrice:'',
          itemID:'',
          itemTypeName:'',
          itemType:'',
          name:'<?php echo $user_detail['m_name']; ?>',
          email:'<?php echo $user_detail['m_email']; ?>',
          addr:'',
          phone:'',
          postCode:'',
          token:'',
          orderQty:<?php if($_GET['qty']){echo (int)$_GET['qty'];}else{echo 1;} ?>,
          discount:0,
          amount:0,
          note:'',
          invoiceAddr:'電子發票',
          invoiceType:'會員載具',
          invoiceMeta:'',
          payType:'信用卡',
          deliverTime:'不指定',
          price:0,
          priceTxt:'',
          arsDate:'',
          arsPeriods:"",
          utm_source:'<?php echo $_GET['utm_source']; ?>',
          utm_medium:'<?php echo $_GET['utm_medium']; ?>',
          utm_campaign:'<?php echo $_GET['utm_campaign']; ?>',
          utm_content:'<?php echo $_GET['utm_content']; ?>',
          setting:<?php echo $setting; ?>,
          logisticType:'<?php if($_GET['logis']=='cvs'){echo 'cvs';}else if($_GET['logis']=='cvs_pay'){echo 'cvs_pay';}
            else{echo "home";} ?>',
          <?php if($_POST['CVSStoreID']){echo 'cvsType:"'.$_POST['LogisticsSubType'].'",cvsID:"'.$_POST['CVSStoreID'].'",cvsName:"'.$_POST['CVSStoreName'].'",cvsAddr:"'.$_POST['CVSAddress'].'"';}
            else{echo 'cvsType:"",cvsID:"",cvsName:"",cvsAddr:""';} ?>}
          app.SetDefault();
          app.changePrice();
      }, 100);
    },
    data: {
      orderDetail:{itemPrice:'',itemNPrice:'',itemID:'',itemTypeName:'',itemType:'',name:'',email:'',area:'',addr:'',phone:'',postCode:'',token:'',orderQty:1,discount:0,amount:0,note:'',invoiceAddr:'',invoiceType:'',invoiceMeta:'',payType:'',deliverTime:'',price:0,priceTxt:'',setting:'',logisticType:'',cvsType:'',cvsID:'',cvsName:'',cvsAddr:'',arsDate:'',arsPeriods:""},
      count:0,
      zipCode:{
        100:'臺北市中正區',
        103:'臺北市大同區',
        104:'臺北市中山區',
        105:'臺北市松山區',
        106:'臺北市大安區',
        108:'臺北市萬華區',
        110:'臺北市信義區',
        111:'臺北市士林區',
        112:'臺北市北投區',
        114:'臺北市內湖區',
        115:'臺北市南港區',
        116:'臺北市文山區',
        200:'基隆市仁愛區',
        201:'基隆市信義區',
        202:'基隆市中正區',
        203:'基隆市中山區',
        204:'基隆市安樂區',
        205:'基隆市暖暖區',
        206:'基隆市七堵區',
        207:'新北市萬里區',
        208:'新北市金山區',
        220:'新北市板橋區',
        221:'新北市汐止區',
        222:'新北市深坑區',
        223:'新北市石碇區',
        224:'新北市瑞芳區',
        226:'新北市平溪區',
        227:'新北市雙溪區',
        228:'新北市貢寮區',
        231:'新北市新店區',
        232:'新北市坪林區',
        233:'新北市烏來區',
        234:'新北市永和區',
        235:'新北市中和區',
        236:'新北市土城區',
        237:'新北市三峽區',
        238:'新北市樹林區',
        239:'新北市鶯歌區',
        241:'新北市三重區',
        242:'新北市新莊區',
        243:'新北市泰山區',
        244:'新北市林口區',
        247:'新北市蘆洲區',
        248:'新北市五股區',
        249:'新北市八里區',
        251:'新北市淡水區',
        252:'新北市三芝區',
        253:'新北市石門區',
        260:'宜蘭縣宜蘭',
        261:'宜蘭縣頭城',
        262:'宜蘭縣礁溪',
        263:'宜蘭縣壯圍',
        264:'宜蘭縣員山',
        265:'宜蘭縣羅東',
        266:'宜蘭縣三星',
        267:'宜蘭縣大同',
        268:'宜蘭縣五結',
        269:'宜蘭縣冬山',
        270:'宜蘭縣蘇澳',
        272:'宜蘭縣南澳',
        300:'新竹市',
        302:'新竹縣竹北',
        303:'新竹縣湖口',
        304:'新竹縣新豐',
        305:'新竹縣新埔',
        306:'新竹縣關西',
        307:'新竹縣芎林',
        308:'新竹縣寶山',
        310:'新竹縣竹東',
        311:'新竹縣五峰',
        312:'新竹縣橫山',
        313:'新竹縣尖石',
        314:'新竹縣北埔',
        315:'新竹縣峨眉',
        320:'桃園市中壢區',
        324:'桃園市平鎮區',
        325:'桃園市龍潭區',
        326:'桃園市楊梅區',
        327:'桃園市新屋區',
        328:'桃園市觀音區',
        330:'桃園市桃園區',
        333:'桃園市龜山區',
        334:'桃園市八德區',
        335:'桃園市大溪區',
        336:'桃園市復興區',
        337:'桃園市大園區',
        338:'桃園市蘆竹區',
        350:'苗栗縣竹南',
        351:'苗栗縣頭份',
        352:'苗栗縣三灣',
        353:'苗栗縣南庄',
        354:'苗栗縣獅潭',
        356:'苗栗縣後龍',
        357:'苗栗縣通霄',
        358:'苗栗縣苑裡',
        360:'苗栗縣苗栗',
        361:'苗栗縣造橋',
        362:'苗栗縣頭屋',
        363:'苗栗縣公館',
        364:'苗栗縣大湖',
        365:'苗栗縣泰安',
        366:'苗栗縣銅鑼',
        367:'苗栗縣三義',
        368:'苗栗縣西湖',
        369:'苗栗縣卓蘭',
        400:'臺中市中區',
        401:'臺中市東區',
        402:'臺中市南區',
        403:'臺中市西區',
        404:'臺中市北區',
        406:'臺中市北屯區',
        407:'臺中市西屯區',
        408:'臺中市南屯區',
        411:'臺中市太平區',
        412:'臺中市大里區',
        413:'臺中市霧峰區',
        414:'臺中市烏日區',
        420:'臺中市豐原區',
        421:'臺中市后里區',
        422:'臺中市石岡區',
        423:'臺中市東勢區',
        424:'臺中市和平區',
        426:'臺中市新社區',
        427:'臺中市潭子區',
        428:'臺中市大雅區',
        429:'臺中市神岡區',
        432:'臺中市大肚區',
        433:'臺中市沙鹿區',
        434:'臺中市龍井區',
        435:'臺中市梧棲區',
        436:'臺中市清水區',
        437:'臺中市大甲區',
        438:'臺中市外埔區',
        439:'臺中市大安區',
        500:'彰化縣彰化',
        502:'彰化縣芬園',
        503:'彰化縣花壇',
        504:'彰化縣秀水',
        505:'彰化縣鹿港',
        506:'彰化縣福興',
        507:'彰化縣線西',
        508:'彰化縣和美',
        509:'彰化縣伸港',
        510:'彰化縣員林',
        511:'彰化縣社頭',
        512:'彰化縣永靖',
        513:'彰化縣埔心',
        514:'彰化縣溪湖',
        515:'彰化縣大村',
        516:'彰化縣埔鹽',
        520:'彰化縣田中',
        521:'彰化縣北斗',
        522:'彰化縣田尾',
        523:'彰化縣埤頭',
        524:'彰化縣溪州',
        525:'彰化縣竹塘',
        526:'彰化縣二林',
        527:'彰化縣大城',
        528:'彰化縣芳苑',
        530:'彰化縣二水',
        540:'南投縣南投',
        541:'南投縣中寮',
        542:'南投縣草屯',
        544:'南投縣國姓',
        545:'南投縣埔里',
        546:'南投縣仁愛',
        551:'南投縣名間',
        552:'南投縣集集',
        553:'南投縣水里',
        555:'南投縣魚池',
        556:'南投縣信義',
        557:'南投縣竹山',
        558:'南投縣鹿谷',
        600:'嘉義市',
        602:'嘉義縣番路',
        603:'嘉義縣梅山',
        604:'嘉義縣竹崎',
        605:'嘉義縣阿里山',
        606:'嘉義縣中埔',
        607:'嘉義縣大埔',
        608:'嘉義縣水上',
        611:'嘉義縣鹿草',
        612:'嘉義縣太保',
        613:'嘉義縣朴子',
        614:'嘉義縣東石',
        615:'嘉義縣六腳',
        616:'嘉義縣新港',
        621:'嘉義縣民雄',
        622:'嘉義縣大林',
        623:'嘉義縣溪口',
        624:'嘉義縣義竹',
        625:'嘉義縣布袋',
        630:'雲林縣斗南',
        631:'雲林縣大埤',
        632:'雲林縣虎尾',
        633:'雲林縣土庫',
        634:'雲林縣褒忠',
        635:'雲林縣東勢',
        636:'雲林縣臺西',
        637:'雲林縣崙背',
        638:'雲林縣麥寮',
        640:'雲林縣斗六',
        643:'雲林縣林內',
        646:'雲林縣古坑',
        647:'雲林縣莿桐',
        648:'雲林縣西螺',
        649:'雲林縣二崙',
        651:'雲林縣北港',
        652:'雲林縣水林',
        653:'雲林縣口湖',
        654:'雲林縣四湖',
        655:'雲林縣元長',
        700:'臺南市中西區',
        701:'臺南市東區',
        702:'臺南市南區',
        704:'臺南市北區',
        708:'臺南市安平區',
        709:'臺南市安南區',
        710:'臺南市永康區',
        711:'臺南市歸仁區',
        712:'臺南市新化區',
        713:'臺南市左鎮區',
        714:'臺南市玉井區',
        715:'臺南市楠西區',
        716:'臺南市南化區',
        717:'臺南市仁德區',
        718:'臺南市關廟區',
        719:'臺南市龍崎區',
        720:'臺南市官田區',
        721:'臺南市麻豆區',
        722:'臺南市佳里區',
        723:'臺南市西港區',
        724:'臺南市七股區',
        725:'臺南市將軍區',
        726:'臺南市學甲區',
        727:'臺南市北門區',
        730:'臺南市新營區',
        731:'臺南市後壁區',
        732:'臺南市白河區',
        733:'臺南市東山區',
        734:'臺南市六甲區',
        735:'臺南市下營區',
        736:'臺南市柳營區',
        737:'臺南市鹽水區',
        741:'臺南市善化區',
        742:'臺南市大內區',
        743:'臺南市山上區',
        744:'臺南市新市區',
        745:'臺南市安定區',
        800:'高雄市新興區',
        801:'高雄市前金區',
        802:'高雄市苓雅區',
        803:'高雄市鹽埕區',
        804:'高雄市鼓山區',
        805:'高雄市旗津區',
        806:'高雄市前鎮區',
        807:'高雄市三民區',
        811:'高雄市楠梓區',
        812:'高雄市小港區',
        813:'高雄市左營區',
        814:'高雄市仁武區',
        815:'高雄市大社區',
        820:'高雄市岡山區',
        821:'高雄市路竹區',
        822:'高雄市阿蓮區',
        823:'高雄市田寮區',
        824:'高雄市燕巢區',
        825:'高雄市橋頭區',
        826:'高雄市梓官區',
        827:'高雄市彌陀區',
        828:'高雄市永安區',
        829:'高雄市湖內區',
        830:'高雄市鳳山區',
        831:'高雄市大寮區',
        832:'高雄市林園區',
        833:'高雄市鳥松區',
        840:'高雄市大樹區',
        842:'高雄市旗山區',
        843:'高雄市美濃區',
        844:'高雄市六龜區',
        845:'高雄市內門區',
        846:'高雄市杉林區',
        847:'高雄市甲仙區',
        848:'高雄市桃源區',
        849:'高雄市那瑪夏區',
        851:'高雄市茂林區',
        852:'高雄市茄萣區',
        880:'澎湖縣馬公',
        881:'澎湖縣西嶼',
        882:'澎湖縣望安',
        883:'澎湖縣七美',
        884:'澎湖縣白沙',
        885:'澎湖縣湖西',
        900:'屏東縣屏東',
        901:'屏東縣三地門',
        902:'屏東縣霧臺',
        903:'屏東縣瑪家',
        904:'屏東縣九如',
        905:'屏東縣里港',
        906:'屏東縣高樹',
        907:'屏東縣鹽埔',
        908:'屏東縣長治',
        909:'屏東縣麟洛',
        911:'屏東縣竹田',
        912:'屏東縣內埔',
        913:'屏東縣萬丹',
        920:'屏東縣潮州',
        921:'屏東縣泰武',
        922:'屏東縣來義',
        923:'屏東縣萬巒',
        924:'屏東縣崁頂',
        925:'屏東縣新埤',
        926:'屏東縣南州',
        927:'屏東縣林邊',
        928:'屏東縣東港',
        929:'屏東縣琉球',
        931:'屏東縣佳冬',
        932:'屏東縣新園',
        940:'屏東縣枋寮',
        941:'屏東縣枋山',
        942:'屏東縣春日',
        943:'屏東縣獅子',
        944:'屏東縣車城',
        945:'屏東縣牡丹',
        946:'屏東縣恆春',
        947:'屏東縣滿州',
        950:'臺東縣臺東',
        951:'臺東縣綠島',
        952:'臺東縣蘭嶼',
        953:'臺東縣延平',
        954:'臺東縣卑南',
        955:'臺東縣鹿野',
        956:'臺東縣關山',
        957:'臺東縣海端',
        958:'臺東縣池上',
        959:'臺東縣東河',
        961:'臺東縣成功',
        962:'臺東縣長濱',
        963:'臺東縣太麻里',
        964:'臺東縣金峰',
        965:'臺東縣大武',
        966:'臺東縣達仁',
        970:'花蓮縣花蓮',
        971:'花蓮縣新城',
        972:'花蓮縣秀林',
        973:'花蓮縣吉安',
        974:'花蓮縣壽豐',
        975:'花蓮縣鳳林',
        976:'花蓮縣光復',
        977:'花蓮縣豐濱',
        978:'花蓮縣瑞穗',
        979:'花蓮縣萬榮',
        981:'花蓮縣玉里',
        982:'花蓮縣卓溪',
        983:'花蓮縣富里',
        890:'金門縣金沙',
        891:'金門縣金湖',
        892:'金門縣金寧',
        893:'金門縣金城',
        894:'金門縣烈嶼',
        896:'金門縣烏坵',
        209:'連江縣南竿',
        210:'連江縣北竿',
        211:'連江縣莒光',
        212:'連江縣東引'
      },
      showItemHtml:true,
      lock:false
    },
    
    methods:{
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      },
      scrollToForm:function(){
        $('body').animate({scrollTop: $("#orderForm").offset().top-200}, 100);
      },
      changePrice:function(){
        if(app.$data.orderDetail.itemType){
          index = app.$data.orderDetail.itemType;
          app.$data.orderDetail.itemPrice = app.$data.orderDetail.setting[index]['price'];
          app.$data.orderDetail.itemNPrice = app.$data.orderDetail.setting[index]['nprice'];
          app.$data.orderDetail.itemTypeName = app.$data.orderDetail.setting[index]['name'];
          app.$data.orderDetail.priceTxt = app.$data.orderDetail.setting[index]['price_txt'];
        }
        if('<?php echo $itemSellType; ?>'=='定期配'){
          index = app.$data.orderDetail.itemType;
          app.$data.orderDetail.arsPeriods = app.$data.orderDetail.setting[index]['periods'];
          app.$data.orderDetail.arsShipStart = '<?php echo date('Y-m'); ?>-'+this.paddingLeft(app.$data.orderDetail.arsDate,2);
        }
      },
      SetDefault:function(){
        app.$data['orderDetail']['itemPrice'] = '<?php echo (int)$itemPrice; ?>';
        app.$data['orderDetail']['itemNPrice'] = '<?php echo (int)$itemNPrice; ?>';
        app.$data['orderDetail']['itemID'] = '<?php echo $itemID; ?>';
        app.$data['orderDetail']['itemType'] = '<?php if($_GET['type']&&$setting[$_GET['type']]){echo $_GET['type'];}else{echo $itemType[0]['value'];} ?>';
        app.$data['orderDetail']['arsDate'] = '<?php echo date('d'); ?>';
      },
      SetZipCode:function(code){
        if(app.$data['zipCode'][code]){
          app.$data.orderDetail.area = app.$data['zipCode'][code];
          return app.$data['zipCode'][code];
        }
        console.log('run');
      },
      paddingLeft:function(str,lenght){
        if(str.length >= lenght)
        return str;
        else
        return this.paddingLeft("0" +str,lenght);
      },
      FormatNumber:function(num){
        var parts = num.toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.'); 
      },
      PreviewOrder:function(num){
        if(app.$data['lock']==true){return;}
        $('#form-msg>.alert').css('display','none');
        $('#preview-msg>.alert').css('display','none');
        $('#btn1').addClass( "disabled" );
        if(app.$data['orderDetail']['logisticType']=='cvs_pay'){
          app.$data['orderDetail']['payType'] = '超商取貨付款';
        }
        submit('#sys-msg','EC_Order/previewOrder','json',app.$data['orderDetail'],'',function(res){
          if(res.code==200){
            $('#preview').modal('show');
            $('#btn1').removeClass( "disabled" );
            app.$data['orderDetail']['itemTypeName'] = res.data['itemTypeName'];
            app.$data['orderDetail']['amount'] = res.data['amount'];
            app.$data['orderDetail']['discount'] = res.data['discount'];
            app.$data['orderDetail']['ship'] = res.data['ship'];
            app.$data['orderDetail']['price'] = res.data['price'];
            gtag('event', 'add_to_cart', {
              "items": [
                {
                  "id": app.$data['orderDetail']['itemType'],
                  "name": '<?php echo $title; ?>',
                  "variant": app.$data['orderDetail']['itemTypeName'],
                  "list_position": 1,
                  "quantity": app.$data['orderDetail']['orderQty'],
                  "price": app.$data['orderDetail']['price']
                }
              ]
            });
            
          }else{
            alert(res.msg);
            $('#btn1').removeClass( "disabled" );
            return;
          }
          app.$data['lock'] = false;
        });
      },
      SubmitOrder:function(){
        if(app.$data['lock']==true){return;}
        app.$data['lock'] = true;
        $('.btn2').addClass( "disabled" );
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_CLIENT; ?>', {action: 'social'}).then(function(token) {
              app.$data['orderDetail']['token'] = token;
              submit('#form-msg','EC_Order/submit','json',app.$data['orderDetail'],'',function(res){
                if(res.code==200){
                  if(app.$data['orderDetail']['payType']=='信用卡'||app.$data['orderDetail']['payType']=='現金匯款'){
                    window.location.assign("<?php echo base_url().'ec/EC_Cart/payOrder?orderNo='; ?>"+res.data['orderNo']);
                  }else{
                    window.location.assign("<?php echo base_url().'ec/EC_Cart/finish?orderNo='; ?>"+res.data['orderNo']);
                  }                  
                }else{
                  alert(res.msg);
                  $('.btn2').removeClass( "disabled" );
                }
                app.$data['lock'] = false;
              });
            });
        });
      },
      CvsSelect:function(){
        window.location.href = '<?php echo base_url(); ?>ec/EC_Query/cvs?type='+app.$data.orderDetail['itemType']+'&qty='+app.$data.orderDetail['orderQty']+'&itemID=<?php echo $_GET['itemID'];?>&logis='+app.$data.orderDetail['logisticType']+'&utm_source=<?php echo $_GET['utm_source'];?>&utm_medium=<?php echo $_GET['utm_medium'];?>&utm_campaign=<?php echo $_GET['utm_campaign'];?>&utm_content=<?php echo $_GET['utm_content'];?>';
      }
    }
  });
  $(document).ready(function() {
    $("#article-slider").camRollSlider();
  });
</script>

<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_CLIENT; ?>"></script>
<style type="text/css">
      .grecaptcha-badge{
        display: none;
      }
</style>

<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>
        window.fbAsyncInit = function() {
          FB.init({
            xfbml            : true,
            version          : 'v9.0'
          });
        };

        (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/zh_TW/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-customerchat" attribution=setup_tool greeting_dialog_display="hide" page_id="<?php echo PAGE_ID; ?>" theme_color="#0A7CFF" logged_in_greeting="您好! 如您對商品有任何問題，歡迎隨時留言詢問，服務人員會盡快與您聯繫" logged_out_greeting="您好! 如您對商品有任何問題，歡迎隨時留言詢問，服務人員會盡快與您聯繫"></div>

