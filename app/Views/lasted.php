<?php include APPPATH . 'Views/include/header.php'; ?>
  <!-- Main content -->
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
  <div class="mg-main">
    <div class="mg_update">
      <div class="container">
        <!-- <img src="https://manga18.club/mangatk.jpg?v=1.1" class="img-responsive" style="width: 100% !important;margin-top: 15px;min-height: 120px;"> -->
        <div class="mg_update-content">
          <div class="section_title">
            <h5>Popular</h5>
          </div>
          <div class="mg_update-owl owl-carousel owl-theme">
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
                <h5>Lastest Update</h5>
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
                        <a href="/manhwa/<?=$value->manga_slug?>" title=""><img src="<?=$cdnUrl?>/manga/<?=$value->manga_slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive"></a>
                      </div>
                      <div class="mg_info">
                        <div class="mg_name">                          
                          <a style="text-transform: capitalize;" href="/manhwa/<?=$value->manga_slug?>"><?=strtolower($value->manga_name)?></a>
                        </div>
                        <div class="mg_chapter">

                        <?php if($value->time_chap_1 != 0){?>
                          <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_1_slug?>">Ch. 
                              <?php if(!empty($value->flag_chap_1)){ ?>
                              <?=intval($value->chapter_1)?>
                              <?php } else { ?>
                              <?=$value->chapter_1?>
                              <?php } ?>
                            </a>
                              <?php if(!empty($value->flag_chap_1)){ ?>
                              <img class="img_flag" src="<?=$cdnUrl?>/flag/<?=$value->flag_chap_1?>.png" >
                              <?php } ?>
                            </div>                            
                            <span class="post-on">
                            <span class="c-new-tag">                           
                            <?php if(time()<$value->time_chap_1+172800){ ?>
                             <img src="<?=base_url();?>new.gif" > 
                            <?php } else { ?>
                            <div class="chapter_time"><span>
                                <?=date("d/m/y", $value->time_chap_1)?>
                              </span></div>
                            <?php } ?>
                            </span>
                            </span>
                          </div>
                           
                        <?php }?>
                        <?php if($value->time_chap_2 != 0){?>
                            <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->manga_slug?>/<?=$value->chap_2_slug?>">Ch.
                              <?php if(!empty($value->flag_chap_2)){ ?>
                              <?=intval($value->chapter_2)?>
                              <?php } else { ?>
                              <?=$value->chapter_2?>
                              <?php } ?>
                              </a>
                              <?php if(!empty($value->flag_chap_2)){ ?>
                              <img class="img_flag" src="<?=$cdnUrl?>/flag/<?=$value->flag_chap_2?>.png" >
                              <?php } ?>
                            </div>
                            <?php if(date("d-m-Y", $value->time_chap_2)==date("d-m-Y", $value->time_chap_1)&&time()<$value->time_chap_1+172800){ ?>
                             <span class="post-on">
                            <span class="c-new-tag">
                            <img src="<?=base_url();?>new.gif" >
                            </span>
                            </span>
                            <?php }else{ ?>
                            
                            <div class="chapter_time">
                              <span>                              
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
                <?= view('pager/segment_pager', ['current_page' => $current_page, 'total_pages' => $total_pages, 'base_url' => $base_url]) ?>
              </div>
             
            </div>
            <div class="col-md-4 col-sm-12 hidden-xs">
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
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include APPPATH . 'Views/include/footer.php'; ?>

