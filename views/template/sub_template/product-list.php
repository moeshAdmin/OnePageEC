<template id="product-list">
  <div class="row">
    <section class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-4" v-for="contentItem in data.children">
      <div class="card mb-4 product-list border" >
        <div class="card-img" style="min-height: 150px;">
          <a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name">
            <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
        </div>
        <div class="card-body">
          <h4 style="font-weight: bold;">{{contentItem.name}}</h4>
          <h6>{{data.name}}</h6>
          <p class="d-none d-lg-block" style="min-height: 100px;max-height: 100px;overflow:hidden;">{{contentItem.keyfeature}}</p>
          <div class="product-line-meta-bg"></div>
        </div>
        <div class="product-list-btn"><a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name"><button type="button" class="btn btn-sm btn-danger" :title="contentItem.name+' '+data.name"><?php echo BTN_READMORE; ?></button></a></div>
      </div>
    </section>
    <section class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-4 d-none d-lg-block" v-if="data.children.length==1||(data.children.length==2&&index==0)" v-for="(contentItem,index,value) in data.children" style="opacity: 0">
      <div class="card mb-4 product-list border" >
        <div class="card-img" style="min-height: 150px;">
          <a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name">
            <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
        </div>
        <div class="card-body">
          <h4 style="font-weight: bold;">{{contentItem.name}}</h4>
          <h6>{{data.name}}</h6>
          <p class="d-none d-lg-block" style="min-height: 100px;max-height: 100px;overflow:hidden;">{{contentItem.keyfeature}}</p>
          <div class="product-line-meta-bg"></div>
        </div>
        <div class="product-list-btn"><a v-bind:href="contentItem.url"><button type="button" class="btn btn-sm btn-danger"><?php echo BTN_READMORE; ?></button></a></div>
      </div>
    </section>
    <section class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-4" v-if="data.children.length==1" v-for="(contentItem,index) in data.children" v-if="index==0" style="opacity: 0">
      <div class="card mb-4 product-list border" >
        <div class="card-img" style="min-height: 150px;">
          <a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name">
            <img style="max-height: 100px;" v-bind:src="contentItem.iconurl"></a>
        </div>
        <div class="card-body">
          <h4 style="font-weight: bold;">{{contentItem.name}}</h4>
          <h6>{{data.name}}</h6>
          <p class="d-none d-lg-block" style="min-height: 100px;max-height: 100px;overflow:hidden;">{{contentItem.keyfeature}}</p>
          <div class="product-line-meta-bg"></div>
        </div>
        <div class="product-list-btn"><a v-bind:href="contentItem.url" :title="contentItem.name+' '+data.name"><button type="button" class="btn btn-sm btn-danger"><?php echo BTN_READMORE; ?></button></a></div>
      </div>
    </section>
  </div>
</template>