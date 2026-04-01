<?php helper('basic'); ?>
<?php include APPPATH . 'Views/include/header.php'; ?>
<!-- <script type="text/javascript" src="https://tags.native-ad.net/129.js"></script> -->

<style type="text/css">
    #comic-viewer {
       max-width: 720px;
        margin: 0 auto;
        background: #ccc;
    }
    .comic-original,
    .comic-scramble {
        display: block;
        width: 100%;
        /*margin-bottom: 15px;*/
    }

    .imageChap img{
      width: 100%;
      max-width: 800px;
    }

    @media only screen and (min-width: 800px) {
      .comic-viewer{            
        width: 100% !important;
        /*margin-left: 5% !important;*/
      }
    }


</style>

 

  <!-- Main content -->
  <div class="chapter_detail">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
             <?php foreach ($breadcrums as $key => $value) { ?>
            <li><a href="<?=$value['link']?>"><?=$value['title']?></a></li>
            <?php } ?>      
          </ol>
        </div>
      </div>
    </div>
    <div class="chapter_detailContent">
      <div class="container">
        <div class="chapter_detailBox">
          <div class="chapter_detailHead">
            <div class="story_name">
              <h1><?=$manga_info->name?></h1>
            </div>
            <div class="chapter_name"> Chapter <span><?=$chapter_info->number?></span></div>
            <div class="chapter_time">[ Update at <span><?=$chapter_info->created_at ?></span> ]</div>
          </div>
          <div class="chapter_detailBody">
            <p>If you cannot see the story, please change the "SERVER" below!</p>
            <div class="section_groupButton">
             
              <div class="section_button" data-value='s1'>
                <button class="bg_active">Server 1</button>
              </div>
              <div class="section_button" data-value='s2'>
                <button class="">Server 2</button>
              </div>
            </div>
             
              <div class="col-md-12 " >
                <br>
                <div class="divads" style="">

