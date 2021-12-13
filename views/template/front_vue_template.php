<template id="front-banner">
  <div class="top-banner d-none d-lg-block animated fadeIn" style="animation-duration: 0.3s;" v-if="data" v-bind:style="{ backgroundImage: 'url(' + data.imgurl + ')' }" >
      <div class="container">
        <div class="row col-12">
          <div class="col-lg-6 col-sm-12 top-title" v-html="data.name"></div>
          <div class="col-lg-6 col-sm-12 top-desc" v-html="data.desc"></div>
        </div>
      </div>
  </div>
  <div class="top-banner" style="height:20rem" v-if="data==null"></div>
</template>

<template id="left-menu">
  <div>
      <div class="" v-for="cateMenu in data">
        <!-- 不開放連結分類 -->
        <div class="card-header cate-header active" v-if="cateMenu.active==true&&cateMenu.template=='none'">{{cateMenu.name}}</div>
        <!-- 顯示品牌logo -->
        <div class="card-header flex-center" style="background: #fff;text-align: center" v-else-if="cateMenu.active==true&&cateMenu.template=='brand-logo'"><img v-if="cateMenu.iconurl" style="height:50px" v-bind:src="cateMenu.iconurl"></div>
        <!-- 其他分類 -->
        <a class="cate-header-link" v-bind:href="cateMenu.url" v-else="" :title="cateMenu.name"><div class="card-header cate-header active" v-if="cateMenu.active==true">{{cateMenu.name}}</div></a>
        <div class="sub-menu cate-menu" v-if="cateMenu.active==true&&cateMenu.children">
            <ul class="list-group">
              <div v-for="cateList in cateMenu.children">
                <li class="list-group-item active" v-if="cateList.active==true"><a v-bind:href="cateList.url" :title="cateList.name">{{cateList.name}}</a></li>
                <li class="list-group-item" v-if="cateList.active==null"><a v-bind:href="cateList.url" :title="cateList.name">{{cateList.name}}</a></li>
              </div>
            </ul>
        </div>
        <a class="sub-menu cate-header-link" v-bind:href="cateMenu.url"><div class="card-header cate-header" v-if="cateMenu.active==null&&cateMenu.parentID!=0" :title="cateMenu.name">{{cateMenu.name}}</div></a>
      </div>
  </div>
</template>