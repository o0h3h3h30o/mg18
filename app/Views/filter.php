<?php include APPPATH . 'Views/include/header.php'; ?>
<div class="mg_grid">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <?php if(isset($category)){ ?>         
            <li class="active"><?=$category->name?></li>
            <?php } else {  ?>
            <li class="active"><?=$heading_title?></li>
            <?php } ?>
          </ol>
        </div>
      </div>
    </div>
    <div class="grid_main">
      <div class="container">
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
        <div class="row">
          <div class="col-md-9 col-sm-12 col-xs-12">
            <div class="grid_content">
              <div class="grid_head">
                <?php if(isset($category)){ ?>         
                <h5><span><?=$category->name?></span></h5>
                <?php } else {  ?>
                <h5><span><?=$heading_title?></span></h5>
                <?php } ?>                  
                <div class="mg_desc">
                  <p><?=$total?> RESULTS</p>
                </div>
              </div>
              <div class="section_list">
                <span>Order By &nbsp;</span>
                <ul>
                  <li <?php if($order_by=='lastest'){ ?>class="active"<?php } ?>><a href="<?=$url.'?order_by=lastest' ?>">Latest</a></li>
                  <li <?php if($order_by=='name'){ ?>class="active"<?php } ?>><a href="<?=$url.'?order_by=name' ?>">A-Z</a></li>                  
                  <li <?php if($order_by=='views'){ ?>class="active"<?php } ?>><a href="<?=$url.'?order_by=views' ?>">Most Views</a></li>             
                </ul>
              </div>
              

              <div class="recoment_box">
                <div class="row">
                  <?php if(count($mangaList)>0){ ?>
                  <?php foreach ($mangaList as $key => $value) { ?>
                  <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="story_item">
                      <div class="story_images">
                        <a href="<?=base_url()?>manhwa/<?=$value->slug?>" title="<?=$value->name?>"><img src="<?=$cdnUrl?>/manga/<?=$value->slug?>/cover/cover_thumb_2.webp" alt="" class="img-responsive"></a>                        
                      </div>
                      <div class="mg_info">
                        <div class="mg_name">
                          <a href="<?=base_url()?>manhwa/<?=$value->slug?>"><?=$value->name?></a>
                        </div>
                        <div class="mg_chapter">
                          <?php if($value->time_chap_1 != 0){?>
                          <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->slug?>/<?=$value->chap_1_slug?>">Chapter <?=$value->chapter_1?></a></div>                            
                            <div class="chapter_time"><span><?php echo time_elapsed_string_2($value->time_chap_1); ?></span></div>
                          </div>
                           
                        <?php }?>
                        <?php if($value->time_chap_2 != 0){?>
                            <div class="item">
                            <div class="chapter_count"><a href="/manhwa/<?=$value->slug?>/<?=$value->chap_2_slug?>">Chapter <?=$value->chapter_2?></a></div>                            
                            <div class="chapter_time"><span><?php echo time_elapsed_string_2($value->time_chap_2); ?></span></div>
                          </div>
                        <?php }?> 
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="section_pagination">
                <?=$links?>
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
            </div>
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
                <?php if(isset($ads['RIGHT_WIDE_1'])){ ?>
<?=$ads['RIGHT_WIDE_1'] ?>
                <?php } ?>
                <?php if(isset($ads['RIGHT_SQRE_1'])){ ?>
<?=$ads['RIGHT_SQRE_1'] ?>
                <?php } ?>
            <div class="mg_block">
              <div class="section_title">
                <h5>Browse Manga by Genres</h5>
              </div>
              <div class="grid_cate">
                <ul>
                <?php foreach ($categories as $key => $value) { ?>
                 <li><a href="<?=base_url().'manga-list/'.$value->slug?>"><?=$value->name?></a></li>
                <?php } ?>
                </ul>
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
  <?php include APPPATH . 'Views/include/footer.php'; ?>