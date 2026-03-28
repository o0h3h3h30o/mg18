$('.mg_update-owl').owlCarousel({
  loop:false,
  margin:25,
  nav:true,
  navText: ["<i class='ti-angle-left' aria-hidden='true'></i>",
  "<i class='ti-angle-right' aria-hidden='true'></i>"],
  autoplay:false,
  autoplayTimeout: 4000,
  smartSpeed: 1000,
  responsiveClass:true,
  dots: false,
  responsive:{

    0:{

      items:2,
      nav:true,
    },
    480:{

      items:2,
      nav:true,
    },
    544:{

      items:3,
      nav:true,
    },
    768:{

      items:4,

    },
    992:{

      items:6,
    }
  }
});
/*** MENU ASIDE ***/
jQuery(function($) {
  "use strict";
  $(".grid-cate").accordion({
    accordion:false,
    speed: 400,
    closedSign: '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    openedSign: '<i class="fa fa-angle-down" aria-hidden="true"></i>'
  });
});

(function($){
  $.fn.extend({ 
    accordion: function(options) {  
      var defaults = {
        accordion: 'true',
        speed: 400,
        closedSign: '[-]',
        openedSign: '[+]'
      };  
      var opts = $.extend(defaults, options); 
      var $this = $(this);  
      $this.find("li").each(function() {
        if($(this).find("ul").size() != 0){
          $(this).find("a:first").after("<em>"+ opts.closedSign +"</em>");  
          if($(this).find("a:first").attr('href') == "#"){
            $(this).find("a:first").click(function(){return false;});
          }
        }
      }); 
      $this.find("div:not('.grid-cate') li em, li:not('.grid-cate') em").click(function() {
        if($(this).parent().find("ul").size() != 0){
          if(opts.accordion){
            if(!$(this).parent().find("ul").is(':visible')){
              parents = $(this).parent().parents("ul");
              visible = $this.find("ul:visible");
              visible.each(function(visibleIndex){
                var close = true;
                parents.each(function(parentIndex){
                  if(parents[parentIndex] == visible[visibleIndex]){
                    close = false;
                    return false;
                  }
                });
                if(close){
                  if($(this).parent().find("ul") != visible[visibleIndex]){
                    $(visible[visibleIndex]).slideUp(opts.speed, function(){
                      $(this).parent("li").find("em:first").html(opts.closedSign);
                    });   
                  }
                }
              });
            }
          }
          if($(this).parent().find("ul:first").is(":visible")){
            $(this).parent().find("ul:first").slideUp(opts.speed, function(){
              $(this).parent("li").find("em:first").delay(opts.speed).html(opts.closedSign);
            }); 
          }else{
            $(this).parent().find("ul:first").slideDown(opts.speed, function(){
              $(this).parent("li").find("em:first").delay(opts.speed).html(opts.openedSign);
            });
          }
        }
      });
    }
  });
})(jQuery);

// menu mobile
$(document).ready(function(){
  $(".mobile_toggle").click(function() {
    $(".header_mobile__content,.bg_overlay").addClass('fopen');
  });
  $(".mobile_close, .bg_overlay").click(function() {
    $(".header_mobile__content,.bg_overlay").removeClass('fopen');
  });
});

$(document).ready(function(){
  $(".sort_button").click(function() {
    $(".sort_mobile__content, .bg_overlay").addClass('fopen');
  });
  $(".mobile_close, .bg_overlay").click(function() {
    $(".sort_mobile__content,.bg_overlay").removeClass('fopen');
  });
});





// tooltip
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip(); 
});


// Toggle Js
$("#sort_extra").click(function() {
  $(".sort_extra-content").toggle();
});

// Show hiden more chapter
$(document).ready(function(){
  $(".chapter_more").click(function() {
    $(".chapter_box>ul>li").addClass('show');
    $(".chapter_more").addClass('hide');
    $(".chapter_box>ul>li").removeClass('hide');
  });
});

// $(document).ready(function(){
//   $('.story_images .img-responsive, .image_story .img-responsive').lazyload();
// });


/*Mượt scroll*/
$(document).ready(function() {
  $("a[href*='#x']:not([href='#])").click(function() {
    let target = $(this).attr("href");
    $('html,body').stop().animate({
      scrollTop: $(target).offset().top
    }, 1000);
    event.preventDefault();
  });
});

// cố định menu
window.onscroll = function() {myFunction()};

var header = document.getElementById("menu_fixed");
var sticky = header.offsetTop;

function myFunction() {
  if (window.pageYOffset > sticky) {
    // header.classList.add("sticky");
    $(".back_top").show();
  } else {
    header.classList.remove("sticky");
    $(".back_top").hide();
  }
}

/*Auto complete*/
