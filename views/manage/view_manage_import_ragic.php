<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/datatables.min.js';?>"></script>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/DataTables-1.10.18/js/dataTables.bootstrap4.min.js';?>"></script>
  <link rel="stylesheet" href="<?php echo base_url().'assets/datatable/DataTables-1.10.18/css/dataTables.bootstrap4.min.css';?>" />
<div id="page" class="container-fluid">
  <div class="loader-overlay" style="width: 100%;height: 104%;position: fixed;background-color: rgb(0 0 0 / 50%);
    z-index: 9999;top: 0;left: 0;display: none;">
    <div style="position: relative;top: 40vh;">
      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto;display: block; shape-rendering: auto;" width="50px" height="50px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
      <circle cx="50" cy="50" fill="none" stroke="#e15b64" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138">
        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="0.3333333333333333s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
      </circle>
      <div style="text-align: center;color: #fff;">
        <div>程序執行中，請勿關閉視窗</div>
        <div class="loader-text" style="color:#fff;"></div>
      </div>
      
    </div>
  </div>
  
      <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
        
        <div class="row">
          <div class="col-md-12 order-md-1">
            <div class="search-area">
              <form id="ship-form" method="post" enctype="multipart/form-data">
                <div class="form-row align-items-center">
                  <div class="col-lg-12 col-sm-12">
                    <div class="input-group mb-2">
                      <select class="form-control" name="runType" v-model="runType">
                        <option value=''>選擇上傳檔案類型</option>
                        <option value='waca_好菌家'>WACA (好菌家訂單列表)</option>
                        <option value='waca_好菌家出貨'>WACA (好菌家出貨單)</option>
                        <option value='cyberbiz_黑松'>cyberbiz_黑松</option>
                        <option value='cyberbiz_日研專科'>cyberbiz_日研專科</option>
                        <option value='pchome'>PCHome</option>
                        <option value='momo'>MOMO</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-12 col-sm-12">
                    <div class="form-control" style="height:auto;">
                        <span class="fileinput-button">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span>點選這裡上傳檔案附件</span>
                        <input id="fileupload-ship" type="file" name="files[]" multiple="multiple"></span>
                        <div id="files-ship" class="files"></div>
                    </div>
                  </div>
                </div>
                
              </form>

              <div class="row justify-content-md-center">
                <div class="col col-lg-2"></div>
                <div class="col-md-auto">
                  <div id="previewBtn" class="btn btn-info" @click="preview">預覽</div>
                </div>
                <div class="col col-lg-2"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 order-md-1">
            <div class="search-area">
              <div id="sContent">
                <div class="no-content" v-html="retHtml"></div>
                <center><div id="uploadBtn" class="btn btn-info btn-block" @click="upload" v-show="status=='preview'">上傳</div></center>
                <br>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 order-md-1">
            <div id="result" class="search-area" style="display: none;">
              <table class="table table-sm table-hover table-striped dataTable no-footer">
                <thead>
                  <th>上傳時間</th><th>類型</th><th>檔名</th><th>上傳者</th>
                </thead>
                <tbody>
                  <tr v-for="uploadData in uploadData">
                    <td>{{uploadData.time}}</td>
                    <td>{{uploadData.desc}}</td>
                    <td>{{uploadData.file}}</td>
                    <td>{{uploadData.upload}}</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        </main>

    </div>
    
</div>
    

<script type="text/javascript">
  $(document).ready(function() {
    uploadInitMultiple('ship');
  });
  var app = new Vue({
      el: '#page',
      created(){
        this.getUploadFile();        
      },
      data: {
        targetHash:'',
        retHtml:'',
        status:'',
        runType:'',
        filesMeta:[],
        fileTypeAry:[],
        uploadData:{},
        lock:false
      },
      
      methods:{
        preview: async function(){
          if(app.$data['lock']){return;}
          app.$data['lock'] = true;
          $('.loader-overlay').css('display','block');
          submit('#sys-msg',"<?php echo base_url().'manage/manage_import_ragic/submit/preview' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import_ragic'},function(res){
            $('.loader-overlay').css('display','none');   
              app.$data['lock'] = false;
              if(res.code==200){   
                app.$data['retHtml'] = res.data['retHtml'];
                app.$data['status'] = 'preview';
              }
          });
        },
        upload: function(){
          if(app.$data['lock']){return;}
          app.$data['lock'] = true;
          $('.loader-overlay').css('display','block');
          submit('#sys-msg',"<?php echo base_url().'manage/manage_import_ragic/submit/upload' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import_ragic'},function(res){
              app.$data['lock'] = false;
              //app.$data['status'] = '';
              $('.loader-overlay').css('display','none');
              if(res.code==200){
                app.$data['retHtml'] = res.data['retHtml'];        
                alert('上傳完成 - '+res.data['retHtml']);
                window.location.reload();
              }
          });
        },
        getUploadFile: function(){
          submit('#hidden-msg',"<?php echo base_url().'manage/manage_import_ragic/getUploadFile' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import_ragic'},function(res){
              app.$data['uploadData'] = res.data;
              setTimeout(function(){
                $('#result').css('display','block');
                $('.table').DataTable({"order": [[ 0, "desc" ]]});
              }, 500); 
          });
        }
      },
      components: {
        //'button-counter': MenuItem
      }
  });

</script>
