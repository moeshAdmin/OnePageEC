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
                  <label>Name</label>
                  <input type="text" class="form-control" v-model="name">
                </div>
                <div class="form-group">
                  <label>Value1</label>
                  <input type="text" class="form-control" v-model="value1">
                </div>
                <div class="form-group">
                  <label>Value2</label>
                  <input type="text" class="form-control" v-model="value2">
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
                      <th scope="col">Name</th>
                      <th scope="col">Value1</th>
                      <th scope="col">Value2</th>
                      <th scope="col">編輯</th>
                      <th scope="col">刪除</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, key, index) in object">
                      <th>{{ value.scName }}</th>
                      <td>{{ value.scValue1 }}</td>
                      <td>{{ value.scValue2 }}</td>
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
      },
      data: {
        name: '',
        value1: '',
        value2: '',
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

<script type="text/javascript">
  //下拉選單
  uLst = new SlimSelect({
    select: '#ulist',
    closeOnSelect: true,
    placeholder: '選擇成員'
  });
</script>