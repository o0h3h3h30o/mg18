<!-- Footer -->
<div class="iedu_footer">
  <div class="container">
    <div class="row">
      <div class="col-md-5 col-sm-4 col-xs-12">
        <div class="footer_left">
          <div class="logo">
            <a href="<?=base_url()?>"><img src="/logo18.png?v=1.1" style="height: 65px !important;" alt="" class="img-responsive"></a>
          </div>
          <p>All content on https://manga18.club and https://manga18.us is collected on the internet. So there are any issues regarding selling rights, please contact me directly at the email address contact@manga18.club If your request is reasonable we will remove it immediately. Sincerely thank you !!</p>
          <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  '' : '' ?></p>
        </div>
      </div>
      <div class="col-sm-7 col-sm-8 col-xs-12">
        <div class="footer_right">
          <div class="footer_block">
            <div class="footer_title">
              <h5>Manga List</h5>
            </div>
            <div class="footer_cate">
              <ul>
                <!-- <li><a href="https://mangatx.cc">Manhua Hot</a></li> -->
                <li><a href="/18usc.html">18 U.S.C. 2257 Compliance Statement</a></li>
                <!-- <li><a href="<?=base_url()?>list-manga?order_by=lastest">Lastest Release</a></li> -->
                
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>
<span href="#" id="back_to_top" title="Back to top" >↑</span>
<style type="text/css">
#back_to_top {
    position: fixed;
    bottom: 40px;
    right: 10px;
    z-index: 9999;
    width: 48px;
    height: 48px;
    text-align: center;
    line-height: 48px;
    background: #333;
    color: #fff;
    cursor: pointer;
    border: 0;
    font-size: 32px;
    border-radius: 2px;
    text-decoration: none;
    transition: opacity .2s ease-out;
    opacity: 0;
}
#back_to_top.show {
    opacity: 1;
}
</style>
<script type="text/javascript">
    $(document).ready(function () {



        $('.menu-ico').click(function () {
            if($('.header-menu').css('display') == 'none'){
                $('.open-menu').css('display','none');
                $('.close-menu').css('display','block');
                $('.header-menu').css('display','block');
                $('.open-search').css('display','block');
                $('#searchmb').css('display','none');
                $('.close-search').css('display','none');
            }else{
                $('.open-menu').css('display','block');
                $('.close-menu').css('display','none');
                $('.header-menu').css('display','none');
            }
        });

        $('.search-ico').click(function () {
            if($('.header-mb-search').css('display') == 'none'){
                $('.open-search').css('display','none');
                $('.header-menu').css('display','none');                
                $('.close-search').css('display','block');
                $('.header-mb-search').css('display','block');
            }else{
                $('.open-search').css('display','block');
                $('.close-search').css('display','none');
                $('.header-mb-search').css('display','none');
            }
        });

        $('.dropdownmenu').click(function () {
            if($('.sub-menu').css('display') == 'none'){
                $('.sub-menu').css('display','block');
            }else{
                $('.sub-menu').css('display','none');
            }
        });

        $('.dropdownmenumb').click(function () {
            if($('.sub-menumb').css('display') == 'none'){
                $('.sub-menumb').css('display','block');
            }else{
                $('.sub-menumb').css('display','none');
            }
        });

        if($('#back_to_top').length) {
            var scrollTrigger = 100, // px
                backToTop = function () {
                    var scrollTop = $(window).scrollTop();
                    if (scrollTop > scrollTrigger) {
                        $('#back_to_top').addClass('show');
                    } else {
                        $('#back_to_top').removeClass('show');
                    }
                };
            backToTop();
            $(window).on('scroll', function () {
                backToTop();
            });
            $('#back_to_top').on('click', function (e) {
                e.preventDefault();
                $('html,body').animate({
                    scrollTop: 0
                }, 700);
            });
        }
        // Load notifications (server-rendered, no API needed for user state)
        <?php if($is_logged): ?>
        $.getJSON("/notifications", function(data) {
            if (data.status == 1 && data.notifications.length > 0) {
                var html = '';
                var unread = 0;
                for (var k in data.notifications) {
                    var n = data.notifications[k];
                    var cls = n.is_read == 0 ? 'notif-unread' : '';
                    if (n.is_read == 0) unread++;
                    var icon = n.icon_class || 'ti-bell';
                    html += '<li class="' + cls + '"><a href="/notification/go/' + n.id + '">'
                        + '<span class="notif-icon"><i class="' + icon + '"></i></span>'
                        + '<span class="notif-info">'
                        + '<strong>' + n.title + '</strong>'
                        + (n.message ? '<br><small>' + n.message + '</small>' : '')
                        + '<br><span class="notif-time">' + n.time_ago + '</span>'
                        + '</span>'
                        + '</a></li>';
                }
                $('.notif-dropdown .notif-body ul').html(html);
                $('.notif-dropdown .notif-empty').hide();
                if (unread > 0) {
                    $('.notify_count, .notify_count_mb').text(unread).show();
                }
            }
        });
        <?php endif; ?>

        // Helper: mark all notifications as read
        function markAllNotifRead() {
            $.ajax({
                url: '/notifications/read',
                type: 'POST',
                dataType: 'json',
                success: function() {
                    $('.notify_count, .notify_count_mb').text('0').hide();
                    $('.notif-dropdown .notif-body ul').empty();
                    $('.notif-dropdown .notif-empty').show();
                }
            });
        }

        // Bell click — toggle dropdown (desktop)
        $(document).on('click', '.bell-trigger', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#notifications-mb').hide();
            $('#notifications').toggle();
        });

        // Bell click — toggle dropdown (mobile)
        $(document).on('click', '.bell-trigger-mb', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#notifications').hide();
            $('#notifications-mb').toggle();
        });

        // "Mark all read" button (desktop)
        $(document).on('click', '.notif-mark-read', function(e) {
            e.preventDefault();
            e.stopPropagation();
            markAllNotifRead();
        });

        // "Mark all read" button (mobile)
        $(document).on('click', '.notif-mark-read-mb', function(e) {
            e.preventDefault();
            e.stopPropagation();
            markAllNotifRead();
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.bell-trigger, #notifications').length) {
                $('#notifications').hide();
            }
            if (!$(e.target).closest('.bell-trigger-mb, #notifications-mb').length) {
                $('#notifications-mb').hide();
            }
        });
    });

    // $('.c-user_avatar-image').click(function () {
    //     if($('.c-user_menu').css('display') == 'none'){
    //         $('.c-user_menu').css('display','block');
    //     }else{
    //         $('.c-user_menu').css('display','none');
    //     }
    // });

    $("#searchpc input[type='text']").on('input', function() {
        $('.live-pc-result').css('display','block');
    });
    $("#searchmb input[type='text']").on('input', function() {
        $('.live-mb-result').css('display','block');
    });

    window.addEventListener('click', function(e){
        // if (document.getElementById('searchpc').contains(e.target)){
        //     // $('.live-pc-result').css('display','block');
        // } else{
        //     $('.live-pc-result').css('display','none');
        // }
    });

    window.addEventListener('click', function(e){
        if (document.getElementById('searchmb').contains(e.target)){
            // $('.live-mb-result').css('display','block');
        } else{
            $('.live-mb-result').css('display','none');
        }
    });

    // window.addEventListener('click', function(e){
    //     if (document.getElementById('user_avatar_image').contains(e.target)){
    //         // $('.live-pc-result').css('display','block');
    //     } else{
    //         $('.c-user_menu').css('display','none');
    //     }
    // });
    var elm = document.getElementsByTagName('a')
    var length = elm.length;
    for (var i = 0; i < length; i++) {
      elm[i].className = elm[i].className + " click_hilltop_click";
    }
    $('.rank_tab li a').removeClass('click_hilltop_click');


    document.querySelectorAll('a').forEach(a => {
      if (a.href.includes('https://mangaclan.net/dis.html?url=')) {
        const urlObj = new URL(a.href);                  // Tạo đối tượng URL từ chuỗi href
        const realUrl = urlObj.searchParams.get('url');  // Lấy giá trị tham số 'url'
        if (realUrl) {
          a.href = realUrl;                              // Gán lại href là URL gốc
        }
      }
    });


    // Lazy load images
    (function(){
      var lazyImages = document.querySelectorAll('img.lazy');
      if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              var img = entry.target;
              img.src = img.dataset.src;
              img.classList.remove('lazy');
              observer.unobserve(img);
            }
          });
        }, { rootMargin: '200px' });
        lazyImages.forEach(function(img) { observer.observe(img); });
      } else {
        // Fallback: load all
        lazyImages.forEach(function(img) { img.src = img.dataset.src; });
      }
    })();

