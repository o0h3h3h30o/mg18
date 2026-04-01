<?php helper('basic'); ?>
<?php include APPPATH . 'Views/include/header.php'; ?>

<!-- <script data-cfasync="false" async type="text/javascript" src="//writshackman.com/f7Y2vhNjG6XQ/35447"></script> -->
  <!-- Main content -->
  <div class="mg_detail">
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
    <div class="story_all">
      <div class="container">
        <div class="story_all-content">
          <div class="row">
            <div class="col-md-8 col-sm-12 col-xs-12">
              <div class="detail_story">
                <div class="row">
                  <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="detail_avatar">
                      <img src="<?=$cdnUrl?>/manga/<?php echo $manga_info->slug; ?>/cover/cover_250x350.jpg" alt="" class="img-responsive">
                    </div>
                  </div>
                  <div class="col-md-8 col-sm-8 col-xs-12">
                    <div class="detail_infomation">
                      <div class="detail_name">
                        <h1><?php echo $manga_info->name; ?></h1>
                      </div>
                      
                      <div class="detail_listInfo">
                         <div class="item">
                          <div class="info_label"><i class="lnr lnr-tag"></i> Other name</div>
                          <div class="info_value"><span><?=$manga_info->otherNames?></span></div>
                        </div>
                        <div class="item">
                          <div class="info_label"><i class="lnr lnr-user"></i> Author</div>
                          <div class="info_value"><?php if($manga_info->author !== 'Updating'): ?><a href="/list-manga?author=<?= urlencode($manga_info->author) ?>"><?= esc($manga_info->author) ?></a><?php else: ?><span>Updating</span><?php endif; ?></div>
                        </div>
                        <div class="item">
                          <div class="info_label"><i class="lnr lnr-user"></i> Artist</div>
                          <div class="info_value"><?php if($manga_info->artist !== 'Updating'): ?><a href="/list-manga?artist=<?= urlencode($manga_info->artist) ?>"><?= esc($manga_info->artist) ?></a><?php else: ?><span>Updating</span><?php endif; ?></div>
                        </div>
                        <div class="item">
                          <div class="info_label"><i class="ti-rss-alt"></i> Status</div>
                          <div class="info_value">
                            <?php if($manga_info->status_id==0){ ?>
                            <span class="label label-danger">Success</span>
                            <?php } else { ?>
                            <span class="label label-success">On Going</span>
                            <?php } ?>
                          </div>
                        </div>
                        <div class="item">
                          <div class="info_label"><i class="lnr lnr-tag"></i> Categories</div>
                          <div class="info_value">
                            <?php if(isset($manga_info->categories)) { ?>
                            <?php foreach ($manga_info->categories as $key => $value) { ?>
                            
                            <a href="<?=base_url().'manga-list/'.$value->slug?>"><?=$value->name?></a>
                            <?php if ($key!=(int)(count($manga_info->categories)-1)){ ?>
                              -
                            <?php } ?>
                            <?php } ?>
                            <?php } ?>
                          </div>
                        </div>
                        <div class="item">
                          <div class="info_label"><i class="lnr lnr-eye"></i> Views</div>
                          <div class="info_value"><span><?=number_format($manga_info->views)?></span></div>
                        </div>
                      </div>
                      <div class="detail_rate">
                        <p>Rating: <span id="mgRatingText"><?=$manga_info->avg ?>/5</span> out of <span id="mgVoteCount"><?=$manga_info->total_rate ?></span> total votes</p>
                        <div class="prd-star">
                          <fieldset class="rate" id="mgRateField">

                            <input class="rate1" id="rate1-star5" type="radio" name="rate1" value="5" <?php if($manga_info->avg>=4.5&&$manga_info->avg<=5){ ?> checked <?php } ?>>
                            <label for="rate1-star5" title="Excellent">5</label>

                            <input class="rate1" id="rate1-star4" type="radio" name="rate1" value="4" <?php if($manga_info->avg>=3.5&&$manga_info->avg<4.5){ ?> checked <?php } ?>>
                            <label for="rate1-star4" title="Good">4</label>

                            <input class="rate1" id="rate1-star3" type="radio" name="rate1" value="3" <?php if($manga_info->avg>=2.5&&$manga_info->avg<3.5){ ?> checked <?php } ?>>
                            <label for="rate1-star3" title="Satisfactory">3</label>

                            <input class="rate1" id="rate1-star2" type="radio" name="rate1" value="2" <?php if($manga_info->avg>=1.5&&$manga_info->avg<2.5){ ?> checked <?php } ?>>
                            <label for="rate1-star2" title="Bad">2</label>

                            <input class="rate1" id="rate1-star1" type="radio" name="rate1" value="1" <?php if($manga_info->avg>0&&$manga_info->avg<=1.5){ ?> checked <?php } ?>>
                            <label for="rate1-star1" title="Very bad">1</label>
                          </fieldset>
                        </div>
                      </div>
                      <div class="detail_groupButton">
                        <div class="detail_flow">
                          <div class="section_button" id="mgBookmarkWrap">
                            <?php if($check_bookmark==0) { ?>
                            <button id="btnBookmark" class="bg_orange"><i class="lnr lnr-bookmark"></i> <span>Bookmark</span></button>
                            <?php } else { ?>
                            <button id="btnUnsubscribe" class="bg_active"><span class="lnr lnr-cross-circle"></span> <span>Unmark</span></button>
                            <?php } ?>
                          </div>
                          <p><span id="mgBookmarkCount"><?=$total_bookmarks?></span> Users bookmarked This</p>
                        </div>
                        <!-- <br> -->
                        <div class="detail_view">
                          <div class="section_groupButton">
                            <br>
                            <div id="continueReadBtn" style="display:none;">
                              <a href="#" class="cr-btn">
                                <span class="cr-btn-icon">▶</span>
                                <span class="cr-btn-text">
                                  <span class="cr-btn-label">Continue Reading</span>
                                  <span class="cr-btn-chap"></span>
                                </span>
                              </a>
                            </div>
                            <style>
                            .cr-btn{
                              display:inline-flex;align-items:center;gap:10px;
                              padding:10px 20px;border-radius:50px;text-decoration:none;
                              background:linear-gradient(135deg,#4ecdc4,#44b3ab);
                              color:#fff;font-weight:600;font-size:14px;
                              box-shadow:0 4px 15px rgba(78,205,196,.35);
                              transition:all .3s ease;
                            }
                            .cr-btn:hover{
                              transform:translateY(-2px);
                              box-shadow:0 6px 25px rgba(78,205,196,.5);
                              background:linear-gradient(135deg,#5de0d7,#4ecdc4);
                              color:#fff;text-decoration:none;
                            }
                            .cr-btn-icon{
                              width:28px;height:28px;border-radius:50%;
                              background:rgba(255,255,255,.2);
                              display:flex;align-items:center;justify-content:center;
                              font-size:12px;flex-shrink:0;
                            }
                            .cr-btn-text{display:flex;flex-direction:column;line-height:1.2;text-align:left}
                            .cr-btn-label{font-size:13px;font-weight:700;letter-spacing:.3px}
                            .cr-btn-chap{font-size:11px;opacity:.85;font-weight:400}
                            </style>
                            <script>
                            (function(){
                              try {
                                var h = JSON.parse(localStorage.getItem('reading_history') || '{}');
                                var d = h[<?= $manga_info->id ?>];
                                if (d) {
                                  var wrap = document.getElementById('continueReadBtn');
                                  var a = wrap.querySelector('a');
                                  a.href = '/manhwa/' + d.slug + '/' + d.chapter_slug;
                                  wrap.querySelector('.cr-btn-chap').textContent = d.chapter_name;
                                  wrap.style.display = '';
                                }
                              } catch(e){}
                            })();
                            </script>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="detail_block detail_review">
                <div class="detail_head">
                  <h5><i class="ti-book"></i> SUMMARY</h5>
                </div>
                <div class="detail_reviewContent">
                  <?= html_entity_decode($manga_info->summary)?>
                </div>
              </div>
              <div class="divads" style="">
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
              <div class="detail_block detail_chapter">
                <div class="detail_head">
                  <h5><i class="ti-menu-alt"></i> CHAPTERS</h5>
                </div>
                <div class="detail_chapterContent">
                  <div class="chapter_head">
                    <div class="item">
                      <p class="chapter_num">Chapters</p>
                      <p class="chapter_info">Update</p>
                      <p class="chapter_info">Views</p>
                      <!-- <p class="chapter_info">FAP OFFline =))</p> -->
                    </div>
                  </div>
                  <div class="chapter_box">
                    <ul>
                      <?php if(count($manga_info->chapters)>0){ ?>
                      <?php $stt = 0; ?>
                      <?php foreach ($manga_info->chapters as $key => $value) { ?>
                      
                      <li class="<?php if($stt<10){ ?><?php } else { ?>hide<?php } ?>">
                        <div class="item">
                          <a style="color: white;" href="/manhwa/<?=$manga_info->slug.'/'.$value->slug?>" class="chapter_num">Chapter <?=$value->number?>
                            <?php if(!empty($value->flag)){ ?>&nbsp;<img class="img_flag" src="<?=$cdnUrl?>/flag/<?=$value->flag?>.png" ><?php } ?>
                          </a>
                          <p class="chapter_info"><?=date("d-m-Y", strtotime($value->created_at))?></p>
                          <p class="chapter_info"><?=number_format($value->view)?></p>
                          <p class="chapter_info hide">
                            <a href="https://azmin.manga18.club/download/<?=$manga_info->slug?>/<?=$value->id?>" 
                           title="download"  style="color: #ff523a;">
                            <i class="glyphicon glyphicon-download-alt"></i>
                        </a>
                          </p>
                        </div>
                      </li>
                      <?php $stt++; ?>
                      <?php } ?>
                      <?php } ?>
                    </ul>
                  </div>
                  <div class="chapter_more">
                    <span href="javascript:;">Show more</span>
                  </div>
                </div>
              </div>
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
                <br>
              <div class="mg_rank detail_chat">
                <div class="rank_tab">
                  <ul>
                    <li class="active"><a data-toggle="tab" href="#chat_manga18" aria-expanded="false">MANGA DISCUSSION</a></li>
                    <li><a data-toggle="tab" href="#chat_disq" aria-expanded="false">DISQ COMMENTS</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="chat_manga18" class="tab-pane active fade in">
                      <div class="chat_manga18">
                        <?php
                          $comment_post_id = $manga_info->id;
                          $comment_post_type = 'manga_all';
                          include APPPATH . 'Views/include/comments.php';
                        ?>
                    </div>
                  </div>
                    <div id="chat_disq" class="tab-pane fade in">
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
          </div>
          <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="mg_rank chat_rank">
              <div class="rank_tab">
                <ul>
                 
                  <li class="active"><a data-toggle="tab" href="#top_day" aria-expanded="false">Top day</a></li>
                  <li><a data-toggle="tab" href="#top_month" aria-expanded="false">Top month</a></li>
                  <li><a data-toggle="tab" href="#top_all" aria-expanded="false">Top all</a></li>
                </ul>
                <div class="tab-content">
                  <div id="top_day" class="tab-pane active fade in">
                    <div class="manga_box story_box">
                      <?php if(count($top_day)>0){ ?>
                        <?php foreach ($top_day as $key => $value) { ?>
                        <div class="item">
                          <div class="mg-item_hoz">
                            <p class="mg_ranking-no" style="color: #ff8b00 !important;"># <span><?=$key+1?></span></p>
                            <div class="story_item">
                              <div class="story_images">
                               
                                <a href="#" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb.jpg" alt="" class="img-responsive"></a>
                              </div>
                              <div class="mg_info">
                                <div class="mg_name" style="text-transform: uppercase;">
                                  <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=$value->name?></a>
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
                  <div id="top_month" class="tab-pane fade in">
                    <div class="manga_box story_box">
                      <?php if(isset($top_month) && count($top_month)>0){ ?>
                        <?php foreach ($top_month as $key => $value) { ?>
                        <div class="item">
                          <div class="mg-item_hoz">
                            <p class="mg_ranking-no" style="color: #ff8b00 !important;"># <span><?=$key+1?></span></p>
                            <div class="story_item">
                              <div class="story_images">
                                <a href="#" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb.jpg" alt="" class="img-responsive"></a>
                              </div>
                              <div class="mg_info">
                                <div class="mg_name" style="text-transform: uppercase;">
                                  <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=$value->name?></a>
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
                  <div id="top_all" class="tab-pane fade in">
                    <div class="manga_box story_box">
                      <?php if(isset($top_all) && count($top_all)>0){ ?>
                        <?php foreach ($top_all as $key => $value) { ?>
                        <div class="item">
                          <div class="mg-item_hoz">
                            <p class="mg_ranking-no" style="color: #ff8b00 !important;"># <span><?=$key+1?></span></p>
                            <div class="story_item">
                              <div class="story_images">
                                <a href="#" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb.jpg" alt="" class="img-responsive"></a>
                              </div>
                              <div class="mg_info">
                                <div class="mg_name" style="text-transform: uppercase;">
                                  <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=$value->name?></a>
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
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Main content -->
<script type="text/javascript">
var _mangaId = <?=$manga_info->id?>;

// Helper: update star checked state based on avg
function updateStars(avg) {
    $('#mgRateField input').prop('checked', false);
    if (avg >= 4.5) $('#rate1-star5').prop('checked', true);
    else if (avg >= 3.5) $('#rate1-star4').prop('checked', true);
    else if (avg >= 2.5) $('#rate1-star3').prop('checked', true);
    else if (avg >= 1.5) $('#rate1-star2').prop('checked', true);
    else if (avg > 0) $('#rate1-star1').prop('checked', true);
}

// Helper: render bookmark button
function renderBookmarkBtn(isBookmarked) {
    var html = isBookmarked
        ? '<button id="btnUnsubscribe" class="bg_active"><span class="lnr lnr-cross-circle"></span> <span>Unmark</span></button>'
        : '<button id="btnBookmark" class="bg_orange"><i class="lnr lnr-bookmark"></i> <span>Bookmark</span></button>';
    $('#mgBookmarkWrap').html(html);
}

$(document).ready(function(){

    // Rate
    $(document).on('click', '.rate1', function(){
        var score = $(this).val();
        $.ajax({
            type: "POST",
            url: '/item_rating',
            data: { manga_id: _mangaId, score: score },
            success: function(result) {
                if (result == 1) {
                    location.reload();
                } else {
                    alert('Please login');
                }
            }
        });
    });

    // Bookmark
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
                    var c = parseInt($('#mgBookmarkCount').text()) || 0;
                    $('#mgBookmarkCount').text(c + 1);
                } else {
                    alert('Please login');
                    btn.prop('disabled', false);
                }
            }
        });
    });

    // Unbookmark
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
                    var c = parseInt($('#mgBookmarkCount').text()) || 0;
                    $('#mgBookmarkCount').text(Math.max(c - 1, 0));
                } else {
                    alert('Error');
                    btn.prop('disabled', false);
                }
            }
        });
    });
});
</script>

<?php include APPPATH . 'Views/include/footer.php'; ?>

