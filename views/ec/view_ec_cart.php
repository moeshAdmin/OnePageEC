<?php 

$user_detail=$this->session->all_userdata(); 
if(count($orderData[0]['detail'])==1){
  $imgCol = 'col-md-4';
  $textCol = 'col-md-8';
}else{
  $imgCol = 'col-md-4';
  $textCol = 'col-md-8';
}
if($orderTable||$orderTableARS){
  $hidden = 'style="display:none;"';
}
?>
<header><h1 style="position: absolute;top:0;color: #fff;display: none;"><?php echo $title.' > '.$cate; ?></h1></header>
<div id="pg-main">
  <div class="container">
    <div class="row">
      <h2><?php echo $title; ?></h2>
    </div>
    <hr>
    <div class="row">
      <div class="col-12">
          <div class="form-group row">
            <h4>訂購資訊</h4>
          </div>
          <?php 
            if($orderTable){
              echo '<h5>一般商品</h5>';
              echo $orderTable; 
            }
            if($orderTableARS){
              echo '<h5>進行中定期配送合約</h5>';
              echo $orderTableARS; 
            }
          ?>
          <div class="form-group row" <?php echo $hidden; ?>>
              <div class="container" style="border:1px solid #ccc;border-radius:5px;padding:30px;">
                <div class="row">
                  <div v-if="'<?php echo $imgCol; ?>'!=''" class="<?php echo $imgCol; ?>" style="text-align: center;margin-top: 20px;"><img style="width:100%;max-width:300px;" 
                    src="<?php if(preg_match('/http/', $itemImg[0][0])){echo $itemImg[0][0];}else{echo '../'.$itemImg[0][0];} ?>">
                  </div>

                  <div class="<?php echo $textCol; ?>">
                    <div style="padding-top: 80px;"></div>
                    <?php echo '<h4><b>'.$orderData[0]['eoItemName'].'</b> '.$orderData[0]['eoItemType'].'</h4>'; ?>
                    <?php if($orderData[0]['eoARSPeriods']){echo '共需配送 '.$orderData[0]['eoARSPeriodsTotal'].' 期</h4>、<b>本期是第 '.$orderData[0]['eoARSPeriods'].'</b> 期';} ?>
                        <table class="table table-sm table-hover table-rwd table-striped">
                          <thead>
                            <th>商品規格</th><th>單價</th><th>數量</th><th>小計</th>
                          </thead>
                          <tbody>
                        <?php 
                          if($viewType!='訂購紀錄'){foreach ($orderData[0]['detail'] as $key => $value) { 
                        ?>
                          <tr>
                            <td data-th="商品規格"><div id="items"><?php echo $value['eodItemName']; ?> <?php echo $value['eodItemType']; ?></div></td>
                            <td data-th="單價"><?php echo number_format($value['eodItemPrice']); ?></td>
                            <td data-th="數量"><?php echo $value['eodItemQty']; ?></td>
                            <td data-th="小計"><span style="color:red"><?php echo number_format($value['eodItemPrice']*$value['eodItemQty']); ?></span> 元</td>
                          </tr>
                        <?php }} ?>
                          </tbody>
                        </table>
                        <hr>
                        <div class="row">
                          
                          <div class="col-6" style="display: none;">運費</div>
                            <div class="col-6" style="text-align:right;display: none;">
                              <span style="color:red"><?php echo number_format($orderData[0]['eoOrderShipAmount']); ?></span> 元
                            </div>
                          <div class="col-6" style="display: none;">折扣</div>
                            <div class="col-6" style="text-align:right;display: none;">
                              <span style="color:red"><?php echo number_format($orderData[0]['eoOrderDiscount']); ?></span> 元
                            </div>
                            <div class="col-12 col-md-2" style="padding-top:0px;">訂單總金額</div>
                            <div class="col-12 col-md-10" style="padding-top:0px;text-align:right;">
                              <span style="color:red"><?php echo number_format($orderData[0]['eoOrderAmount']); ?></span> 元
                              <span id="value" style="display: none;"><?php echo $orderData[0]['eoOrderAmount']; ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-12 col-md-2">訂單編號</div>
                          <div class="col-12 col-md-10" style="text-align:right;">
                            <?php echo $orderData[0]['eoOrderNo']; ?>
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-12 col-md-2">付款資訊</div>
                            <div class="col-12 col-md-10" style="text-align:right;">
                              <?php echo $orderData[0]['payBtn']; ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-12 col-md-2">配送資訊</div>
                            <div class="col-12 col-md-10" style="text-align:right;">
                              <?php 
                                $name = mb_substr($orderData[0]['eoReceiverName'], 0,1);
                                if(mb_strlen($orderData[0]['eoReceiverName'])==2){
                                  $name .= '*';
                                }else if(mb_strlen($orderData[0]['eoReceiverName'])==3){
                                  $name .= '**';
                                }else if(mb_strlen($orderData[0]['eoReceiverName'])==4){
                                  $name .= '***';
                                }
                                $phone = substr($orderData[0]['eoReceiverPhone'], 0,4).'***'.substr($orderData[0]['eoReceiverPhone'], 4,-3);
                                if($orderData[0]['eoDeliverCvsName']){
                                  $addr = $orderData[0]['eoDeliverCvsName'];
                                }else{
                                  $addr = $orderData[0]['eoReceiverPostCode'].' '.mb_substr($orderData[0]['eoReceiverAddr'], 0,8).'******';
                                }
                                echo $name.' 先生/小姐<br>';
                                echo $phone.'<br>';
                                echo $addr.'<br>';
                                echo $orderData[0]['eoMemberNote'];
                                if($orderData[0]['eoDeliverCode']){
                                  echo $orderData[0]['eoDeliverName'].'-<span style="text-decoration: underline;color: #17a2b8;font-weight: bold;cursor: pointer;" @click="copyDeliverCode()">'.$orderData[0]['eoDeliverCode'].' [複製]</span><br>';
                                  echo '<input style="opacity:0;" type="text" value="'.$orderData[0]['eoDeliverCode'].'" id="deliverCode">';
                                  if(preg_match('/全家/', $orderData[0]['eoDeliverName'])){
                                    echo '<br><a target="blank" href="https://www.famiport.com.tw/Web_Famiport/page/process.aspx"><div class="btn btn-sm btn-info">前往查詢配送狀態</div></a>';
                                  }else if(preg_match('/宅配通/', $orderData[0]['eoDeliverName'])){
                                    echo '<br><a target="blank" href="http://query2.e-can.com.tw/%E5%A4%9A%E7%AD%86%E6%9F%A5%E4%BB%B6A.htm"><div class="btn btn-sm btn-info">前往查詢配送狀態</div></a>';
                                  }
                                }
                              ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">                          
                          <div class="col-12 col-md-2">訂單通知</div>
                            <div class="col-12 col-md-10" style="text-align:right;">
                              <br>
                                <div class="fb-send-to-messenger"
                                  messenger_app_id="<?php echo APP_ID; ?>" 
                                  page_id="<?php echo PAGE_ID; ?>" 
                                  data-ref="<?php echo $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']); ?>" 
                                  color="white" 
                                  size="xlarge">
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-12 col-md-2">發票資訊</div>
                            <div class="col-12 col-md-10" style="text-align:right;">
                              <?php 
                                if($orderData[0]['eoInvoiceStatus']=='未開立'){
                                  echo '尚未開立';
                                }else if($orderData[0]['eoInvoiceStatus']=='已開立'){
                                  echo $orderData[0]['eoInvoiceNo'];
                                }else{
                                  echo '發票處理中';
                                }
                              ?>
                              <?php 
                                if($orderData[0]['eoInvoiceMeta']){
                                  echo '<br>載具: '.$orderData[0]['eoInvoiceMeta'];
                                } 
                              ?> 
                              <?php 
                                if($orderData[0]['eoInvoiceLoveCode']){
                                  echo '<br>愛心碼: '.$orderData[0]['eoInvoiceLoveCode'];
                                } 
                              ?> 
                              <?php 
                                if($orderData[0]['eoInvoiceCom']){
                                  echo '<br>'.$orderData[0]['eoInvoiceCom'].'<br>';
                                  echo $orderData[0]['eoInvoiceAddr'];
                                }  
                              ?>
                              <?php 
                                if($orderData[0]['eoInvoiceStatus']=='已開立'){
                                  echo '<br>您可以在 <a href="https://www.einvoice.nat.gov.tw/APCONSUMER/BTC601W/">財政部電子發票平台</a> 查詢';
                                }
                              ?>
                            </div>
                        </div>
                        <hr v-if="'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='待出貨'||'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='已出貨'">
                        <div class="row" v-if="'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='待出貨'||'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='已出貨'">
                          <div class="col-12" style="text-align:right;">
                            <a href="<?php echo base_url().'ec/EC_Order?itemID='.$itemID.'&showItemHtml=N'; ?>"><div class="btn btn-sm btn-info">好喜歡💖 再買一次</div></a>
                          </div>
                        </div>
                        <hr>
                        <div><?php echo SERVICE_META; ?></div>
                        <div class="row" v-if="1==2&&'<?php echo $orderData[0]['eoOrderStatus']; ?>'!='已取消'">
                          <div class="col-6">聯繫客服</div>
                            <div class="col-6" style="text-align:right;">
                              <?php echo $orderData[0]['changeBtn']; ?>
                            </div>
                        </div>
                        
                  </div>
                  <div class="item-status" style="color:red;border:1px solid red" 
                    v-if="'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='待付款'||'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='已取消'">
                      <?php echo $orderData[0]['eoOrderStatus']; ?>
                  </div>
                  <div class="item-status" style="color:#008ea6;border:1px solid #008ea6;" v-if="'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='待出貨'||'<?php echo $orderData[0]['eoOrderStatus']; ?>'=='已出貨'">
                      <?php echo $orderData[0]['eoOrderStatus']; ?>
                  </div>
                </div>
              </div>
            
          </div>
          <hr>
          <div class="form-group row" style="border: 1px solid rgb(255, 156, 152);background: rgb(255, 226, 223);padding: 5px 20px 10px 20px;">
            <h4 style="color:#ff0000;">注意事項</h4>
            <div class="col-sm-12">
              <?php echo NOTICE_META; ?>
              <br>本網站採用 reCAPTCHA 識別技術且遵循 Google
                              <a href="https://policies.google.com/privacy">Privacy Policy</a> 與
                              <a href="https://policies.google.com/terms">Terms of Service</a> 協議。
            </div>
          </div>
      </div>
    </div>
  </div>

  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>
  <!-- Modal -->
  <div id="valid-form" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><b>請確認信用卡有效期限</b></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>為確保配送順利，請輸入您欲使用之信用卡有效期限：</p>
          <form>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>月</label>
                <select class="form-control" v-model="inputData.month">
                  <option value=""></option>
                  <?php 
                    for ($i=1; $i < 13; $i++) { 
                      echo '<option value="'.str_pad($i,2,"0",STR_PAD_LEFT).'">'.str_pad($i,2,"0",STR_PAD_LEFT).'</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="form-group col-md-8">
                <label>年</label>
                <select class="form-control" v-model="inputData.year">
                  <option value=""></option>
                  <?php 
                    for ($i=date('Y'); $i < date('Y')+30; $i++) { 
                      echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <div class="btn btn-info btn-sm" @click="validConfirm()">繼續付款</div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
  <div id="edit-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit-order" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit-order">異動配送資訊</h5>
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
                  <input type="text" class="form-control" v-model="orderData.ReceiverName">
                </div>
                <div class="col-md-6">
                  <label>連絡電話</label>
                  <input type="text" class="form-control" v-model="orderData.ReceiverPhone">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-2">
                  <label>郵遞區號</label>
                  <input type="text" class="form-control" v-model="orderData.ReceiverPostCode">
                </div>
                <div class="col-md-10">
                  <label>收件地址</label>
                  <input type="text" class="form-control" v-model="orderData.ReceiverAddr">
                </div>
              </div>
              <p style="color:red">您修改後的資訊將於下一期 <b>{{orderData.NextDeliver}}</b> 之配送訂單生效</p>
            </form>
          </div>
          
        </div>
        <div class="modal-footer">
          <div class="btn btn-info btn-sm" @click="editFormSubmit(orderData.Hash)">送出</div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
</div>
<hr>




<!--
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>  -->

<script>
  var app = new Vue({
    el: '#pg-main',
    created: function (){
      /*$('#valid-form').modal('show');
      setTimeout(function(){ 
        app.socialStateCheck('fb');
      }, 500);*/
    },
    data: {
      msgData:{},
      orderData:{},
      textMsg:'',
      requireOrderNo:'',
      validTarget:'',
      payHash:'',
      inputData:{month:'',year:''},
      showMessengerBtn:false
    },
    
    methods:{
      socialStateCheck: function (type) {
        if(type=='fb'){
          FB.getLoginStatus(function(response) {
            if(response.status=='unknown'){
              app.$data['showMessengerBtn'] = false;
            }else{
              app.$data['showMessengerBtn'] = true;
            }
            if(response.status=='connected'){
              submit('#form-msg','<?php echo base_url(); ?>Social/oauth/<?php echo $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']); ?>','json',response.authResponse,'',function(res){
                if(res.code==200){ 
                    console.log(res);
                }else{
                }
              });
            }else{

            }
          });
        }
      },
      copyDeliverCode:function(){
        var copyText = $("#deliverCode");
        copyText.select();
        document.execCommand("Copy");
        alert("託運單號已複製完成 " + copyText.val());
      },
      setInput: function (res){
        $.each(res.data, function(index) {
            app.$data[index] = res.data[index];
        });
      },
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      },
      FormatNumber:function(num){
        var parts = num.toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.'); 
      },
      validChk:function(hash,target){
        app.$data['validTarget'] = target;
        app.$data['payHash'] = hash;
        $('#valid-form').modal('show');
      },
      validConfirm:function(){
        userDate = app.$data['inputData']['year']+''+app.$data['inputData']['month'];
        if(userDate>app.$data['validTarget']){
          window.location = '<?php echo base_url();?>ec/EC_Cart/startPay/'+app.$data['payHash'];
        }else{
          alert('您的卡片效期不足，請換一張信用卡付款');
        }
      },
      editForm:function(hash,target){
        app.$data['hash'] = hash;
        
        submit('hidden','<?php echo base_url();?>ec/EC_Cart/loadARSData/'+hash,'json','none','',function(res){
            if(res.code==200){
              app.setInput(res);
              $('#edit-form').modal('show');
            }
        });
      },
      editFormSubmit:function(hash,target){
        var yes = confirm('是否確認更改配送資訊');
        if(yes){
          app.$data['hash'] = hash;
          submit('hidden','<?php echo base_url();?>ec/EC_Cart/setARSData/'+hash,'json',app.$data['orderData'],'',function(res){
              if(res.code==200){
                alert('變更完成');
                $('#edit-form').modal('hide');
                location.reload();
              }
          });
        }
      }
    }
  });
  $(document).ready(function() {
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
      appId      : '<?php echo APP_ID; ?>',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v9.0'
    });
  };
  setTimeout(function(){ 
      FB.Event.subscribe('send_to_messenger', function(e) {
        if(e.event=="clicked"){
          alert('已將訂單資訊寄送至您的 Messenger !');
        }
      });
  }, 500);
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = 'https://connect.facebook.net/zh_TW/sdk/xfbml.customerchat.js';
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-customerchat" attribution=setup_tool greeting_dialog_display="hide" page_id="<?php echo PAGE_ID; ?>" theme_color="#0A7CFF" logged_in_greeting="您好! 感謝您訂購商品，如對商品或訂單有任何疑問，歡迎隨時與我們聯繫" logged_out_greeting="您好! 感謝您訂購商品，如對商品或訂單有任何疑問，歡迎隨時與我們聯繫"></div>