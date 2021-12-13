<template id="news-list">
  <div class="row">
    <div class="col-12" v-for="contentCate in data.children">
      <h4 style="font-weight: bold;background: #ccc;padding: 10px 30px;margin: -30px;margin-bottom: 20px;">{{contentCate.name}}</h4>
      <div class="row" style="padding-bottom:30px;">
      <section class="col-md-12" v-for="contentItem in contentCate.children">
        <div class="row mb-4 product-list border" style="height:auto">
          <div class="col-md-4">
            <div class="card-body card-img" style="min-height: 150px;">
              <a v-bind:href="contentItem.url">
              <img style="width:100%" v-bind:src="contentItem.iconurl"></a>
            </div>
          </div>
          <div class="col-md-8">
            <div class="card-body">
              <h5 style="font-weight: bold;"><a v-bind:href="contentItem.url">{{contentItem.name}}</a></h5>
              <h6>{{contentItem.date}}</h6>
              <p style="min-height: 100px;" v-if="contentItem.desc">{{contentItem.desc}}</p>
            </div>
          </div>
        </div>
      </section>
      </div>
    </div>
  </div>
</template>