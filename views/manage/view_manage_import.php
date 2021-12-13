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
        
        <div class="row">
          <div class="col-md-12 order-md-1">
            <div class="search-area">
              <form id="ship-form" method="post" enctype="multipart/form-data">
                <div class="form-row align-items-center">
                  <div class="col-lg-12 col-sm-12">
                    <div class="input-group mb-2">
                      <select class="form-control" name="runType" v-model="runType">
                        <option value=''>選擇上傳檔案類型</option>
                        <option value='fong'>[峰潮] 出貨回應檔</option>
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
                  <th>上傳時間</th><th>類型</th><th></th><th>檔名</th><th>上傳者</th>
                </thead>
                <tbody>
                  <tr v-for="uploadData in uploadData">
                    <td>{{uploadData.time}}</td>
                    <td>{{uploadData.cust}}</td>
                    <td><a href="#" @click="init.popWindow(uploadData.report_url)">檢視</a></td>
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
        runType:'fong',
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
          submit('#sys-msg',"<?php echo base_url().'manage/manage_import/submit/preview' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import'},function(res){
            $('.loader-overlay').css('display','none');              
              app.$data['lock'] = false;
              app.$data['retHtml'] = res.data['retHtml'];
              if(res.code==200){                
                app.$data['status'] = 'preview';
              }
          });
        },
        upload: function(){
          if(app.$data['lock']){return;}
          app.$data['lock'] = true;
          $('.loader-overlay').css('display','block');
          submit('#sys-msg',"<?php echo base_url().'manage/manage_import/submit/upload' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import'},function(res){
              app.$data['retHtml'] = res.data['retHtml'];        
              app.$data['lock'] = false;
              app.$data['status'] = '';
              $('.loader-overlay').css('display','none');
              if(res.code==200){
                alert('上傳完成 - '+res.data['retHtml']);
                window.location.reload();
              }
          });
        },
        getUploadFile: function(){
          submit('#hidden-msg',"<?php echo base_url().'manage/manage_import/getUploadFile' ?>",'json','#ship-form',{empID:'<?php echo $user_detail['empID']; ?>',pageID:'manage_import'},function(res){
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
