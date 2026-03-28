<!DOCTYPE html>
<html lang="en">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

      <meta name="language" content="en-us">
      <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
      <title><?=$heading_title?></title>
      <link rel="icon" type="image/png" href="/fav.png?v=1" sizes="128x128">
      <!-- <meta name="referrer" content="no-referrer-when-downgrade" /> -->
      <meta name="title" content="<?=$heading_title?>">
      <meta name="description" content="<?=$seo_description?>">
      <meta name="keywords" content="<?=$seo_keyword?>">
     <?php
        // Lấy URL hiện tại không có query string
        $canonical = current_url();
        $canonical = strtok($canonical, '?'); // Xoá query string nếu có
    ?>
      <link rel="canonical" href="<?= $canonical ?>" />
      <meta property="og:type" content="website">
      <meta property="og:url" content="<?=base_url();?>">
      <meta property="og:title" content="<?=$heading_title?>">
      <meta property="og:description" content="<?=$seo_description?>">
      <?php if(isset($seo_image)){ ?>
      <meta property="og:image" content="<?=$seo_image?>">
      <?php } ?>
   
      <meta name="twitter:site" content="Manga18">
      <meta property="twitter:card" content="summary_large_image">
      <meta property="twitter:url" content="https://manga18.club/">
      <meta property="twitter:title" content="<?=$heading_title?>">
      <meta property="twitter:description" content="<?=$seo_description?>">
      <?php if(isset($seo_image)){ ?>
      <meta property="twitter:image" content="<?=$seo_image?>">
      <?php } else { ?>
      <meta property="twitter:image" content="https://manga18.club/manga18.png">
      <?php } ?>
      
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="/vendor/css/font-awesome.min.css">
      <link rel="stylesheet" type="text/css" href="/manga-club/ti-icons/css/themify-icons.css">
      <link rel="stylesheet" href="/vendor/css/linearicons.min.css">
      <!-- Lib css-->
      <link rel='stylesheet' href='/manga-club/css/jqueryui.css'>
      <link rel="stylesheet" href="/vendor/css/bootstrap-3.3.7.min.css">
      <link rel="stylesheet" href="/vendor/css/owl.carousel.min.css">
      <link rel="stylesheet" href="/vendor/css/owl.theme.default.css">
      <link rel='stylesheet' href='/manga-club/css/animate.css'>
      <link rel="stylesheet" href="/vendor/css/jquery.fancybox.min.css" />

      <!-- custom css -->
      <link rel="stylesheet" href="/manga-club/css/custom.css?v=1.1" />
      <link rel="stylesheet" href="/manga-club/css/responsive.css?v=1.1" />

      <!-- jquery -->
      <script src="/vendor/js/jquery-2.2.3.min.js"></script>
      <script>
      $.ajaxSetup({
          beforeSend: function(xhr, settings) {
              if (settings.type === 'POST') {
                  var csrfName = $('meta[name="<?= csrf_token() ?>"]').attr('name');
                  var csrfHash = $('meta[name="<?= csrf_token() ?>"]').attr('content');
                  if (typeof settings.data === 'string') {
                      settings.data += '&' + csrfName + '=' + csrfHash;
                  } else if (typeof settings.data === 'object' && settings.data !== null) {
                      settings.data[csrfName] = csrfHash;
                  } else {
                      settings.data = csrfName + '=' + csrfHash;
                  }
              }
          }
      });
      </script>


      <!--Begin tag--->

<!--End tag-->
    <script>
      var _isLogged = document.cookie.indexOf('is_logged=1') !== -1;
    </script>
    <!-- CF cache safe: only use JS cookie check to toggle nav -->
    <script>
    if(_isLogged){document.write('<style>.guest-nav,.guest-nav-mb{display:none!important}li.user-nav{display:inline-block!important}.user-nav-mb{display:block!important}</style>');}
    </script>
   </head>
   <body style="background: url('<?=base_url()?>bg.jpeg');">


 <style type="text/css">
  .divads {
    overflow: hidden;
    max-width: 100% !important;
  }
</style>
<style type="text/css">
  .img_flag{height:23px;}
  .mg_chapter{height: 50px;}
  .user_flow .column40{width:40%;float:left;}
  .user_flow .column35{width:35%;float:left;}
  .user_flow .column20{width:20%;float:left;}
  @media(max-width:767px){
    .user_flow .column40,.user_flow .column35,.user_flow .column20{width:100%;float:none;}
  }
