
<template id="product-line-no-menu-banner-only">
  <div class="row" style="padding-top: 20px;">
    <div class="col-md-12">
        <h4 style="font-weight: bold;background: #E7EBF3;padding: 10px;text-align: center;font-size: 12pt">{{data.name}}</h4>
        <a href="#" @click="window.location = data.url;">
            <div :style="'background-image: url('+data.iconurl+');width:100%;padding-bottom: 30px;min-height:270px;background-position: center;background-repeat: no-repeat;background-size: cover;'"></div>
            
        </a>
    </div>
  </div>
</template>