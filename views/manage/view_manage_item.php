<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/datatables.min.js';?>"></script>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/DataTables-1.10.18/js/dataTables.bootstrap4.min.js';?>"></script>
  <link rel="stylesheet" href="<?php echo base_url().'assets/datatable/DataTables-1.10.18/css/dataTables.bootstrap4.min.css';?>" />


<div id="page" class="container-fluid">
    <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
        
          <div style="display: none;" class="btn btn-info mb-2 btn-block" data-toggle="modal" data-target="#edit-modal">查詢</div>
          <div class="row">
            <div class="col-md-5">
              <form id="input-form" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                  <div class="col-md-2">
                    <label> </label>
                    <div class="btn btn-info form-control btn-block" @click="load('','')">新增</div>
                  </div>
                  <div class="col-md-10">
                    <label>Name</label>
                    <input type="text" class="form-control" v-model="name">
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-6">
                    <label>商品狀態</label>
                    <select class="form-control" v-model="status">
                      <option value="銷售中">銷售中</option>
                      <option value="預購中">預購中</option>
                      <option value="售完">售完</option>
                      <option value="測試商品">測試商品</option>
                      <option value="隱藏賣場">隱藏賣場</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>商品類型</label>
                    <select class="form-control" v-model="type">
                      <option value="一般商品">一般商品</option>
                      <option value="定期配">定期配</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>商品描述</label>
                  <input type="text" class="form-control" v-model="desc">
                </div>
                <div class="form-group">
                  <label>HTML</label>
                  <textarea id="article" class="form-control" v-model="html" rows="6" name="article"></textarea>
                </div>                
                <div class="form-group">
                  <label>商品圖片</label>
                  <input type="text" class="form-control" v-model="img">
                </div>
                <div class="form-group">
                  <label v-if="itemID"><div class="btn btn-primary btn-sm" @click="advEditor">商品規格編輯</div></label>
                  <label v-else><div style="color:red">請先儲存商品頁再進行規格設定</div></label>
                  <div v-for="(value2,key2,index2) in settingJson" v-if="value2.name">
                    <span class="badge badge-dark">{{value2.itemKey}}</span>
                    <span>{{value2.name}}</span>_<span style="color:red">{{value2.price}} 元</span><br>
                    <span v-if="type=='定期配'">
                      <div v-if="value2.periodType=='M'">每 {{value2.periodFreq}} 個月配送 1 次，共 {{value2.periods}} 期</div>
                      <div v-if="value2.periodType=='D'">每 {{value2.periodFreq}} 天配送 1 次，共 {{value2.periods}} 期</div>
                    </span>
                  </div>
                  <textarea style="display: none;" class="form-control" v-model="setting" rows="6"></textarea>
                </div>
                <div class="form-group" style="display: none;">
                  <label>售價</label>
                  <input type="text" class="form-control" v-model="price">
                </div>
                <input type="hidden" v-model="hash">
                <div class="form-group">
                  <div class="btn btn-info mb-2" @click="submit">送出</div>
                </div>
              </form>
            </div>
            <div id="right" class="col-md-7">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">名稱</th>
                      <th scope="col">上架狀態</th>
                      <th scope="col">編輯</th>
                      <th scope="col">刪除</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object">
                      <td>{{ value.eiSysID }}</td>
                      <td>
                        <a :href="'<?php echo base_url().'ec/EC_Order?itemID=' ?>'+value.eiSysID">{{ value.eiName }}</a>
                        <div v-for="(value2,key2,index2) in value.eiSetting">
                          <span class="badge badge-dark">{{key2}}</span>
                          <span>{{value2.name}}</span>_<span style="color:red">{{value2.price}} 元</span><br>
                          <span v-if="value.eiItemType=='定期配'">
                            <div v-if="value2.periodType=='M'">每 {{value2.periodFreq}} 個月配送 1 次，共 {{value2.periods}} 期</div>
                            <div v-if="value2.periodType=='D'">每 {{value2.periodFreq}} 天配送 1 次，共 {{value2.periods}} 期</div>
                          </span>
                        </div>
                      </td>
                      <td>{{ value.eiItemType }}<br>{{ value.eiStatus }}</td>
                      <td><div class="btn btn-info btn-sm" @click="load('',value.hash)">編輯</div></td>
                      <td><div class="btn btn-danger btn-sm" @click="delData('',value.hash)">刪除</div></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
    </div>

  <!-- modal -->
  <div class="modal fade" id="advForm" tabindex="-1" role="dialog" aria-labelledby="advForm" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 95%;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">商品規格編輯</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="form-msg"></div>
          
          <div @click="advAction('add',null)"><a href="#">[add]</a></div>
          <table class="table">
            <thead>              
              <th>複製</th>
              <th v-if="type=='定期配'">方案_ID<span style="color:red">*</span></th>
              <th v-else>組合料號(與倉庫端相同)<span style="color:red">*</span></th>
              <th>規格名稱<span style="color:red">*</span></th>
              <th>原價<span style="color:red">*</span></th>
              <th>售價自訂說明</th>
              <th>售價<span style="color:red">*</span></th>
              <th v-if="type=='定期配'">定期配設定</th>
              <th>刪除</th>
            </thead>
            <tbody>
              <tr v-for="(item,key,value) in settingJson">
                
                <td><div @click="advAction('clone',key)"><a href="#">[複製]</a></div></td>
                <td><input type="text" v-model="item.itemKey"></td>
                <td><input type="text" v-model="item.name"></td>
                <td><input type="text" v-model="item.nprice"></td>
                <td><input type="text" v-model="item.price_txt"></td>
                <td><input type="text" v-model="item.price"></td>
                <td v-if="type=='定期配'">
                  總期數<span style="color:red">*</span>: <input type="text" v-model="item.periods"><br>
                  週期<span style="color:red">*</span>: <input type="text" v-model="item.periodType"><br>
                  頻率<span style="color:red">*</span>: <input type="text" v-model="item.periodFreq"><br>
                  通路來源<span style="color:red">*</span>: <input type="text" v-model="item.source"><br>
                  說明<div v-if="item.periodType=='M'">每 {{item.periodFreq}} 個月配送 1 次，共 {{item.periods}} 期</div>
                  <div v-if="item.periodType=='D'">每 {{item.periodFreq}} 天配送 1 次，共 {{item.periods}} 期</div>
                </td>
                <td><div @click="advAction('remove',key)"><a href="#">[x]</a></div></td>
              </tr>
            </tbody>
          </table>
          <div v-if="type=='定期配'">
            <a target="blank" href="https://ap3.ragic.com/hugePlus/forms4/4">設定完成後，請到這裡設定定期配範本檔</a><br>
            <span style="color:red">EC_PAGE_ID: {{itemID}}、EC_方案_ID: 同上表各項</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-info btn2" id="submitOrder"  @click="setSettingToStr">送出</button>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
