<?php include APPPATH . 'Views/include/header.php'; ?>
<!-- Main content -->


<div class="mg-main">
   <div class="mg_update">
      <div class="container">
         <br>
         <br>
         <br>
         <br>
               <div class="air_date title-cat" style="
      /*display: bloc;*/
    background: #212121;
    border: 1px dashed #d9ed39;
    padding: 5px 0 0 0;
    margin: 1px 0 1px 0;
      ">
      <h4 style="
          font-size: 18px;
          padding: 0 15px 0px 15px;
          border-bottom: 3px solid transparent;
          font-weight: bold;
          text-transform: uppercase;
          font-family: 'Cuprum', sans-serif;
          display: inline-block;
          color: #dcf836;
          /* border-bottom: 3px solid #dcf836; */
      ">Note</h4>
      <p style="
          padding: 15px;
          display: inline-block;
      ">Due to Disqus policy blocking Manga18 domain, the comment function may not be available at this time. We hope for your understanding, we will develop the comment function on the app soon. Best regard.
      </p>
      </div>
         <br>
         <div class="divads" style="text-align: center;">
            <?php if(isset($ads['TOP_LARGE'])){ ?>
<?=$ads['TOP_LARGE'] ?>
            <?php } ?>
            <?php if(isset($ads['TOP_SQRE_1'])){ ?>
<?=$ads['TOP_SQRE_1'] ?>
            <?php } ?>
            <?php if(isset($ads['TOP_SQRE_2'])){ ?>
<?=$ads['TOP_SQRE_2'] ?>
            <?php } ?>
         </div>
         <div class="mg_update-content">
            <div class="section_title">
               <h5>Popular</h5>
            </div>
            <div class="mg_update-owl owl-carousel owl-theme ">
               <?php foreach ($top_month as $key => $value) { ?>
               <div class="story_item">
                  <div class="story_images">
                     <a href="<?=base_url().'manhwa/'.$value->slug?>" title="<?=$value->name?>">
                     <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive lazy">
                     </a>
                  </div>
                  <div class="story_info">
                     <div class="story_name">
                        <a href="<?=base_url().'manhwa/'.$value->slug?>"><?=$value->name?></a>
                     </div>
                     <div class="story_other">
                        <!-- <div class="story_chapter"><a href="#">Chapter <span>114</span></a></div> -->
                        <div class="story_time"><i class="ti-timer"></i> <span><?php echo time_elapsed_string_2($value->time_chap_1); ?></span></div>
                     </div>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
   <div class="story_all">
      <div class="container">
         <div class="story_all-content">
            <div class="row">
               <style type="text/css">
                 #sidebar .section ul>li {
                      padding: 10px 15px;
                      border-bottom: 1px solid #383838;
                      font-size: .97em;
                  }
                  #sidebar a {
                        color: #fff;
                        text-decoration: none;
                        transition: color .1s linear;
                        -moz-transition: color .1s linear;
                        -webkit-transition: color .1s linear;
                    }
               </style>
               <div class="col-md-8 col-sm-12 col-xs-12">
                  <div class="divads" style="">
                     <?php if(isset($ads['BOTTOM_LARGE'])){ ?>
<?=$ads['BOTTOM_LARGE'] ?>
                     <?php } ?>
                     <?php if(isset($ads['BOTTOM_SQRE_1'])){ ?>
<?=$ads['BOTTOM_SQRE_1'] ?>
                     <?php } ?>
                     <?php if(isset($ads['BOTTOM_SQRE_2'])){ ?>
<?=$ads['BOTTOM_SQRE_2'] ?>
                     <?php } ?>
                  </div>
                  <div class="section_title">
                     <h5>Manhwa Update</h5>
                  </div>
                  <div class="recoment_box">
                     <div class="row">
                        <?php foreach ($listChapters as $key => $value) { ?>
                        <div class="col-md-3 col-sm-3 col-xs-6">
                           <div class="story_item">
                              <?php if(isset($value->chapter_1)){ ?>
                              <?php if($value->chapter_1<11){ ?>
                              <span class="p-icon-text" style="
                                 background: #d4393c;
                                 font-size: 12px;
                                 font-weight: bold;
                                 line-height: 25px;
                                 padding: 0 10px;
                                 display: block;
                                 position: absolute;
                                 z-index: 9;
                                 top: 5px;
                                 left: 5px;
                                 border-radius: 2px;
                                 ">New</span>
                              <?php } ?>
                              <?php } ?>
                              <div class="story_images">
                                 <a href="/manhwa/<?=$value->manga_slug?>" title=""><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?=$cdnUrl?>/manga/<?=$value->manga_slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive lazy"></a>
                              </div>
                              <div class="mg_info">
                                 <div class="mg_name">
                                    <a style="text-transform: capitalize;" href="/manhwa/<?=$value->manga_slug?>"><?=strtolower($value->manga_name)?></a>
                                 </div>
                                 <div class="mg_chapter">
                                    <?php if($value->time_chap_1 != 0){ ?>
                                    <div class="item">
                                       <div class="chapter_count">
                                          <a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_1_slug?>">Ch. <?=$value->chapter_1?></a>
                                          <?php if(!empty($value->flag_chap_1)){ ?>
                                          <img class="img_flag lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?=$cdnUrl?>/flag/<?=$value->flag_chap_1?>.png" >
                                          <?php } ?>
                                       </div>
                                       <span class="post-on">
                                          <span class="c-new-tag">
                                             <?php if(time()<$value->time_chap_1+172800){ ?>                            
                                             <img src="<?=base_url();?>new.gif" >
                                             <?php } else { ?>
                                             <div class="chapter_time">
                                                <span>
                                                <?=date("d/m/y", $value->time_chap_1)?>
                                                </span>
                                             </div>
                                             <?php } ?>
                                          </span>
                                       </span>
                                    </div>
                                    <?php }?>
                                    <?php if($value->time_chap_2 != 0){?>
                                    <div class="item">
                                       <div class="chapter_count">
                                          <a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_2_slug?>">Ch. <?=$value->chapter_2?></a>
                                          <?php if(!empty($value->flag_chap_2)){ ?>
                                          <img class="img_flag lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?=$cdnUrl?>/flag/<?=$value->flag_chap_2?>.png" >
                                          <?php } ?>
                                       </div>
                                       <?php if(date("d/m/y", $value->time_chap_2)==date("d/m/y", $value->time_chap_1)){ ?>
                                       <span class="post-on">
                                       <span class="c-new-tag">
                                       <img src="<?=base_url();?>new.gif" >
                                       </span>
                                       </span>
                                       <?php }else{ ?>
                                       <div class="chapter_time"><span>                              
                                          <?=date("d/m/y", $value->time_chap_2)?>
                                          </span>
                                       </div>
                                       <?php } ?>
                                    </div>
                                    <?php }?> 
                                 </div>
                              </div>
                           </div>
                        </div>
                        <?php } ?>  
                     </div>
                  </div>
                  <div class="section_pagination">
                     <?=$links?>
                  </div>
                 
               </div>
               <div class="col-md-4 col-sm-12 col-xs-12">
                  <?php if(isset($ads['RIGHT_WIDE_1'])){ ?>
<?=$ads['RIGHT_WIDE_1'] ?>
                  <?php } ?>
                  <?php if(isset($ads['RIGHT_SQRE_1'])){ ?>
<?=$ads['RIGHT_SQRE_1'] ?>
                  <?php } ?>
                  <div class="mg_block mg_comment">
                    <div class="section_title"><h5 style="font-size:18px;">Recent Comments</h5></div>
                    <div id="rc-list"></div>
                    <style>
                    .rc-item{padding:12px;margin-bottom:10px;border:1px solid #2e3e45;border-radius:10px;background:rgba(30,42,48,.5);transition:border-color .2s,background .2s}
                    .rc-item:hover{border-color:#4ecdc4;background:rgba(30,42,48,.8)}
                    .rc-item:last-child{margin-bottom:0}
                    .rc-book{margin-bottom:8px}
                    .rc-book .book_name a{color:#4ecdc4;font-size:13px;font-weight:600;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                    .rc-book .book_name a:hover{color:#6ee7d7;text-decoration:underline}
                    .rc-book .book_chapter a{font-size:11px;color:#888;text-decoration:none}
                    .rc-book .book_chapter a:hover{color:#aaa}
                    .rc-media{display:flex;gap:10px}
                    .rc-avatar{width:36px;height:36px;border-radius:50%;flex-shrink:0;object-fit:cover;background:#2a3a45}
                    .rc-avatar-letter{width:36px;height:36px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#3a5a60,#2a3a45);display:flex;align-items:center;justify-content:center;color:#4ecdc4;font-size:14px;font-weight:700}
                    .rc-body{min-width:0;flex:1}
                    .rc-user-row{display:flex;align-items:center;gap:8px;margin-bottom:4px}
                    .rc-username{font-weight:700;color:#e8e8e8;font-size:13px}
                    .rc-time{font-size:11px;color:#666}
                    .rc-text{font-size:13px;color:#aab;line-height:1.5;word-break:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
                    </style>
                    <script>
                    (function(){
                      fetch('/api/comments/recent?limit=5')
                        .then(function(r){return r.json()})
                        .then(function(d){
                          if(d.status!=='ok'||!d.comments.length) return;
                          var html='';
                          d.comments.forEach(function(c){
                            var initial=(c.username||'?').charAt(0).toUpperCase();
                            var avatar=(c.avatar&&c.avatar!=='0')
                              ? '<img class="rc-avatar" src="'+c.avatar+'">'
                              : '<div class="rc-avatar-letter">'+initial+'</div>';
                            html+='<div class="rc-item">'
                              +'<div class="rc-book"><div class="book_name"><a href="'+c.link+'">'+c.manga_title+'</a></div></div>'
                              +'<div class="rc-media">'
                              +avatar
                              +'<div class="rc-body">'
                              +'<div class="rc-user-row"><span class="rc-username">'+c.username+'</span><span class="rc-time">'+c.time_ago+'</span></div>'
                              +'<p class="rc-text">'+c.comment+'</p>'
                              +'</div></div></div>';
                          });
                          document.getElementById('rc-list').innerHTML=html;
                        });
                    })();
                    </script>
                  </div>
                  <div style="clear:both;"></div>
                   <?php if(isset($ads['RIGHT_SQRE_2'])){ ?>
<?=$ads['RIGHT_SQRE_2'] ?>
                  <?php } ?>
                  <?php if(isset($ads['RIGHT_WIDE_2'])){ ?>
<?=$ads['RIGHT_WIDE_2'] ?>
                  <?php } ?>
                  <div style="clear:both;"></div>
                  <div class="mg_block mg_rank">
                     <div class="rank_tab">
                        <ul>
                           <li class="active" ><a data-toggle="tab" href="#top_day" aria-expanded="false">Top day</a></li>
                           <li ><a data-toggle="tab" href="#top_month" aria-expanded="true">Top month</a></li>
                           <li ><a data-toggle="tab" href="#top_all" aria-expanded="true">Top all</a></li>
                        </ul>
                        <div class="tab-content">
                           <div id="top_month" class="tab-pane fade in">
                              <div class="manga_box story_box">
                                 <?php if(count($top_month)>0){ ?>
                                 <?php foreach ($top_month as $key => $value) { ?>
                                 <div class="item">
                                    <div class="mg-item_hoz">
                                       <p class="mg_ranking-no"># <span><?=$key+1?></span></p>
                                       <div class="story_item">
                                          <div class="story_images">
                                             <a href="#" title=""><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive lazy"></a>
                                          </div>
                                          <div class="mg_info">
                                             <div class="mg_name" style="text-transform: capitalize;">
                                                <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=strtolower($value->name)?></a>
                                             </div>
                                             <div class="mg_chapter">
                                                <div class="item">
                                                   <div class="chapter_view"><span class="lnr lnr-eye"></span> <span><?=number_format($value->view)?></span></div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <?php }} ?>
                              </div>
                           </div>
                           <div id="top_week" class="tab-pane fade in">
                              <div class="erros_text">Chưa có dữ liệu</div>
                           </div>
                           <div id="top_day" class="tab-pane active fade in">
                              <div class="manga_box story_box">
                                 <?php if(count($top_day)>0){ ?>
                                 <?php foreach ($top_day as $key => $value) { ?>
                                 <div class="item">
                                    <div class="mg-item_hoz">
                                       <p class="mg_ranking-no" ># <span><?=$key+1?></span></p>
                                       <div class="story_item">
                                          <div class="story_images">
                                             <a href="#" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb.jpg" alt="" class="img-responsive"></a>                                
                                          </div>
                                          <div class="mg_info">
                                             <div class="mg_name" style="text-transform: capitalize;">
                                                <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=strtolower($value->name)?></a>
                                             </div>
                                             <div class="mg_chapter">
                                                <div class="item">
                                                   <!-- <div class="chapter_count"><a href="#"></a></div> -->
                                                   <div class="chapter_view"><span class="lnr lnr-eye"></span> <span><?=number_format($value->view)?></span></div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <?php }} ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                 

               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include APPPATH . 'Views/include/footer.php'; ?>