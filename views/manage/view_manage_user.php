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
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
        
          <div style="display: none;" class="btn btn-info mb-2 btn-block" data-toggle="modal" data-target="#edit-modal">查詢</div>
          <div class="row">
            <div class="col-md-5">
              <form id="input-form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                  <label>管理者名稱</label>
                  <input type="text" class="form-control" v-model="CName">
                </div>
                <div class="form-group">
                  <label>登入信箱</label>
                  <input type="email" class="form-control" v-model="Email">
                </div>
                <div class="form-group">
                  <label>密碼</label>
                  <input type="password" class="form-control" v-model="Password">
                </div>
                <div class="form-group">
                  <label>角色</label>
                  <select id="actorLst" v-model="userActor" multiple>
                    <option value="管理者">管理者</option>
                    <option value="儀錶板">儀錶板</option>
                    <option value="訂單管理">訂單管理</option>
                    <option value="出貨管理">出貨管理</option>
                    <option value="電子發票">電子發票</option>
                    <option value="異常管理">異常管理</option>
                    <option value="商品管理">商品管理</option>
                    <option value="商城管理">商城管理</option>
                    <option value="系統">系統</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>狀態</label>
                  <select class="form-control" v-model="Active">
                    <option value="N">啟用</option>
                    <option value="Y">停用</option>
                  </select>
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
                      <th scope="col">姓名</th>
                      <th scope="col">信箱</th>
                      <th scope="col">腳色</th>
                      <th scope="col">狀態</th>
                      <th scope="col">編輯</th>
                      <th scope="col">刪除</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object">
                      <th>{{ value.suCName }}</th>
                      <td>{{ value.suEmail }}</td>
                      <td>{{ value.suActor }}</td>
                      <td>
                        <div v-if="value.suIsDisabled=='N'">啟用</div>
                        <div v-else style="color:red">停用</div>
                      </td>
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
</div>
</transition>
    

<script type="text/javascript">
  
  var app = new Vue({
      el: '#page',
      created(){
        this.load(this.$data,'');
        setTimeout(function(){     
          actorLst = new SlimSelect({
            select: '#actorLst',
            closeOnSelect: false,
            placeholder: '選擇',
            addable: function (value) {
              if (value === 'bad') {return false}
              return value
              return {
                text: value,
                value: value.toLowerCase()
              }
            }
          });
        }, 100);
      },
      data: {
        Active: '',
        CName: '',
        Email: '',
        Password: '',
        userActor: '管理者',
        hash: '',
        object: {}
      },
      
      methods:{
        submit: function (e) {
          submit('#sys-msg',init.setUrl(window.location.href)+'/submit','json',app.$data,'',function(res){
            if(res.code=="200"){
              window.location.reload();
            }
          });
          e.preventDefault();
        },
        load: function (data,hash){
          submit('#hide-msg',init.setUrl(window.location.href)+'/load/'+hash,'json','none','',function(res){
            app.setInput(res);
            actorLst.set(res.data['userActor']);
          });
        },
        delData: function (data,hash){
          var r = confirm("請確認是否刪除?");
          if (r == true) {
            submit('#hide-msg',init.setUrl(window.location.href)+'/del/'+hash,'json','none','',function(res){
              if(res.code=="200"){
                app.load('','');
              }
            });
          }
        },
        setInput: function (res){
          $.each(res.data, function(index) {
              app.$data[index] = res.data[index];
              console.log(app.$data[index]+','+index);
          });
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
