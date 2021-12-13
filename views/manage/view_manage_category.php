<script type="text/x-template" id="item-template">
  <li>
    <div
      :class="{bold: isFolder}"
      >
      <a href="#" v-if="isFolder" @click="toggle">[{{ isOpen ? '-' : '+' }}]</a>
      <a href="#" @click="loadItemData(item.hash)">[編輯]</a>
      <a href="#" @click="copyItemData(item.hash)">[複製]</a>
      <span v-html="igs(item.name)"></span>
      <a href="#" @click="delData(item.hash)">[X]</a>
    </div>
      
    <ul v-show="isOpen" v-if="isFolder">
      <tree-item
        class="item"
        v-for="(child, index) in item.children"
        :key="index"
        :item="child"
        @make-folder="$emit('make-folder', $event)"
        @add-item="$emit('add-item', $event)"
      ></tree-item>
      <li class="add" style="display:none;" @click="$emit('add-item', item)">+</li>
    </ul>
  </li>
</script>

<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>


<transition name="container-fade">
<div class="container-fluid">
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
                    <div class="btn btn-info form-control" @click="load('','')">新增</div>
                  </div>
                  <div class="col-md-10">
                    <label>分類名</label>
                    <input type="text" class="form-control" v-model="cateName" v-on:change="genUrl">
                  </div>
                </div>
                <div class="form-group">
                  <label>自訂網址</label>
                  <input type="text" class="form-control" v-model="cateUrl">
                </div>
                <div class="form-group">
                  <label>分類圖示</label>
                  <input type="text" class="form-control" v-model="cateIconUrl">
                </div>
                <div class="form-group">
                  <label>分類橫幅圖片</label>
                  <input type="text" class="form-control" v-model="cateImageUrl">
                </div>
                <div class="form-group row">
                  <div class="col-6">
                    <label>分類模板</label>
                    <select class="form-control" v-model="cateTemplate" >
                      <option value="product-line-list">產品線清單表</option>
                      <option value="product-line-list-no-menu">產品線清單表(無側欄)</option>
                      <option value="product-line-no-menu-banner-only">產品線清單表(只有橫幅)</option>
                      <option value="product-list">產品介紹列表</option>
                      <option value="news-list">新聞列表</option>
                      <option value="cate-list">分類圖示列表</option>
                      <option value="brand-logo">品牌層(特殊)</option>
                      <option value="none">不列舉此分類</option>
                    </select>
                  </div>
                  <div class="col-6" v-if="cateParent==0">
                    <label>顯示關聯文章</label>
                    <select class="form-control" v-model="cateRelated" >
                      <option value="Y">是</option>
                      <option value="N">否</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>分類敘述</label>
                  <textarea class="form-control" rows="3" v-model="cateDesc"></textarea>
                </div>
                <div class="form-group row">
                  <div class="col-6">
                    <label>上層分類</label>
                    <select class="form-control" v-model="cateParent">
                      <option value="0">root</option>
                      <option v-for="option in parentOption" v-bind:value="option.id"> {{ option.name }} </option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label>排序方式</label>
                    <select class="form-control" v-model="cateOrderBy" >
                      <option value="caSysID ASC">建檔順序</option>
                      <option value="caDate ASC">發布日期</option>
                      <option value="caTitleEN ASC">文章標題</option>
                      <option value="caSysID DESC">建檔順序(反序)</option>
                      <option value="caDate DESC">發布日期(反序)</option>
                      <option value="caTitleEN DESC">文章標題(反序)</option>
                    </select>
                  </div>
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
                <input type="hidden" v-model="cateSysID">
                <div class="form-group">
                  <div class="btn btn-info mb-2" @click="submit">送出</div>
                </div>
                {{ errors }}
              </form>
            </div>
            <div id="right" class="col-md-7">
              <div class="table-responsive">
                <ul id="demo">
                  <tree-item
                    class="item"
                    :item="treeData"
                  ></tree-item>
                </ul>
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
      el: '#input-form',
      created(){
        this.load('');
      },
      data: {
        cateSysID: '',
        cateName: '',
        cateUrl: '',
        cateDesc:'',
        cateParent:0,
        cateImageUrl:'',
        cateIconUrl:'',
        cateTemplate:'',
        cateRelated:'Y',
        cateOrderBy:'caSysID',
        toAllLang:true,
        forceRemark:false,
        parentOptionData: {},
        errors:''
      },
      
      methods:{
        submit: function (e) {
          submit('#sys-msg',init.setUrl(window.location.href)+'/submit','json',app.$data,'',function(res){
            if(res.code=="200"){
              app.reload();
            }
          });
        },
        load: function (hash,type){
          submit('#hide-msg',init.setUrl(window.location.href)+'/load/'+hash,'json','none','',function(res){
            app.setInput(res);
            if(type=="copy"){
              app.$data['cateSysID'] = '';
            }
          });
          
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
          });
        },
        reload: function (){
          app.load('');
          demo.loadTree(app.$data.menuType);
          submit('#sys-msg','<?php echo base_url(); ?>manage/manage_menu/rebuild','json');
        },
        genUrl: function (){
          if(!app.cateUrl){
            app.cateUrl = app.cateName.replace(/[`~!@#$%^&*()_\+=<>?:"{}|,.\/;'\\[\]·~！@#￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、]/g,"").replace(/\s+/g,"-").replace(/ignore/g,"").toLowerCase();
          }
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
  //樹狀結構相關
  var treeData = {}
  // define the tree-item component
  Vue.component('tree-item', {
    template: '#item-template',
    props: {
      item: Object
    },
    data: function () {
      return {
        isOpen: true
      }
    },
    computed: {
      isFolder: function () {
        return this.item.children &&
          this.item.children.length
      }
    },
    methods: {
      toggle: function () {
        if (this.isFolder) {
          this.isOpen = !this.isOpen
        }
      },
      makeFolder: function () {
        if (!this.isFolder) {
          //this.$emit('make-folder', this.item)
          //this.isOpen = true
        }
      },
      copyItemData: function (hash) {
        app.load(hash,'copy');
      },
      loadItemData: function (hash) {
        app.load(hash);
      },
      delData: function (hash) {
        app.delData('',hash);
      },
      igs: function(str){
        if(str.indexOf('_ignore')>0){
          return '<span style="color:#ccc">'+str+'</span>';
        }else{
          return str;
        }
      }
    }
  })

  // boot up the demo
  var demo = new Vue({
    el: '#demo',
    data: {
      treeData: treeData
    },
    created(){
        this.loadTree(this.$data,'');
    },
    methods: {
      loadTree: function (e){
        submit('#hide-msg',init.setUrl(window.location.href)+'/loadCateTree','json','none','',function(res){
            demo.$data['treeData'] = res.data;
            app.$data['parentOptionData'] = res.data['parentOptionData'];
          });
      }
    }
  })
</script>
