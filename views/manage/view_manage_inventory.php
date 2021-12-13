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
        
          <div style="display: none;" class="btn btn-info mb-2 btn-block" data-toggle="modal" data-target="#edit-modal">查詢</div>
          <div class="row">
            <div class="col-md-5">
              <form id="input-form" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                  <div class="col-md-12">
                    <label>商品名稱</label>
                    <input type="text" class="form-control" v-model="itemFullName" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-6">
                    <label>規格</label>
                    <input type="text" class="form-control" v-model="itemName" disabled>
                  </div>
                  <div class="col-md-6">
                    <label>貨號</label>
                    <input type="text" class="form-control" v-model="itemNo" disabled>
                  </div>
                </div>
                <hr>
                <div class="form-group row">
                  <div class="col-md-6">
                    <label>動作</label>
                    <select class="form-control" v-model="action">
                      <option value="入庫">入庫</option>
                      <option value="出庫">出庫</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>數量</label>
                    <input type="text" class="form-control" v-model="itemQty">
                  </div>                  
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label>備註</label>
                    <input type="text" class="form-control" v-model="note">
                  </div>
                </div>
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
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object">
                      <td>{{ value.eiSysID }}</td>
                      <td>
                        <a :href="'<?php echo base_url().'ec/EC_Order?itemID=' ?>'+value.eiSysID">{{ value.eiName }}</a>
                        <table class="table-sm" style="width:100%;font-size: 10pt;">
                          <tbody>
                            <tr v-for="(value2,key2,index2) in value.eiSetting">
                              <td style="width:10%"><div class="btn btn-info btn-sm" @click="setAction(value.eiName,key2,value2.name)">操作</div></td>
                              <td style="width:10%"><span class="badge badge-dark">{{key2}}</span></td>
                              <td style="width:15%"><a target="blank" :href="'<?php echo base_url().'manage/manage_inventory/inventoryDetail/' ?>'+key2">在庫: {{value2.stock}}</a></td>
                              <td><span>{{value2.name}}</span>_<span style="color:red">{{value2.price}} 元</span></td>
                              
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td>{{ value.eiItemType }}<br>{{ value.eiStatus }}</td>
                    </tr>
                  </tbody>
                </table>
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
        this.load(this.$data,'');
      },
      data: {
        itemFullName:'',
        itemName:'',
        itemNo:'',
        itemQty:'',
        note:'',
        action:'',
        object: {}
      },
      
      methods:{
        submit: function (e) {
          var r = confirm("確認 "+app.$data['action']+" 貨號 "+app.$data['itemNo']+" 數量 "+app.$data['itemQty']+" ?");
          if (r == true) {
            var sendData = app.$data;
            sendData['object'] = {};
            submit('#sys-msg',init.setUrl(window.location.href)+'/submit','json',sendData,'',function(res){
              if(res.code=="200"){
                app.load('new','');
              }
            });
          }
          
        },
        load: function (data,hash){
          if(data==''&&hash==''){
            $.each(app.$data, function(index) {
              if(index=='object'){return;}
              app.$data[index] = '';
            });
          }else{
            if ($.fn.DataTable.isDataTable('.table')) {
              $('.table').DataTable().destroy();
              $('tbody>tr').remove();
            }
            submit('#hide-msg',init.setUrl(window.location.href)+'/load/'+hash,'json','none','',function(res){
              app.setInput(res);
              setTimeout(function(){
                var tableObject = $('.table').DataTable();
                init.tableConfig(tableObject);
                app.setAction('','','');
              }, 10);
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
        setAction: function(fullName,itemNo,name){
          app.$data['itemFullName'] = fullName;
          app.$data['itemNo'] = itemNo;
          app.$data['itemName'] = name;
          app.$data['itemQty'] = '';
          app.$data['note'] = '';
          app.$data['action'] = '';
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
