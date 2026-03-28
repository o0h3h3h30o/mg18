<?php include APPPATH . 'Views/include/header.php'; ?>
  <!-- Main content -->

  
  <div class="mg-main">
    <div class="mg_update">
      <div class="container">
        <!-- <img src="https://manga18.club/mangatk.jpg?v=1.1" class="img-responsive" style="width: 100% !important;margin-top: 15px;min-height: 120px;"> -->
          <div class="mg_update-content hidden-xs">
            <div class="section_title">
              <h5>Popular</h5>
            </div>
            <div class="mg_update-owl owl-carousel owl-theme ">
              <?php foreach ($top_month as $key => $value) { ?>
              <div class="story_item">

                <div class="story_images">
                  <a href="<?=base_url().'manhwa/'.$value->slug?>" title="<?=$value->name?>">

                    <img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive">
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
            <div class="col-md-4 col-sm-12 col-xs-12">
              <?php if(count($bookmarks)>0) { ?>
              <div class="mg_block mg_flow">
                <div class="section_title">
                  <h5>Bookmarks</h5>
                  <div class="see_all">
                    <a href="#">See all</a>
                  </div>
                </div>
                <div class="flow_box">
                  <?php if(count($bookmarks)>0) { ?>
                  <?php foreach ($bookmarks as $key => $value) { ?>
                    <div class="item">
                      <div class="mg-item_hoz">
                        <div class="story_item">
                          <div class="story_images">
                            <a href="<?=base_url().'manhwa/'.$value->slug?>" title="<?=$value->name?>"><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive"></a>
                           
                          </div>
                          <div class="mg_info">
                            <div class="mg_name">
                              <a href="<?=base_url().'manhwa/'.$value->slug?>"><?=$value->name?></a>
                            </div>
                            <div class="mg_chapter">
                              <div class="item">
                                <div class="chapter_count"><a href="<?=base_url().'manhwa/'.$value->slug.'/'.$value->last_chapter->chapter_slug?>">Chapter <?=$value->last_chapter->chapter_number?></a></div>
                                <div class="chapter_time"><span><?=time_elapsed_string($value->last_chapter->chapter_created_at)?></span></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
                  <?php } ?>
                  

                </div>
              </div>
              <?php } ?>
               <?php if(isset($ads['RIGHT_WIDE_1'])){ ?>
                <?=$ads['RIGHT_WIDE_1'] ?>
                <?php } ?>
                 <?php if(isset($ads['RIGHT_SQRE_1'])){ ?>
                <?=$ads['RIGHT_SQRE_1'] ?>
                <?php } ?>
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
                                <a href="#" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive"></a>
                              </div>
                              <div class="mg_info">
                                <div class="mg_name" style="text-transform: uppercase;">
                                  <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=$value->name?></a>
                                </div>
                                <div class="mg_chapter">
                                  <div class="item">                                    
                                    <div class="chapter_view"><span class="lnr lnr-eye"></span> <span><?=number_format($value->total)?></span></div>
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
                  </div>
                </div>
              </div>
               <?php if(isset($ads['RIGHT_SQRE_2'])){ ?>
                <?=$ads['RIGHT_SQRE_2'] ?>
                <?php } ?>
                <?php if(isset($ads['RIGHT_WIDE_2'])){ ?>
                <?=$ads['RIGHT_WIDE_2'] ?>
                <?php } ?>
            </div>
            <div class="col-md-8 col-sm-12 col-xs-12">
                <?php if(isset($ads['TOP_LARGE'])){ ?>
                <?=$ads['TOP_LARGE'] ?>
                <?php } ?>
                <?php if(isset($ads['TOP_SQRE_1'])){ ?>
                <?=$ads['TOP_SQRE_1'] ?>
                <?php } ?>
                <?php if(isset($ads['TOP_SQRE_2'])){ ?>
                <?=$ads['TOP_SQRE_2'] ?>
                <?php } ?>
              <div class="section_title">
                <h5>Lastest Update</h5>
              </div>
              <div class="recoment_box">
              	
                <div class="row">

                <?php foreach ($listChapters as $key => $value) { ?>
                  <div class="col-md-3 col-sm-3 col-xs-6">
                    <div class="story_item">
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
                      <div class="story_images">
                        <a href="/manhwa/<?=$value->manga_slug?>" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->manga_slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive"></a>
                      </div>
                      <div class="mg_info">
                        <div class="mg_name">
                          <a style="text-transform: uppercase;" href="/manhwa/<?=$value->manga_slug?>"><?=$value->manga_name?></a>
                        </div>
                        <div class="mg_chapter">                        
                        <?php if($value->time_chap_1 != 0){?>
                          <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_1_slug?>">Chapter <?=$value->chapter_1?></a></div>                            
                            <span class="post-on">
                            <span class="c-new-tag">
                            <img src="/new.gif" >
                            </span>
                            </span>
                          </div>
                           
                        <?php }?>
                        <?php if($value->time_chap_2 != 0){?>
                            <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_2_slug?>">Chapter <?=$value->chapter_2?></a></div>                            
                            <div class="chapter_time"><span><?php echo time_elapsed_string_2($value->time_chap_2); ?></span></div>
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
            
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include APPPATH . 'Views/include/footer.php'; ?>

