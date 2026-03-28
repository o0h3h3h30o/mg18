<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/profile">Profile</a></li>
            <li class="active">Reading History</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="user_main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-3 col-sm-4 col-xs-12">
            <?php $active_page = 'history'; include APPPATH . 'Views/include/user_sidebar.php'; ?>
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="user_right">

              <div class="up-head">
                <h5><i class="ti-timer"></i> Reading History</h5>
              </div>

              <?php if(!empty($history)): ?>
              <div class="up-list">
                <?php foreach ($history as $value): ?>
                <div class="up-item" data-manga="<?= $value->manga_id ?>">
                  <a href="/manhwa/<?= $value->manga_slug ?>" class="up-item-cover">
                    <img src="<?=$cdnUrl?>/manga/<?= $value->manga_slug ?>/cover/cover_thumb_2.webp" alt="">
                  </a>
                  <div class="up-item-info">
                    <a href="/manhwa/<?= $value->manga_slug ?>" class="up-item-name"><?= esc($value->manga_name) ?></a>
                    <div class="up-item-meta">
                      <a href="/manhwa/<?= $value->manga_slug ?>/<?= $value->chapter_slug ?>" class="up-meta-chap">Ch. <?= $value->chapter_number ?></a>
                      <span class="up-meta-tag"><i class="ti-time"></i> <?= time_elapsed_string(date('Y-m-d H:i:s', $value->read_at)) ?></span>
                    </div>
                    <a href="/manhwa/<?= $value->manga_slug ?>/<?= $value->chapter_slug ?>" class="up-continue-btn"><i class="ti-control-play"></i> Continue</a>
                  </div>
                  <button class="up-item-del btn-delete-history" data-id="<?= $value->manga_id ?>" title="Remove">
                    <i class="ti-trash"></i>
                  </button>
                </div>
                <?php endforeach; ?>
              </div>
              <?php else: ?>
              <div class="up-empty">
                <i class="ti-timer" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
                <p>No reading history yet. Start reading!</p>
              </div>
              <?php endif; ?>

              <?php if(($total_pages ?? 0) > 1): ?>
              <div class="section_pagination">
                <?= view('pager/segment_pager', ['current_page' => $current_page, 'total_pages' => $total_pages, 'base_url' => $base_url]) ?>
              </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include APPPATH . 'Views/include/user_page_styles.php'; ?>

<script>
$(document).ready(function(){
  $('.btn-delete-history').click(function(){
    if(!confirm('Remove from history?')) return;
    var btn = $(this);
    var mangaId = btn.data('id');
    $.ajax({
      type: "POST",
      url: '/history/delete',
      data: { manga_id: mangaId },
      dataType: 'json',
      success: function(result){
        if(result.status == 1){
          btn.closest('.up-item').css({transition:'all .3s',opacity:0,transform:'translateX(20px)'});
          setTimeout(function(){ btn.closest('.up-item').remove(); }, 300);
        } else {
          alert(result.message || 'Error');
        }
      }
    });
  });
});
</script>
<?php include APPPATH . 'Views/include/footer.php'; ?>