</script>
<!-- End Footer -->
<!-- Lib js -->
<script src='<?=base_url()?>manga-club/js/jqueryui110.js'></script>
  <script src="/vendor/js/bootstrap-3.3.7.min.js"></script>
  <script src="/vendor/js/owl.carousel.min.js"></script>
  <!-- Custom js -->
  <script type="text/javascript" src="<?=base_url()?>manga-club/js/custom.js?v=1.1"></script>

  <script type="text/javascript">
(function($){

  var $project = $('.search_complete');

  $project.autocomplete({
    minLength: 0,
    source: function (request, response) {
        jQuery.getJSON("/search", {
            search: request.term
        }, function (data) {            
            response(data.data);
        });
    }
  });
  
  $project.data( "ui-autocomplete" )._renderItem = function( ul, item ) {

    var $li = $('<li>'),
    $img = $('<img>');


    $img.attr({
      src: '<?=$cdnUrl?>/manga/'+item.slug+'/cover/cover_thumb.jpg',
      alt: item.name
    });

    $li.attr('data-value', item.name);
    $li.append('<a href="<?=base_url().'manhwa/';?>'+item.slug+'">');
    $li.find('a').append($img).append("<p>" + item.name + "</p>" );    

    return $li.appendTo(ul);
  };
  

})(jQuery);

