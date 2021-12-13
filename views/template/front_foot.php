<!-- ªí§À¸ê°T (ex. Copyright, Contact Info, ...) -->
<?php
	// Custom JS Files
	if( isset($this->my_template->asset['footer']['js'])) {
		foreach($this->my_template->get_js('footer') as $js_file) {
			echo $js_file.PHP_EOL;
		}
	}
?>  
<?php 
  echo FOOT_META;
?>
</div>

</body>

<script type="text/javascript">

  $(document).ready(function() {
    $('.nav-item-top').on('click',function(){
      $(this).find('.dropdown-menu').addClass('show');
    });
    window.onscroll = function() {
      if($(window).width()>1024){
        if(window.pageYOffset>50){
          $('#topMenu').addClass('navbar-sm');
        }else{
          $('#topMenu').removeClass('navbar-sm');
        }
      }
    };
  });

  var nav = new Vue({
    el: '#topMenu',
    created: function (){
    },
    data: {
      menuTree: null,
      show:false,
      <?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash();?>'
    },
    methods:{
      submit: function (e) {
      },
      callAjax: function (data){
        submit('#hide-msg',init.setUrl('<?php echo base_url(); ?>')+'portal/getMenu/topMenu','json',data,'',function(res){
          nav.$data.menuTree = res.data;
          $(document).ready(function() {
              initMenu();
              $('.nav-item').on('click',function(){
          $(this).find('dropdown-menu').addClass('show');});
          });
        });
      },
      loadPost: function (url){
        console.log(url);
        window.location.href = url;
      },
      showTab: function(targetID,targetID2){
        if(targetID!=''){
          $('#v'+targetID).addClass('show active');
          $('#v'+targetID).css('display','block'); 
        }
        if(targetID2!=''){
          this.chgImage(targetID2);
        }
      },
      hideTab: function(targetID,targetID2){
      },
      chgImage:function(targetID){
        $('.dropdown-sub-meta-img').hide();
        $('#v'+targetID).fadeIn('fast');
      }
    },
    mounted: function (){
      setTimeout(function(){
        nav.$data['show'] = true;
      }, 200);
    }
  });
  
  function initMenu(){
    //處理上方menu滑入
    var w = $(window).width();
    var h = $(window).height();

    if(w<991){
      setTimeout(function(){
        $('.dropdown-content').removeClass('dropdown-content').addClass('dropdown-menu');

      }, 250);
    }else{
      $('.dropdown-content').show();
      $('.tab-pane-hide').hide();
      $('.nav-side-items').hover(function() {
        $('.tab-pane-hide').hide();
        var str = this.href;
        var targetID = str.substring(str.indexOf('#'));
        $(this).tab('show');
        $(targetID).css('display','block'); 
        //$('.dropdown-sub-meta-img').hide();
        $(targetID+'-1').fadeIn('fast');
      });
    }
  }
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script type="text/javascript" src='<?php echo base_url(); ?>assets/vueInit.js'></script>

</html>