</div>

    

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
        this.load('new','');
      },
      data: {
        itemID: '',
        name: '',
        desc: '',
        img: '',
        price: '',
        hash: '',
        html: '',
        type: '一般商品',
        status: '銷售中',
        setting:'',
        settingJson:{},
        object: {}
      },
      
      methods:{
        submit: function (e) {
          for ( instance in CKEDITOR.instances ){
            CKEDITOR.instances[instance].updateElement();
          }
          app.$data.html = $('#article').val();
          var sendData = app.$data;
          sendData['object'] = {};
          submit('#sys-msg',init.setUrl(window.location.href)+'/submit','json',sendData,'',function(res){
            if(res.code=="200"){
              app.load('new','');
            }
          });
        },
        load: function (data,hash){
          if(data==''&&hash==''){
            $.each(app.$data, function(index) {
              if(index=='object'){return;}
              app.$data[index] = '';
            });
            app.editorInit();
          }else if(data=='new'){
            if ($.fn.DataTable.isDataTable('.table-hover')) {
              $('.table-hover').DataTable().destroy();
              $('tbody>tr').remove();
            }
            submit('#hide-msg',init.setUrl(window.location.href)+'/load/'+hash,'json','none','',function(res){
              app.setInput(res);
              setTimeout(function(){
                var tableObject = $('.table-hover').DataTable();
                init.tableConfig(tableObject);
                app.editorInit();
              }, 10); 
            }); 
          }else{            
            submit('#hide-msg',init.setUrl(window.location.href)+'/load/'+hash,'json','none','',function(res){
              app.setInput(res);
              setTimeout(function(){
                app.editorInit();
                app.setSettingJson(res.data['setting']);
              }, 10); 
            }); 
          }
        },
        delData: function (data,hash){
          var r = confirm("請確認是否刪除?");
          if (r == true) {
            submit('#hide-msg',init.setUrl(window.location.href)+'/del/'+hash,'json','none','',function(res){
              if(res.code=="200"){
                window.location.reload();
              }
            });
          }
        },
        setInput: function (res){
          $.each(res.data, function(index) {
              app.$data[index] = res.data[index];
              if(index=='object'){
                $.each(res.data['object'], function(index2) {
                  app.$data['object'][index2]['eiSetting'] = JSON.parse(res.data['object'][index2]['eiSetting']);
                });
              }
          });
        },
        editorInit: function(){
          setTimeout(function(){
            var editor = CKEDITOR.instances.article;

            if (editor) {
              editor.destroy(true); 
            }
            //CKEDITOR.replace( 'article');
            CKEDITOR.replace( 'article', {
              filebrowserBrowseUrl: '<?php echo base_url();?>assets/ckeditor/plugins/ckfinder/ckfinder.html',
              filebrowserUploadUrl: '<?php echo base_url();?>assets/ckeditor/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
            } );
          },10);
        },
        setSettingJson: function(str){
          if(str==''){
            str = '{"": {"name": "","nprice": "","price": "","periods": "","periodType": "","periodFreq": "","source": ""}}';
          }
          num = 0;
          var setObj = JSON.parse(str);
          var newObj = [];
          $.each(setObj, function(index) {
            newObj[num] = setObj[index];
            newObj[num]['itemKey'] = index;
            num++;
          });
          app.$data['settingJson'] = newObj;
        },
        advEditor: function(){
          app.setSettingJson(app.$data['setting']);
          $('#advForm').modal('show');
        },
        advAction: function(type,index){
          if(type=='add'){
            var newObj = {itemKey:'',name: "",nprice: "",periodFreq: "",periodType: "",periods: "",price: "",source: ""};
            app.$data['settingJson'].push(newObj);
          }else if(type=='remove'){
            app.$data['settingJson'].splice(index, 1);
          }else if(type=='clone'){
            var newObj = {itemKey:'',name: app.$data['settingJson'][index]['name'],nprice: app.$data['settingJson'][index]['nprice'],periodFreq: app.$data['settingJson'][index]['periodFreq'],periodType: app.$data['settingJson'][index]['periodType'],periods: app.$data['settingJson'][index]['periods'],price: app.$data['settingJson'][index]['price'],source: app.$data['settingJson'][index]['source']};
            app.$data['settingJson'].push(newObj);
          }
        },
        setSettingToStr: function(){
          str = '';
          value = '';
          obj = app.$data['settingJson'];
          for (var index in obj) {
            for (var index2 in obj[index]) {
              if(value==''){
                value = '"'+index2+'":"'+obj[index][index2]+'"';
              }else{
                value += ',"'+index2+'":"'+obj[index][index2]+'"';
              }
            }
            console.log(obj[index]['itemKey']);
            setIndex = obj[index]['itemKey'];
            if(str==''){
              str = '"'+setIndex+'":{'+value+'}';
            }else{
              str += "\r\n"+',"'+setIndex+'":{'+value+'}';
            }
            value = '';
          }
          app.$data['setting'] = '{'+str+'}';
          $('#advForm').modal('hide');
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

Object.size = function(obj) {
  var size = 0,
    key;
  for (key in obj) {
    if (obj.hasOwnProperty(key)) size++;
  }
  return size;
};

</script>

<script type="text/javascript">
  //下拉選單
  uLst = new SlimSelect({
    select: '#ulist',
    closeOnSelect: true,
    placeholder: '選擇成員'
  });
</script>