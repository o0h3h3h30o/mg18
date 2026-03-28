<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/profile">Profile</a></li>
            <li class="active">Bookmarks</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="user_main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-3 col-sm-4 col-xs-12">
            <?php $active_page = 'bookmarks'; include APPPATH . 'Views/include/user_sidebar.php'; ?>
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="user_right">

              <div class="up-head">
                <h5><i class="ti-heart"></i> Bookmarks</h5>
                <span class="up-count"><?= count($bookmarks) ?> manga</span>
              </div>

              <?php if(count($bookmarks) > 0): ?>
              <div class="up-list">
                <?php foreach ($bookmarks as $value): ?>
                <div class="up-item" data-manga="<?= $value->manga_id ?>">
                  <a href="/manhwa/<?= $value->slug ?>" class="up-item-cover">
                    <img src="<?=$cdnUrl?>/manga/<?= $value->slug ?>/cover/cover_thumb.jpg" alt="">
                  </a>
                  <div class="up-item-info">
                    <a href="/manhwa/<?= $value->slug ?>" class="up-item-name"><?= esc($value->name) ?></a>
                    <div class="up-item-meta">
                      <span class="up-meta-tag"><i class="ti-time"></i> <?= time_elapsed_string($value->created_at) ?></span>
                      <?php if(!empty($value->chapter_1)): ?>
                      <a href="/manhwa/<?= $value->slug . '/' . $value->chap_1_slug ?>" class="up-meta-chap">Ch. <?= $value->chapter_1 ?></a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <button class="up-item-del btn-delete-bookmark" data-id="<?= $value->manga_id ?>" title="Remove">
                    <i class="ti-trash"></i>
                  </button>
                </div>
                <?php endforeach; ?>
              </div>
              <?php else: ?>
              <div class="up-empty">
                <i class="ti-heart" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
                <p>No bookmarks yet. Start exploring!</p>
              </div>
              <?php endif; ?>

              <?php if(!empty($links)): ?>
              <div class="section_pagination"><?= $links ?></div>
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
  $('.btn-delete-bookmark').click(function(){
    if(!confirm('Remove this bookmark?')) return;
    var btn = $(this);
    var mangaId = btn.data('id');
    $.ajax({
      type: "POST",
      url: '/item_unbookmark',
      data: { manga_id: mangaId },
      success: function(result){
        if(result == 1){
          btn.closest('.up-item').css({transition:'all .3s',opacity:0,transform:'translateX(20px)'});
          setTimeout(function(){ btn.closest('.up-item').remove(); }, 300);
        } else {
          alert('Error removing bookmark');
        }
      }
    });
  });
});
</script>
<?php include APPPATH . 'Views/include/footer.php'; ?>
