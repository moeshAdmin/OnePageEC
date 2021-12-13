
<template id="product-line-list-no-menu">
  <div>
    
    <h4 style="font-weight: bold;background: #E7EBF3;padding: 10px 30px;margin: 30px -30px;">{{data.name}}</h4>
    <center><a :href="data.url"><img v-if="data.imgurl" :src="data.imgurl" style="width:100%;padding-bottom: 30px;"></a></center>
    <div :id="'product-scroll-'+data.sys" style="overflow: auto;scroll-behavior: smooth">
      <div class="product-line-row-frame" style="margin: 0px;padding: 0px;">
        <div class="product-line-row" :id="'row-'+data.sys">
          <section class="product-card" style="max-width: 300px;float: left;margin: 5px;min-width: 150px;" v-for="(contentItem,index,value) in data.children" v-if="index<5">
            <div class="card mb-4 product-list border" style="transition: all 0.3s linear;min-height: 350px;">
              <div class="card-img" style="min-height: 150px;max-height: 150px;">
                <a v-bind:href="contentItem.url">
                  <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
              </div>
              <div class="card-body">
                <h5 style="font-weight: bold;">{{contentItem.name}}</h5>
                <h6 v-if="contentItem.meta&&contentItem.meta.productMeta">{{contentItem.meta.productMeta}}</h6>
                <h6 v-else>{{contentItem.parentName}}</h6>
                <p class="d-none d-lg-block product-line-meta" v-if="contentItem.keyfeature">
                  {{contentItem.keyfeature}}
                </p>
                <div class="product-line-meta-bg"></div>
              </div>
              <div class="product-list-btn"><a v-bind:href="contentItem.url"><button type="button" class="btn btn-sm btn-danger"><?php echo BTN_READMORE; ?></button></a></div>
            </div>
          </section>

          <section class="product-card" v-if="data.children.length>5" style="max-width: 300px;float: left;margin: 5px;min-width: 150px;width:300px;">
            <div class="card mb-4 product-list border" style="transition: all 0.3s linear;min-height: 350px;">
              <div class="card-img" style="width:150px;min-height: 150px;max-height: 150px;">
                <a href="">
                  <img style="max-height: 100px;" src=""></a>
              </div>
              <div class="card-body">
                <h5 style="font-weight: bold;">{{data.name}}</h5>
                <h6 style="color:#fff">.</h6>
                <p class="d-none d-lg-block product-line-meta" style="opacity: 0">
                  {{data.name}}
                </p>
                <div class="product-line-meta-bg"></div>
              </div>
              <div class="product-list-btn"><a :href="data.url"><button type="button" class="btn btn-sm btn-danger">See More</button></a></div>
            </div>
          </section>

        </div>
        <div v-if="data.children.length>4">
          <div class="btn btn-light scroll-btn" style="right: 0;" @click="scrollProduct('right','product-scroll-'+data.sys)">></div>
          <div class="btn btn-light scroll-btn" style="left: 0;" @click="scrollProduct('left','product-scroll-'+data.sys)"><</div>
        </div>
      </div>
      
    </div>
    
  </div>
</template>

<template id="product-line-list-no-menu-xx">
  <div>
    <h4 style="font-weight: bold;background: #E7EBF3;padding: 10px 30px;margin: 30px -30px;">{{data.name}}</h4>
    <center><img v-if="data.imgurl" :src="data.imgurl" style="width:100%;padding-bottom: 30px;"></center>
    <div class="row product-line-row" :id="'row-'+data.sys">
      <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-3" v-for="contentItem in data.children">
        <div class="card mb-4 product-list border" style="transition: all 0.3s linear;min-height: 350px;">
          <div class="card-img" style="min-height: 150px;max-height: 150px;">
            <a v-bind:href="contentItem.url">
              <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
          </div>
          <div class="card-body">
            <h5 style="font-weight: bold;">{{contentItem.name}}</h5>
            <p class="d-none d-lg-block product-line-meta" v-if="contentItem.keyfeature">
              {{contentItem.keyfeature}}
            </p>
            <div class="product-line-meta-bg"></div>
          </div>
          <div class="product-list-btn"><a v-bind:href="contentItem.url"><button type="button" class="btn btn-sm btn-danger"><?php echo BTN_READMORE; ?></button></a></div>
        </div>
      </div>
      <div :id="'btn-'+data.sys" class="product-line-row-bg" v-if="data.children.length>4">
        <button type="button" class="btn btn-outline-danger" @click="window.location = data.url;">Show More</button>
      </div>
      <div :id="'btn-'+data.sys" v-else style="position: inherit;">　</div>
    </div>
  </div>
</template>
