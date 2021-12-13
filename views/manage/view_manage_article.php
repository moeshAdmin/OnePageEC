<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/datatables.min.js';?>"></script>
  <script type="text/javascript" src="<?php echo base_url().'assets/datatable/DataTables-1.10.18/js/dataTables.bootstrap4.min.js';?>"></script>
  <link rel="stylesheet" href="<?php echo base_url().'assets/datatable/DataTables-1.10.18/css/dataTables.bootstrap4.min.css';?>" />

<transition name="container-fade">
<div id="manage" class="container-fluid">
      <div class="row">
        <main role="main" class="col-md-11 ml-sm-auto col-lg-11 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $dt['title']; ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
            </div>
          </div>
        
          <div style="display: none;" class="btn btn-info mb-2 btn-block" data-toggle="modal" data-target="#edit-modal">查詢</div>
          <div class="row">
            <div class="col-md-6">
              <form id="input-form" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                  <div class="col-md-1">
                    <label></label>
                    <div class="btn btn-info" @click="load('','')">新增</div>
                  </div>
                  <div class="col-md-3">
                    <label>頁面類型 <a href="#" data-toggle="tooltip" data-placement="top" title="不公開頁面會直接遮蔽此頁面瀏覽功能">(?)</a></label>
                    <select class="form-control" v-model="articleType" >
                      <option value="page">靜態頁面</option>
                      <option value="product">產品頁面</option>
                      <option value="news">新聞頁面</option>
                      <option value="private">不公開頁面</option>
                      <option value="contact">聯絡頁面(特殊)</option>
                      <option value="event">獨立活動頁面(特殊)</option>
                    </select>
                  </div>
                  <div class="col-md-8">
                    <label>頁面標題 <a href="#" data-toggle="tooltip" data-placement="top" title="使用_ignore標籤，可使項目在特定語系不顯示">(?)</a></label>
                    <input type="text" class="form-control" v-model="articleTitle" v-on:change="genUrl">
                  </div>
                </div>
                <div class="form-group">
                  <textarea id="article" class="form-control" rows="6" v-model="articleContent" name="article"></textarea>
                </div>
                <div class="form-group">
                  <div v-if="articleType=='product'">
                    <label>[產品頁面]需使用特殊標籤，如<span style="color:red">[keyfeature]內文內文內文[/keyfeature]</span>，資料才會正確顯示，標籤有[keyfeature]、[specification]、[setup]、[download]、[faq]、[support]</label>
                  </div>
                  <div v-if="articleType=='private'">
                    <label>本頁面在此語系不對外開放。</label>
                  </div>
                  <div v-if="articleType=='page'||articleType=='product'||articleType=='news'">
                    <label>若需要於內文中加入關聯產品，可使用[related]標籤，如<span style="color:red">[related]1;3;5[/related]</span>，數字為文章ID</label>
                  </div>
                  <div v-if="articleType=='event'">
                    <label>獨立活動頁面請直接將文章網址 <span style="color:red">article</span> 字樣改為 <span style="color:red">event</span> 即可</label>
                  </div>
                </div>
                <div class="form-group row"  v-if="articleType=='product'">
                  <div class="col-md-12">
                    <label>幻燈片圖檔連結 (請用分號分隔，如<span style="color:red">assets/images/img1.png;assets/images/img2.png</span>)</label>
                    <textarea class="form-control" rows="2" v-model="articleSlider"></textarea>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-3">
                    <label>分類</label>
                    <select class="form-control" v-model="articleCategory" >
                      <option v-for="option in parentOption" v-bind:value="option.id"> {{ option.name }} </option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>日期</label>
                    <input type="text" class="form-control" v-model="articleDate" >
                  </div>
                  <div class="col-md-6">
                    <label>自訂網址 <a href="#" data-toggle="tooltip" data-placement="top" title="此項目會與 SEO 有關，請謹慎設計">(?)</a></label>
                    <input type="text" class="form-control" v-model="articleURL">
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-6">
                    <label>橫幅圖片</label>
                    <input type="text" class="form-control" v-model="articleBanner">
                  </div>
                  <div class="col-md-6">
                    <label>圖示</label>
                    <input type="text" class="form-control" v-model="articleIcon">
                  </div>
                </div>
                <div class="form-group">
                  <label>文章參數 <a href="#" data-toggle="tooltip" data-placement="top" title="格式為[key||value;key2||value2;]，可使用的有:productMeta-修改於分類版中的副標、showMenu/showTopBanner/showBreadcrumb-定義是否顯示側選單/上方橫幅與導航列，預設均為開啟">(?)</a></label>
                  <input type="text" class="form-control" v-model="articleMeta" placeholder="key||value;key2||value2;...">
                </div>

                <div class="form-group custom-control custom-checkbox custom-control-inline">
                  <input type="checkbox" id="ck2" class="custom-control-input" v-model="toAllLang">
                  <label class="custom-control-label" for="ck2">套用至所有語系
                    <a href="#" data-toggle="tooltip" data-placement="top" title="勾選後，資料會自動儲存於每個語系(已有資料語系除外)">(?)</a>
                  </label>
                </div>
                <div class="form-group custom-control custom-checkbox custom-control-inline">
                  <input type="checkbox" id="ck1" class="custom-control-input" v-model="forceRemark">
                  <label class="custom-control-label" for="ck1">強制複寫
                    <a href="#" data-toggle="tooltip" data-placement="top" title="將資料強制覆蓋於每個語系">(?)</a>
                  </label>
                </div>
                <input type="hidden" v-model="articleHash">
                <div class="form-group">
                  <div class="btn btn-info mb-2" @click="submit">送出</div>
                </div>
                {{ errors }}
              </form>
            </div>
            <div id="right" class="col-md-6">
              <div class="table-responsive">
                <table class="table table-hover" style="font-size: 10pt;">
                  <thead>
                    <tr>
                      <th scope="col">ID</th>
                      <th scope="col">標題</th>
                      <th scope="col">分類</th>
                      <th scope="col">類型</th>
                      <th scope="col">編輯</th>
                      <th scope="col">刪除</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ad in articleData">
                      <td>{{ad.articleID}}</td>
                      <td><a v-bind:href="ad.articleURL+'?cache=false'" target="blank">{{ad.articleTitle}}</a></td>
                      <td>{{ad.articleCategory}}</td>
                      <td>{{ad.articleType}}</td>
                      <td><div class="btn btn-info btn-sm" @click="load(ad.articleHash,'')">編輯</div></td>
                      <td><div class="btn btn-danger btn-sm" @click="delData(ad.articleHash)">刪除</div></td>
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
  <!-- modal 視窗區 -->
    <!-- MODAL -->    
    <div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="model-title"><strong>日誌內容</strong></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div id="content" class="modal-body">
              
            </div>
            <div class="modal-footer">
            </div>
          </div>
        </div>
    </div>   
    