</style>
<style type="text/css">
  /* Notification bell & badge */
  .notify_count {
    background: #ff523a;
    color: #fff;
    border-radius: 50%;
    padding: 1px 5px;
    font-size: 10px;
    position: relative;
    top: -8px;
    left: -3px;
  }

  /* Shared notification dropdown styles (desktop + mobile) */
  .notif-dropdown {
    display: none;
    position: absolute;
    right: 0;
    width: 360px;
    max-height: 440px;
    background: #1e2a30;
    border: 1px solid #2e3e45;
    border-radius: 14px;
    z-index: 9999;
    box-shadow: 0 8px 30px rgba(0,0,0,.5);
    overflow: hidden;
  }
  #notifications { top: 42px; }
  #notifications-mb { top: 35px; width: 300px; }

  /* Dropdown header */
  .notif-dropdown .notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 18px 12px;
    border-bottom: 1px solid #2e3e45;
  }
  .notif-dropdown .notif-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
  }
  .notif-dropdown .notif-header .notif-mark-read,
  .notif-dropdown .notif-header .notif-mark-read-mb {
    font-size: 13px;
    color: #4ecdc4;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    text-decoration: none;
  }
  .notif-dropdown .notif-header .notif-mark-read:hover,
  .notif-dropdown .notif-header .notif-mark-read-mb:hover {
    color: #7eddd6;
    text-decoration: underline;
  }

  /* Notification list */
  .notif-dropdown .notif-body {
    max-height: 360px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #3a5a60 transparent;
  }
  .notif-dropdown .notif-body::-webkit-scrollbar {
    width: 5px;
  }
  .notif-dropdown .notif-body::-webkit-scrollbar-track {
    background: transparent;
  }
  .notif-dropdown .notif-body::-webkit-scrollbar-thumb {
    background: #3a5a60;
    border-radius: 10px;
  }
  .notif-dropdown .notif-body::-webkit-scrollbar-thumb:hover {
    background: #4ecdc4;
  }
  .notif-dropdown .notif-body ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .notif-dropdown .notif-body ul li a {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    color: #b0bec5;
    border-bottom: 1px solid #2a3940;
    text-decoration: none;
    font-size: 13px;
    line-height: 1.5;
    transition: background .15s;
  }
  .notif-dropdown .notif-body ul li a:hover {
    background: #263238;
    text-decoration: none;
    color: #b0bec5;
  }
  .notif-dropdown .notif-body ul li.notif-unread a {
    background: #1a3a3a;
    color: #e0e0e0;
  }
  .notif-dropdown .notif-body ul li a .notif-icon {
    flex: 0 0 36px;
    height: 36px;
    border-radius: 50%;
    background: #2a3e45;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4ecdc4;
    font-size: 15px;
  }
  .notif-dropdown .notif-body ul li.notif-unread a .notif-icon {
    background: #2a4a4a;
  }
  .notif-dropdown .notif-body ul li a .notif-info {
    flex: 1;
    min-width: 0;
  }
  .notif-dropdown .notif-body ul li a strong {
    color: #e0e0e0;
    font-size: 12px;
    font-weight: 600;
  }
  .notif-dropdown .notif-body ul li.notif-unread a strong {
    color: #fff;
  }
  .notif-dropdown .notif-body ul li a small {
    color: #8a9da5;
  }
  .notif-dropdown .notif-body .notif-time {
    color: #5a7a80;
    font-size: 11px;
  }

  /* Empty state */
  .notif-dropdown .notif-empty {
    padding: 40px 20px;
    text-align: center;
    color: #607d8b;
    font-size: 14px;
  }

  /* View all footer */
  .notif-dropdown .notif-footer {
    border-top: 1px solid #2e3e45;
    text-align: center;
  }
  .notif-dropdown .notif-footer a {
    display: block;
    padding: 12px;
    color: #4ecdc4;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s;
  }
  .notif-dropdown .notif-footer a:hover {
    background: #263238;
    color: #7eddd6;
  }

  /* Comment block spacing */
  .mg_block.mg_comment { margin-bottom: 30px; }

  /* Default: hide logged-in nav (CF serves guest HTML) */
  .header_center__right > ul > li.user-nav, .user-nav-mb { display: none; }
  .user-nav, .user-nav-mb { transition: opacity .2s ease-in; }
  /* Fix layout shift: reserve fixed space for right nav */
  .header_center__right { min-width: 200px; flex-shrink: 0; }
  /* Mobile bell icon */
  .top-header { display: flex; align-items: center; justify-content: space-between; }
  .bell-ico { display: none; }
  @media(max-width:767px){
    .bell-ico.user-nav-mb { display: inline-block; }
  }