(function($){

  var $project = $('.search_complete2');

  $project.autocomplete({
    minLength: 0,
    source: function (request, response) {
        jQuery.getJSON("/search", {
            search: request.term
        }, function (data) {            
            response(data.data);
        });
    }
  });
  
  $project.data( "ui-autocomplete" )._renderItem = function( ul, item ) {

    var $li = $('<li>'),
    $img = $('<img>');


    $img.attr({
      src: '<?=$cdnUrl?>/manga/'+item.slug+'/cover/cover_thumb.jpg',
      alt: item.name
    });

    $li.attr('data-value', item.name);
    $li.append('<a href="<?=base_url().'manhwa/';?>'+item.slug+'">');
    $li.find('a').append($img).append("<p>" + item.name + "</p>" );    

    return $li.appendTo(ul);
  };
  

})(jQuery);
  </script>
  <script type="text/javascript">
      $('#btnSearch').click(function(){
        var name_search = $('.search_complete').val().trim();
        if(name_search) window.location.href = '/list-manga?search='+encodeURIComponent(name_search);
      });
      $('.search_complete, .search_complete2').on('keypress', function(e){
        if(e.which === 13){
          var name_search = $(this).val().trim();
          if(name_search) window.location.href = '/list-manga?search='+encodeURIComponent(name_search);
        }
      })
  </script>


<?php if (!empty($is_admin)): ?>
<div id="adminDebugToggle" style="position:fixed;bottom:8px;left:8px;z-index:99999;">
  <button onclick="document.getElementById('adminDebugPanel').style.display=document.getElementById('adminDebugPanel').style.display==='none'?'block':'none'" style="background:#222;color:#0f0;border:1px solid #0f0;border-radius:4px;padding:4px 10px;font-size:11px;cursor:pointer;opacity:0.7;">🐛 Debug</button>
</div>
<div id="adminDebugPanel" style="display:none;position:fixed;bottom:40px;left:8px;z-index:99999;background:#111;color:#0f0;border:1px solid #333;border-radius:8px;padding:12px 16px;font-family:monospace;font-size:12px;max-width:500px;max-height:400px;overflow:auto;box-shadow:0 4px 20px rgba(0,0,0,0.8);">
  <div style="margin-bottom:6px;color:#ff0;font-weight:bold;">Admin Debug Panel</div>
  <div><b>IP:</b> <?= service('request')->getServer('REMOTE_ADDR') ?></div>
  <div><b>X-Real-IP:</b> <?= service('request')->getServer('HTTP_X_REAL_IP') ?? 'N/A' ?></div>
  <div><b>X-Forwarded-For:</b> <?= service('request')->getServer('HTTP_X_FORWARDED_FOR') ?? 'N/A' ?></div>
  <div><b>CF-Connecting-IP:</b> <?= service('request')->getServer('HTTP_CF_CONNECTING_IP') ?? 'N/A' ?></div>
  <div><b>Session ID:</b> <?= session_id() ?: 'N/A' ?></div>
  <div><b>User:</b> <?= $user_info->username ?? 'N/A' ?> (ID: <?= $user_info->id ?? '?' ?>)</div>
  <div><b>Render:</b> {elapsed_time}s</div>
  <div><b>PHP:</b> <?= PHP_VERSION ?></div>
  <div><b>CI:</b> <?= \CodeIgniter\CodeIgniter::CI_VERSION ?></div>
  <div><b>Env:</b> <?= ENVIRONMENT ?></div>
  <div><b>Server Time:</b> <?= date('Y-m-d H:i:s T') ?> (<?= date_default_timezone_get() ?>)</div>
  <div style="margin-top:6px;color:#888;font-size:10px;">Only visible to admin</div>
</div>
<?php endif; ?>

</body>
</html>