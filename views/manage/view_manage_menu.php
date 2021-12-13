<script type="text/x-template" id="item-template">
  <li>
    <div>
      <a href="#" v-if="isFolder" @click="toggle">[{{ isOpen ? '-' : '+' }}]</a>
      <a href="#" @click="loadItemData(item.hash,item.nest)">[編輯]</a>
      <a href="#" @click="copyItemData(item.hash,item.nest)">[複製]</a>
      <span v-html="igs(item.name)"></span>
      <a href="#" @click="delData(item.hash)">[X]</a>
    </div>
      
    <ul v-show="isOpen">
      <tree-item
        class="item"
        v-for="(child, index) in item.children"
        :key="index"
        :item="child"
      ></tree-item>
    </ul>
  </li>
</script>

<?php 
$user_detail=$this->session->all_userdata();
$dt = $this->page->manage_sideMenu($page_id,$_SERVER);
echo $dt['menu'];
?>


<transition name="fade">
<div id="main-menu" class="container-fluid">
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
                    <label>選單類型</label>
                    <select class="form-control" v-model="menuType" @change="loadTree">
                      <option value="topMenu">頂部選單</option>
                      <option value="front">首頁</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-6">
                    <label>項目名稱 <a href="#" data-toggle="tooltip" data-placement="top" title="使用_ignore標籤，可使項目在特定語系不顯示">(?)</a></label>
                    <input type="text" class="form-control" v-model="itemName">
                  </div>
                  <div class="col-6" v-if="nest==1&menuType!='front'&&menuType!='frontJP'">
                    <label>使用模板</label>
                    <select class="form-control" v-model="template">
                      <option value="column-2">全版兩欄式</option>
                      <option value="column-3">全版三欄式</option>
                      <option value="column-icon">兩欄圖示型</option>
                      <option value="no-drop">無下拉選單</option>
                    </select>
                  </div>
                  <div class="col-6" v-if="nest==1&(menuType=='front'||menuType=='frontJP')">
                    <label>使用模板</label>
                    <select class="form-control" v-model="template">
                      <option value="slider">slider</option>
                      <option value="free-block">free-block</option>
                      <option value="item-card">全商品列表</option>
                      <option value="no-show">不顯示</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>圖片連結</label>
                  <input type="text" class="form-control" v-model="itemImgurl">
                </div>
                <div class="form-group">
                  <label>說明文字</label>
                  <textarea type="text" class="form-control" v-model="itemMeta"></textarea>
                </div>
                <div class="form-group row">
                  <div class="col-6">
                    <label>目標類型</label>
                    <select class="form-control" v-model="itemType" @change="loadTypeData">
                      <option value="category">分類</option>
                      <option value="tag">標籤</option>
                      <option value="article">文章</option>
                      <option value="url">外部連結</option>
                      <option value="none">不指定</option>
                    </select>
                  </div>
                  <div class="col-6" v-if="itemType == 'category'||itemType == 'article'||itemType == 'tag'">
                    <label>目標選項</label>
                    <select class="form-control" v-model="itemTargetID">
                      <option  v-for="option in itemTypeOption" v-bind:value="option.value"> {{ option.name }} </option>
                    </select>
                  </div>
                  <div class="col-6" v-if="itemType == 'url'">
                    <label>外部連結</label>
                    <input type="text" class="form-control" v-model="urlID">
                  </div>
                </div>
                

                <div class="form-group row">
                  <div class="col-6">
                    <label>排序</label>
                  <input type="text" class="form-control" v-model="itemOrder">
                  </div>
                  <div class="col-6">
                    <label>上層選單</label>
                  <select class="form-control" v-model="itemParent">
                    <option value="0">root</option>
                    <option v-for="option in parentOption" v-bind:value="option.id"> {{ option.name }} </option>
                  </select>
                  </div>
                </div>
                
                <div class="form-group row">
                  <div class="col-6" v-if="nest==1">
                    <label>Tab1(頂層使用)</label>
                    <select class="form-control" v-model="itemTab1">
                      <option v-for="option in parentOption" v-bind:value="option.id"> {{ option.name }} </option>
                    </select>
                  </div>
                  <div class="col-6" v-if="nest==1||nest==3">
                    <label>Tab2(左欄使用，注意階層)</label>
                    <select class="form-control" v-model="itemTab2">
                      <option v-for="option in parentOption" v-bind:value="option.id"> {{ option.name }} </option>
                    </select>
                  </div>
                </div>
                
                <input type="hidden" class="form-control" v-model="itemSysID">
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
                <div class="form-group">
                  <div class="btn btn-info mb-2" @click="submit">送出</div>
                </div>
              </form>
            </div>
            <div id="right" class="col-md-7">
              <div class="table-responsive">
                <ul id="demo" style="max-height: 600px;">
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
    

<script type="text/javascript">
  
  var app = new Vue({
      el: '#input-form',
      created(){
        this.load('');
        this.loadTypeData();
      },
      data: {
        menuType:'topMenu',
        itemSysID: '',
        itemName: '',
        itemType: 'category',
        itemTargetID:'',
        urlID:'',
        itemOrder:0,
        itemParent:'',
        itemTab1:'',
        itemTab2:'',
        errors:'',
        template:'',
        nest:1,
        itemImgurl:'',
        itemMeta:'',        
        toAllLang:true,
        forceRemark:false,
        parentOptionData: {},
        itemTypeOptionData: {}
      },
      computed: {
           parentOption: {
                get:function(){
                  return this.parentOptionData[this.menuType];
                },
                set:function(){}
           },
           itemTypeOption: {
                get:function(){
                  return this.itemTypeOptionData[this.itemType];
                },
                set:function(){}
           }
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
            init.setInput(res);
            app.loadTypeData();
            if(type=="copy"){
              app.$data['itemSysID'] = '';
            }
          });
        },
        delData: function (hash){
          var r = confirm("請確認是否刪除?");
          if (r == true) {
            submit('#hide-msg',init.setUrl(window.location.href)+'/del/'+hash,'json','none','',function(res){
              if(res.code=="200"){
                app.reload();
              }
            });
          }
        },
        loadTree: function (){//載入樹狀結構，並更新parent menu option
          demo.loadTree(app.$data.menuType);
        },
        loadTypeData: function (){//根據指向類型(文章/分類/標籤...)動態載入option
          submit('#hide-msg',init.setUrl(window.location.href)+'/loadTypeData/'+this.$data.itemType,'json','none','',function(res){
            app.$data['itemTypeOptionData'] = res.data['itemTypeOptionData'];
          });
        },
        reload: function (){
          app.load('');
          demo.loadTree(app.$data.menuType);
          submit('#sys-msg','<?php echo base_url(); ?>manage/manage_menu/rebuild','json');
        }
      },
      components: {
        //'button-counter': MenuItem
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
      loadItemData: function (hash,nest) {
        app.$data['nest'] = nest;
        app.load(hash);
      },
      copyItemData: function (hash,nest) {
        app.$data['nest'] = nest;
        app.load(hash,'copy');
      },
      delData: function (hash) {
        app.delData(hash);
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
        this.loadTree(app.$data.menuType);
    },
    methods: {
      loadTree: function (e){
        submit('#hide-msg',init.setUrl(window.location.href)+'/loadCateTree/'+app.$data.menuType,'json','none','',function(res){
            demo.$data['treeData'] = res.data;
            app.$data['parentOptionData'] = res.data['parentOptionData'];
          });
      }
    }
  })
</script>