</style>


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7Y3LEN2VDG"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-7Y3LEN2VDG');
</script>

      <div id="fb-root"></div>
       
      <div id="xtop"></div>
      <div class="iedu_header" id="menu_fixed">
      <div class="header_desktop hidden-xs">
         <div class="iedu_header__center">
            <div class="container">
               <div class="iedu_center__content">
                  <div class="logo">
                     <a href="/"><img src="/logo18.png" alt="" style="height: 65px !important;" class="img-responsive"></a>
                  </div>
                  <div class="header_search">
                    <!-- <form action="list-manga" method="GET"> -->
                     <input type="text" name="search" placeholder="Search..." class="search_complete">
                     <button type="submit" id='btnSearch'><span class="lnr lnr-magnifier"></span></button>
                     <!-- </form> -->
                  </div>
                  <div class="header_center__right">
                     <ul>
                       
                        <!-- Guest (default, CF cached) -->
                        <li class="guest-nav"><a href="/login">Login</a></li>
                        <li class="guest-nav iedu_register"><a href="/register">Register</a></li>
                        <!-- Logged in (hidden by default, shown by CSS/JS) -->
                        <li class="user-nav" style="position:relative;">
                           <a href="#" class="bell-trigger"><i class="ti-bell"></i><span class="notify_count" style="display:none;">0</span></a>
                           <div id="notifications" class="notif-dropdown">
                              <div class="notif-header">
                                <h4>Notifications</h4>
                                <a href="#" class="notif-mark-read">Mark all read</a>
                              </div>
                              <div class="notif-body">
                                <ul></ul>
                                <div class="notif-empty">No notifications</div>
                              </div>
                              <div class="notif-footer">
                                <a href="/notification">View all notifications</a>
                              </div>
                           </div>
                        </li>
                        <li class="user-nav">
                           <div class="user_login">
                              <div class="user_name"><i class="lnr lnr-user"></i> <span id="hdr-username"></span></div>
                           </div>
                           <div class="header_list header_loginBox">
                              <ul>
                                 <li><a href="/profile"><i class="lnr lnr-user"></i> Profile</a></li>
                                 <li><a href="/logout"><i class="lnr lnr-exit"></i> Logout</a></li>
                              </ul>
                           </div>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="mg_menu header-bottom">
            <div class="container">
               <ul>
                  <li>
                     <a href="/" title="Read Manga Online For Free"><span class="lnr lnr-home"></span>&nbsp;&nbsp;Home</a>
                  </li>
                  <li>
                     <a href="/list-manga" title="Manga List">Manga List</a>
                  </li>
                 <li>
                     <a href="https://manga18.us" title="Manga List">Webtoon Hot</a>
                  </li>
                  <li>
                     <a href="https://manhwas.me" title="Manga List">Tumanhwas Espanol</a>
                  </li>
                  <li>
                     <a href="https://hanman18.com" title="Manga List">China Toptoon/Toomics</a>
                  </li>
                   <li>
                     <a href="https://18porncomic.com" title="Manga List">Porn Comic</a>
                  </li> 
                  
                  <!-- <li class="dropdownmenu">
                     <a href="#" title="Manga List - Genres: All">
                     Genres <i class="icofont-caret-right"></i>
                     </a>
                  </li> -->
                  <div class="sub-menu" style="display: none;">
                     <div class="container" style="background-color: white;">
                        <ul>
                          <?php foreach ($categories as $key => $value) { ?>
                           <li>
                              <a href="<?=base_url()?>manga-list/<?=$value->slug?>" title="Action Manga"><i class="icofont-caret-right"></i> <?=$value->name?></a>
                           </li>
                         <?php } ?>
                        </ul>
                     </div>
                  </div>
                 
               </ul>
            </div>
         </div>
      </div>
      <div class="header-manga mb-header">
         <div class="top-header">
            <div class="menu-ico">
               <i class="ti-align-left"></i>
               <i class="icofont-close close-menu" style="display: block;"></i>
            </div>
            <div class="logo">
               <a title="Read Manga Online in English Free" href="/">
               <img src="/logo18.png?v=1.1" style="height: 65px !important;" alt="" alt="Read Manga Online For Free">
               </a>
            </div>
            <div class="bell-ico user-nav-mb" style="position:relative;margin-right:8px;">
               <a href="#" class="bell-trigger-mb" style="color:#fff;font-size:20px;position:relative;">
                  <i class="ti-bell"></i><span class="notify_count_mb" style="display:none;background:#ff523a;color:#fff;border-radius:50%;padding:1px 5px;font-size:9px;position:absolute;top:-5px;right:-8px;">0</span>
               </a>
               <div id="notifications-mb" class="notif-dropdown">
                  <div class="notif-header">
                    <h4>Notifications</h4>
                    <a href="#" class="notif-mark-read-mb">Mark all read</a>
                  </div>
                  <div class="notif-body">
                    <ul></ul>
                    <div class="notif-empty">No notifications</div>
                  </div>
                  <div class="notif-footer">
                    <a href="/notification">View all notifications</a>
                  </div>
               </div>
            </div>
            <div class="search-ico">
               <i class="lnr lnr-magnifier open-search"></i>
               <i class="ti-close close-search" style="display: none;"></i>
            </div>
         </div>
         <div class="under-header">
            <div class="header-menu" style="display: block;">
               <ul>
               <li>
                  <a href="/" title="Read Manga Online in English Free">Home</a>
               </li>
               <li>
                  <a href="<?=base_url();?>list-manga" title="Manga List">Manga List</a>
               </li>
               <li>
                     <a href="https://manga18.us" title="Manga List">Webtoon Hot</a>
                  </li>
                  <li>
                     <a href="https://manhwas.me" title="Manga List">Tumanhwas Espanol</a>
                  </li>
                   <li>
                     <a href="https://hanman18.com" title="Manga List">China Toptoon/Toomics</a>
                  </li>
                  <li>
                     <a href="https://18porncomic.com" title="Manga List">Porn Comic</a>
                  </li>
                  
               <!-- <li class="dropdownmenu">
                  <a href="#" title="Manga List - Genres: All">
                  Genres <i class="fa fa-angle-right" aria-hidden="true"></i>
                  </a>
               </li>
               <div class="sub-menu" style="display: none;">
                  <ul>
                     <?php foreach ($categories as $key => $value) { ?>
                       <li>
                          <a href="<?=base_url()?>manga-list/<?=$value->slug?>" title="Action Manga"><i class="icofont-caret-right"></i> <?=$value->name;?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                       </li>
                     <?php } ?>
                  </ul>
                  
               </div> -->
               
               <!-- Guest (default, CF cached) -->
               <div class="user-block guest-nav-mb">
                  <div class="guest-option">
                     <a href="/login">Sign In</a>
                     <a href="/register">Sign Up</a>
                  </div>
               </div>
               <!-- Logged in (hidden by default, shown by CSS/JS) -->
               <div class="user-block user-nav-mb">
                  <div class="guest-option">
                     <div class="user_name">Hello <span class="hdr-username-mb"></span><br><br></div>
                      <a href="/profile">Profile</a>
                      <a href="/bookmarks">Bookmark</a>
                      <a href="/history">History</a>
                      <a href="/logout">Logout</a>
                  </div>
               </div>
              
              
            </div>
             <div id="searchmb" class="header-search header-mb-search ng-scope" style="display: none;" ng-controller="livesearch">
                  <form action="/list-manga" method="get" class="ng-pristine ng-valid">
                     <input name="search" type="text" style="font-size: 16px !important;" placeholder="Search..." autocomplete="off" class="search_complete2">
                     <button type="submit">
                     <i class="lnr lnr-magnifier"></i>
                     </button>
                  </form>
                  
               </div>
         </div>
      </div>
      <!-- End Header -->
      <div class="clear" style="clear: both;"> </div>
        
  <div class="mg-main">
    <div class="mg_update">
      <div class="container">

      </div>
      </div>
      </div>