<script type="text/javascript">
  

  var app = new Vue({
      el: '#manage',
      created(){
        this.loadAll('');
        var id = "<?php echo $_GET['id'];?>";
        if(id>0){
          hash = '<?php echo $this->Api_common->stringHash("encrypt", $_GET['id']);?>';
          this.load(hash);
        }else{
          this.load('');
        }
      },
      data: {
        articleHash:'',
        articleType: '',
        articleTitle: '',
        articleContent: '',
        articleDate: '',
        articleCategory:'',
        articleURL:'',
        articleBanner:'',
        articleIcon:'',
        articleMeta:'',
        articleSlider:'',
        toAllLang:true,
        forceRemark:false,
        articleData: {},
        errors:'',
        parentOptionData: {}
      },
      
      methods:{
        submit: function (e) {
          for ( instance in CKEDITOR.instances ){
            CKEDITOR.instances[instance].updateElement();
          }
          app.$data.articleContent = $('#article').val();
          setTimeout(function(){
            submit('#sys-msg',init.setUrl(window.location.origin+window.location.pathname)+'/submit','json',app.$data,'',function(res){
              if(res.code=="200"){
                app.reload();
              }
            });
          },500);
        },
        load: function (hash,type){
          submit('#hide-msg',init.setUrl(window.location.origin+window.location.pathname)+'/load/'+hash,'json','none','',function(res){
            app.setInput(res);
            if(type=="copy"){
              app.$data['articleHash'] = '';
            }
            app.editorInit();
          });
          submit('#hide-msg',init.setUrl(window.location.origin+window.location.pathname)+'/loadCateTree','json','none','',function(res){
            app.$data['parentOptionData'] = res.data['parentOptionData'];
          });

        },
        loadAll: function (){
          submit('#hide-msg',init.setUrl(window.location.origin+window.location.pathname)+'/loadAll/','json','none','',function(res){
            app.setInput(res);
            if ($.fn.DataTable.isDataTable('.table')) {
              $('.table').DataTable().destroy();
            }
            setTimeout(function(){
              var tableObject = $('.table').DataTable();
              init.tableConfig(tableObject);
            }, 50); 
          });
        },
        delData: function (hash){
          var r = confirm("請確認是否刪除?");
          if (r == true) {
            submit('#hide-msg',init.setUrl(window.location.origin+window.location.pathname)+'/del/'+hash,'json','none','',function(res){
              if(res.code=="200"){
                window.location.reload();
              }
            });
          }
        },
        setInput: function (res){
          $.each(res.data, function(index) {
              app.$data[index] = res.data[index];
          });
        },
        reload: function (){
          app.load('');
          app.loadAll();
          submit('#sys-msg','<?php echo base_url(); ?>manage/manage_menu/rebuild','json');
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
        genUrl: function (){
        }
      },
      computed: {
        parentOption: {
                get:function(){
                  return this.parentOptionData['cate'];
                },
                set:function(){}
           },
      }
  });
</script>
