
<template id="product-line-list">
  <div style="margin-top: -60px;">
    <h4 style="font-weight: bold;background: #E7EBF3;padding: 10px 30px;margin: 30px -30px;">{{data.name}}</h4>
    <div class="row product-line-row" :id="'row-'+data.sys">
      <section class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-4" v-for="contentItem in data.children">
        <div class="card mb-4 product-list border" style="transition: all 0.3s linear;z-index: 0">
          <div class="card-img" style="min-height: 150px;max-height: 150px;">
            <a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name">
              <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
          </div>
          <div class="card-body">
            <h5 style="font-weight: bold;">{{contentItem.name}}</h5>
            <h6 v-if="contentItem.meta&&contentItem.meta.productMeta">{{contentItem.meta.productMeta}}</h6>
            <h6 v-else>{{data.name}}</h6>
            <p class="d-none d-lg-block product-line-meta" v-if="contentItem.keyfeature">
              {{contentItem.keyfeature}}
            </p>
            <div class="product-line-meta-bg"></div>
          </div>
          <div class="product-list-btn"><a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name"><button type="button" class="btn btn-sm btn-danger"><?php echo BTN_READMORE; ?></button></a></div>
        </div>
      </section>
      <div :id="'btn-'+data.sys" class="product-line-row-bg btn-showmore" v-if="data.children.length>3">
        <button type="button" class="btn btn-outline-danger" @click="showFull(data.sys)">Show More</button>
      </div>
    </div>
  </div>
</template>
