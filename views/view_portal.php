
<div id="main-front" style="min-height: 1080px;">
  <div id="screen"></div>
  <section v-for="(frontItem,index,key) in menuTree">
    <!-- free-block -->
    <article class="album" :id="'free-'+index" v-if="frontItem.template=='free-block'" v-html="frontItem.meta">
    </article>

    <!-- item模組 -->
    <div class="album" style="margin-top:30px;" v-if="frontItem.template=='item-card'">
      <div class="container">
        <div class="row">
          <?php 
            echo $cardElement;
          ?>
        </div>
      </div>
    </div>

    <div class="album" v-if="index==0"></div>
  </section>
    
  <div class="scroll-top-btn">
    <button type="button" class="btn btn-secondary btn-sm" @click="scrollTop">▲</button>
  </div>
</div>

<script>
  var app = new Vue({
    el: '#main-front',
    created: function (){
      this.callAjax(this.$data);
    },
    data: {
      carousel:true,
      inno:true,
      block1:true,
      block2:true,
      block3:true,
      block4:true,
      menuTree:null,
      num:0,
      ctn:''
    },
    
    methods:{
      submit: function (e) {
        //submit('#sys-msg',window.location.href+'/Portal/test','json',app.$data);
        //e.preventDefault();
      },
      callAjax: function (data){
        submit('#hide-msg',init.setUrl('<?php echo base_url(); ?>')+'portal/getMenu/front','json',data,'',function(res){
          app.$data.menuTree = res.data;
        });
      },
      setInput: function (res){
        $.each(res.data, function(index) {
            app.$data[index] = res.data[index];
        });
      },
      close: function (target){
        
      },
      scrollTop:function(){
        $('body').animate({scrollTop: 0}, 600);
      }
    },
    mounted: function () {
    }
  });
</script>