<script  async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
<ins class="eas6a97888e37" data-zoneid="5251336"></ins>
<script type="module">(AdProvider = window.AdProvider || []).push({"serve": {}});</script>

                  </div>
                <div class="clear-fix"></div>
                <div style="display: table;">
                <?php if(isset($ads['TOP_SQRE_1'])){ ?>
<?=$ads['TOP_SQRE_1'] ?>
                <?php } ?>
                <?php if(isset($ads['TOP_SQRE_2'])){ ?>
<?=$ads['TOP_SQRE_2'] ?>
                <?php } ?>
                </div>
                <br>
              </div>
              
            <div class="section_note">
              <p><i class="ti-info-alt"></i> Use the left (←) or right (→) arrows to switch chapters</p>
              <div style="text-align:center;margin-bottom:10px;">
                <button class="btn btn-sm btn-outline-danger btnReport" onclick="openReportModal()" style="background:transparent;border:1px solid #e52d27;color:#e52d27;padding:4px 12px;cursor:pointer;border-radius:3px;">
                  <i class="fa fa-flag"></i> Report Broken Chapter
                </button>
                <span class="reportMsgDone" style="display:none;color:#4caf50;margin-left:10px;">Reported! Thank you.</span>
              </div>
            </div>
            <div class="detail_chapterMain">
              <div class="detail_chapterMenu">
                <div class="list_icon">
                  <ul>
                    <li><a href="/"><i class="lnr lnr-home"></i></a></li>
                    <li><a href="<?=base_url().'/manhwa/'.$manga_info->slug?>"><i class="lnr lnr-list"></i></a></li>
                    <!-- <li><a href="#"><i class="lnr lnr-redo"></i><span>2</span></a></li> -->
                  </ul>
                </div>
                
                <div class="chapter_control">
                  <?php if(isset($chapter_info->prevChapter->id)){ ?>
                  <button onclick="prevChap();"><i class="ti-angle-left"></i></button>
                  <?php } ?>
                  <select class="form-control" id="slcChapter">
                      <?php foreach($manga_info->chapters as $k => $v) { ?>
                      <option value="<?php echo base_url();?>manhwa/<?=$manga_info->slug.'/'.$v->slug?>" <?php if($v->current==1) echo "selected"; ?>><?=$v->name ?></option>    
                      <?php } ?>
                  </select>
                  <?php if(isset($chapter_info->nextChapter->id)){ ?>
                  <button onclick="nextChap();"><i class="ti-angle-right"></i></button>
                  <?php } ?>
                </div>
                <div class="section_button" id="mgBookmarkWrap">
                  <?php if($check_bookmark==0) { ?>
                  <button id="btnBookmark" class="bg_orange"><i class="lnr lnr-bookmark"></i> <span>Bookmark</span></button>
                  <?php } else { ?>
                  <button id="btnUnsubscribe" class="bg_active"><span class="lnr lnr-cross-circle"></span> <span>Unmark</span></button>
                  <?php } ?>
                </div>
              </div>
              
              <div class="chapter_boxImages" id="chapter_boxImages">
              </div>
             <div class="chapter_boxImages" style="margin-top: 0px;">
              <?php if($chapter_info->source_url==''){?>
               <img src="<?=base_url();?>1.jpg" style="width: 100%;max-width: 800px;"/> 
              <?php } ?>
             </div>
             <div class="detail_chapterMenu">
                <div style="text-align:center;margin-bottom:10px;">
                  <button class="btn btn-sm btn-outline-danger btnReport" onclick="openReportModal()" style="background:transparent;border:1px solid #e52d27;color:#e52d27;padding:4px 12px;cursor:pointer;border-radius:3px;">
                    <i class="fa fa-flag"></i> Report Broken Chapter
                  </button>
                  <span class="reportMsgDone" style="display:none;color:#4caf50;margin-left:10px;">Reported! Thank you.</span>
                </div>

                <!-- Report Modal -->
                <div id="reportModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:99999;justify-content:center;align-items:center;">
                  <div style="background:#1a2e2a;border-radius:12px;padding:28px 32px;max-width:480px;width:90%;position:relative;box-shadow:0 8px 32px rgba(0,0,0,0.5);">
                    <button onclick="closeReportModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;color:#999;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
                    <h4 style="margin:0 0 4px;color:#e8e8e8;font-size:20px;font-weight:700;">Report Chapter Issue</h4>
                    <p style="margin:0 0 18px;color:#7a9e8e;font-size:14px;"><?= esc($manga_info->name) ?> &mdash; Chapter <?= esc($chapter_info->number) ?></p>
                    <div style="margin-bottom:14px;">
                      <label style="color:#e52d27;font-size:14px;font-weight:600;">Reason <span style="color:#e52d27;">*</span></label>
                    </div>
                    <div id="reportReasons">
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="broken_images" checked> <span>Broken / Images not loading</span></label>
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="wrong_images"> <span>Wrong / Unrelated images</span></label>
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="missing_pages"> <span>Missing pages</span></label>
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="low_quality"> <span>Low quality / Blurry</span></label>
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="wrong_order"> <span>Pages in wrong order</span></label>
                      <label class="rpt-opt"><input type="radio" name="report_reason" value="other"> <span>Other</span></label>
                    </div>
                    <textarea id="reportNote" placeholder="Additional details (optional)" style="width:100%;background:#243b35;border:1px solid #3a5a50;border-radius:8px;color:#ccc;padding:10px 12px;font-size:14px;resize:vertical;min-height:60px;margin-top:12px;box-sizing:border-box;"></textarea>
                    <div style="margin-top:14px;">
                      <label style="color:#ccc;font-size:13px;margin-bottom:6px;display:block;">Enter the code below to verify</label>
                      <div style="display:flex;align-items:center;gap:10px;">
                        <img id="captchaImg" src="" style="border-radius:6px;cursor:pointer;height:50px;" title="Click to refresh" onclick="refreshCaptcha()">
                        <button type="button" onclick="refreshCaptcha()" style="background:none;border:none;color:#7a9e8e;font-size:18px;cursor:pointer;padding:4px;" title="Refresh captcha">&#8635;</button>
                      </div>
                      <input type="text" id="captchaInput" placeholder="Type the code here" autocomplete="off" maxlength="5" style="width:160px;background:#243b35;border:1px solid #3a5a50;border-radius:6px;color:#e8e8e8;padding:8px 12px;font-size:15px;margin-top:8px;letter-spacing:3px;box-sizing:border-box;">
                      <div id="captchaError" style="display:none;color:#e52d27;font-size:13px;margin-top:4px;"></div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;">
                      <button onclick="closeReportModal()" style="background:#2a4a40;border:1px solid #3a5a50;color:#aaa;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:14px;">Cancel</button>
                      <button onclick="submitReport()" id="btnSubmitReport" style="background:#e52d27;border:none;color:#fff;padding:8px 24px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;">Submit Report</button>
                    </div>
                  </div>
                </div>
                <style>
                  .rpt-opt{display:block;background:#243b35;border:1px solid #3a5a50;border-radius:8px;padding:12px 16px;margin-bottom:8px;cursor:pointer;color:#ccc;font-size:14px;transition:border-color .2s;}
                  .rpt-opt:hover{border-color:#5a8a70;}
                  .rpt-opt input[type=radio]{margin-right:10px;accent-color:#e52d27;vertical-align:middle;}
                  .rpt-opt span{vertical-align:middle;}
                </style>
                <script>
                function refreshCaptcha(){
                  document.getElementById('captchaImg').src='/captcha/report?t='+Date.now();
                  document.getElementById('captchaInput').value='';
                  document.getElementById('captchaError').style.display='none';
                }
                function openReportModal(){
                  refreshCaptcha();
                  var m=document.getElementById('reportModal');
                  m.style.display='flex';
                  document.body.style.overflow='hidden';
                }
                function closeReportModal(){
                  var m=document.getElementById('reportModal');
                  m.style.display='none';
                  document.body.style.overflow='';
                }
                document.getElementById('reportModal').addEventListener('click',function(e){
                  if(e.target===this) closeReportModal();
                });
                function submitReport(){
                  var captchaVal=document.getElementById('captchaInput').value.trim();
                  if(!captchaVal){
                    document.getElementById('captchaError').textContent='Please enter the captcha code.';
                    document.getElementById('captchaError').style.display='block';
                    return;
                  }
                  var reason=document.querySelector('input[name="report_reason"]:checked').value;
                  var note=document.getElementById('reportNote').value;
                  var btn=document.getElementById('btnSubmitReport');
                  btn.disabled=true; btn.textContent='Submitting...';
                  var fd=new FormData();
                  fd.append('chapter_id','<?= $chapter_info->id ?>');
                  fd.append('manga_id','<?= $manga_info->id ?>');
                  fd.append('reason',reason);
                  fd.append('note',note);
                  fd.append('captcha',captchaVal);
                  fd.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');
                  fetch('/report-chapter',{
                    method:'POST',
                    headers:{'X-Requested-With':'XMLHttpRequest'},
                    body:fd
                  })
                  .then(r=>r.json())
                  .then(d=>{
                    if(d.status==='error'){
                      document.getElementById('captchaError').textContent=d.message||'Invalid captcha';
                      document.getElementById('captchaError').style.display='block';
                      refreshCaptcha();
                      btn.disabled=false;btn.textContent='Submit Report';
                      return;
                    }
                    closeReportModal();
                    document.querySelectorAll('.btnReport').forEach(function(b){b.style.display='none';});
                    document.querySelectorAll('.reportMsgDone').forEach(function(m){m.style.display='inline';});
                  })
                  .catch(()=>{
                    btn.disabled=false;btn.textContent='Submit Report';
                    alert('Error, please try again.');
                  });
                }
                </script>
                <div class="chapter_control">
                  <?php if(isset($chapter_info->prevChapter->id)){ ?>
                  <button onclick="prevChap();"><i class="ti-angle-left"></i></button>
                  <?php } ?>
                  <select class="form-control" id="slcChapter2">
                      <?php foreach($manga_info->chapters as $k => $v) { ?>
                      <option value="<?php echo base_url();?>manhwa/<?=$manga_info->slug.'/'.$v->slug?>" <?php if($v->current==1) echo "selected"; ?>><?=$v->name ?></option>
                      <?php } ?>
                  </select>
                  <?php if(isset($chapter_info->nextChapter->id)){ ?>
                  <button onclick="nextChap();"><i class="ti-angle-right"></i></button>
                  <?php } ?>
                </div>

              </div>

            <div class="mg_rank detail_chat">
              <div class="rank_tab">
                <ul>
                  <li class="active"><a data-toggle="tab" href="#tab_chapter_cmt" aria-expanded="true">CHAPTER COMMENTS</a></li>
                  <li><a data-toggle="tab" href="#tab_manga_cmt" aria-expanded="false">MANGA COMMENTS</a></li>
                  <li><a data-toggle="tab" href="#tab_disq_cmt" aria-expanded="false">DISQ COMMENTS</a></li>
                </ul>
                <div class="tab-content">
                  <div id="tab_chapter_cmt" class="tab-pane active fade in">
                        <?php
                          $comment_post_id = $chapter_info->id;
                          $comment_post_type = 'chapter';
                          include APPPATH . 'Views/include/comments.php';
                        ?>
                  </div>
                  <div id="tab_manga_cmt" class="tab-pane fade in">
                        <?php
                          $comment_post_id = $manga_info->id;
                          $comment_post_type = 'manga_all';
                          include APPPATH . 'Views/include/comments.php';
                        ?>
                  </div>
                  <div id="tab_disq_cmt" class="tab-pane fade in">
                    <div id="easyComment_Content"></div>
                    <script type="text/javascript">
                      var easyComment_ContentID = document.title;
                      var easyComment_Language = 'en';
                      var easyComment_FooterLinks = 'On';
                      var easyComment_Domain = 'https://disq.manga18.club';
                      (function() {
                        var EC = document.createElement('script');
                        EC.type = 'text/javascript';
                        EC.async = true;
                        EC.src = easyComment_Domain + '/plugin/embed.js';
                        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(EC);
                      })();
                    </script>
                  </div>
                </div>
            </div>
          </div>
            <div class="col-md-12 center" style="margin:auto;text-align: center !important; ">
              <div class="divads" style="">
                
                <?php if(isset($ads['BOTTOM_LARGE'])){ ?>
<?=$ads['BOTTOM_LARGE'] ?>
                <?php } ?>
                </div>
                <?php if(isset($ads['BOTTOM_SQRE_1'])){ ?>
<?=$ads['BOTTOM_SQRE_1'] ?>
                <?php } ?>
                <?php if(isset($ads['BOTTOM_SQRE_2'])){ ?>
<?=$ads['BOTTOM_SQRE_2'] ?>
                <?php } ?>


                <?php if(isset($ads['LEFT_WIDE_1'])){ ?>
<?=$ads['LEFT_WIDE_1'] ?>
                <?php } ?>
                 <?php if(isset($ads['LEFT_WIDE_2'])){ ?>
<?=$ads['LEFT_WIDE_2'] ?>
                <?php } ?>  
                <?php if(isset($ads['RIGHT_WIDE_2'])){ ?>
<?=$ads['RIGHT_WIDE_2'] ?>
                <?php } ?>
                <?php if(isset($ads['RIGHT_WIDE_1'])){ ?>
<?=$ads['RIGHT_WIDE_1'] ?>
                <?php } ?>
              </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Main content -->
<script type="text/javascript">
  var slides_page;
  var slides_p_path = [<?php foreach ($allPages as $key => $value) { ?><?php if($value->external!=1){ ?><?php echo '"'.base64_encode($cdnUrl.'/manga/'.$manga_info->slug.'/chapters/'.$chapter_info->slug.'/'.$value->image).'",'; ?><?php }else{echo '"'.base64_encode($value->image).'",';}} ?>];  
  $(document)['ready'](function() {
      var _0xb00c=["","\x6C\x65\x6E\x67\x74\x68","\x3C\x64\x69\x76\x20\x63\x6C\x61\x73\x73\x3D\x22\x69\x6D\x61\x67\x65\x5F\x73\x74\x6F\x72\x79\x20\x69\x6D\x61\x67\x65\x43\x68\x61\x70\x22\x3E\x3C\x69\x6D\x67\x20\x73\x72\x63\x3D\x27","\x27\x20\x63\x6C\x61\x73\x73\x3D\x22\x69\x6D\x67\x2D\x72\x65\x73\x70\x6F\x6E\x73\x69\x76\x65\x20\x69\x6D\x61\x67\x65\x2D\x63\x68\x61\x70\x74\x65\x72\x22\x20\x2F\x3E\x3C\x2F\x64\x69\x76\x3E","\x69\x6E\x6E\x65\x72\x48\x54\x4D\x4C","\x63\x68\x61\x70\x74\x65\x72\x5F\x62\x6F\x78\x49\x6D\x61\x67\x65\x73","\x67\x65\x74\x45\x6C\x65\x6D\x65\x6E\x74\x42\x79\x49\x64"];str_story= _0xb00c[0];var timeout=5000/ slides_p_path[_0xb00c[1]];var _0x7668x1=0;var _0x7668x2=setInterval(function(){str_story+= _0xb00c[2]+ atob(slides_p_path[_0x7668x1])+ _0xb00c[3];document[_0xb00c[6]](_0xb00c[5])[_0xb00c[4]]= str_story;_0x7668x1++;if(_0x7668x1=== slides_p_path[_0xb00c[1]]){clearInterval(_0x7668x2)}},timeout)
  })


  $('#slcChapter').change(function(){
      var link = $(this).val();
      window.location.href = link;
  })

    $('#slcChapter2').change(function(){
      var link = $(this).val();
      window.location.href = link;
  })
//   setInterval(() => {
//     $.each($('iframe'), (arr,x) => {
//         let src = $(x).attr('sandbox');
//         if (src && src.match(/allow-popups/gi)) {
//           $(x).remove();
//         }
//     });
// }, 300);

</script>

<script>
  var title = document.title;
  

  var next_chapter =  "<?php if(isset($chapter_info->nextChapter->id)){ echo base_url().'manhwa/'.$manga_info->slug.'/'.$chapter_info->nextChapter->slug; } ?>" ;
  var prev_chapter =  "<?php if(isset($chapter_info->prevChapter->id)){ echo base_url().'manhwa/'.$manga_info->slug.'/'.$chapter_info->prevChapter->slug; } ?>" ;
  

  var initialized = false;
  


  function nextChap(){
      window.location = next_chapter;
  }
  
  function prevChap(){
      window.location = prev_chapter;
  }
  




  $(document).on('keyup', function (e) {
      KeyCheck(e);
  });

  function KeyCheck(e) {
      var ev = e || window.event;
      ev.preventDefault();
      var KeyID = ev.keyCode;
      switch (KeyID) {
          case 36:
              window.location = "<?php echo base_url(); ?>/manhwa/<?=$manga_info->slug?>";
              break;
          case 33:
          case 37:
              prevChap();
              break;
          case 34:
          case 39:
              nextChap();
              break;
      }
  }
  
                      
</script>
<script type="text/javascript">
    $(document).ready(function(){
       
        var _mangaId = <?=$manga_info->id?>;

        function renderBookmarkBtn(isBookmarked) {
            var html = isBookmarked
                ? '<button id="btnUnsubscribe" class="bg_active"><span class="lnr lnr-cross-circle"></span> <span>Unmark</span></button>'
                : '<button id="btnBookmark" class="bg_orange"><i class="lnr lnr-bookmark"></i> <span>Bookmark</span></button>';
            $('#mgBookmarkWrap').html(html);
        }

        // Track view count
        $.ajax({
            type: 'POST',
            url: '/api/track-view',
            data: { manga_id: _mangaId, chapter_slug: '<?= $chapter_info->slug ?>' },
            dataType: 'json',
            success: function(d) { console.log('track-view:', d); },
            error: function(xhr) { console.log('track-view error:', xhr.status, xhr.responseText); }
        });

        // Save reading history to localStorage (works for all users including guest)
        try {
            var history = JSON.parse(localStorage.getItem('reading_history') || '{}');
            history[_mangaId] = {
                slug: '<?= esc($manga_info->slug) ?>',
                name: '<?= esc($manga_info->name, 'js') ?>',
                chapter_slug: '<?= esc($chapter_info->slug) ?>',
                chapter_name: '<?= esc($chapter_info->name, 'js') ?>',
                cover: '<?=$cdnUrl?>/manga/<?= esc($manga_info->slug) ?>/cover/cover_thumb.jpg',
                time: Date.now()
            };
            // Giữ tối đa 50 manga gần nhất
            var keys = Object.keys(history);
            if (keys.length > 50) {
                keys.sort(function(a, b) { return history[a].time - history[b].time; });
                for (var i = 0; i < keys.length - 50; i++) delete history[keys[i]];
            }
            localStorage.setItem('reading_history', JSON.stringify(history));
        } catch(e) {}

        $(document).on('click', '#btnBookmark', function(){
            var btn = $(this);
            btn.prop('disabled', true);
            $.ajax({
                type: "POST",
                url: '/item_bookmark',
                data: { manga_id: _mangaId },
                success: function(result) {
                    if (result == 1) {
                        renderBookmarkBtn(true);
                    } else {
                        alert('Please login');
                        btn.prop('disabled', false);
                    }
                }
            });
        });

        $(document).on('click', '#btnUnsubscribe', function(){
            var btn = $(this);
            btn.prop('disabled', true);
            $.ajax({
                type: "POST",
                url: '/item_unbookmark',
                data: { manga_id: _mangaId },
                success: function(result) {
                    if (result == 1) {
                        renderBookmarkBtn(false);
                    } else {
                        alert('Error');
                        btn.prop('disabled', false);
                    }
                }
            });
        });

        // function load_disqus( disqus_shortname ) {
        //     // Prepare the trigger and target
        //     var is_disqus_empty = document.getElementById('disqus_empty'),
        //         disqus_target   = document.getElementById('disqus_thread'),
        //         disqus_embed    = document.createElement('script'),
        //         disqus_hook     = (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]);

        //     // Load script asynchronously only when the trigger and target exist
        //     if( disqus_target && is_disqus_empty ) {
        //         disqus_embed.type = 'text/javascript';
        //         disqus_embed.async = true;
        //         disqus_embed.src = '//' + disqus_shortname + '.disqus.com/embed.js';
        //         disqus_hook.appendChild(disqus_embed);
        //         is_disqus_empty.remove();
        //     }
        // }

        /*
         * Load disqus only when the document is scrolled till the top of the
         * section where comments are supposed to appear.
         */
        
        
            // var disqus_target = document.getElementById('disqus_thread');

            // if( disqus_target ) {
            //     load_disqus('manga18-us');
            // }
        
    });
</script>
<?php include APPPATH . 'Views/include/footer.php'; ?>

