<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/profile">Profile</a></li>
            <li class="active">Notifications</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="user_main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-3 col-sm-4 col-xs-12">
            <?php $active_page = 'notification'; include APPPATH . 'Views/include/user_sidebar.php'; ?>
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="user_right">

              <div class="up-head">
                <h5><i class="ti-bell"></i> Notifications</h5>
                <?php if($count_unread > 0): ?>
                <button class="up-action-btn" id="markAllReadPage"><i class="ti-check"></i> Mark all read</button>
                <?php endif; ?>
              </div>

              <div class="noti-tabs">
                <a href="/notification?filter=all" class="noti-tab <?= $filter === 'all' ? 'active' : '' ?>">All <span class="noti-tab-count"><?= $count_all ?></span></a>
                <a href="/notification?filter=unread" class="noti-tab <?= $filter === 'unread' ? 'active' : '' ?>">Unread <span class="noti-tab-count"><?= $count_unread ?></span></a>
                <a href="/notification?filter=read" class="noti-tab <?= $filter === 'read' ? 'active' : '' ?>">Read <span class="noti-tab-count"><?= $count_all - $count_unread ?></span></a>
              </div>

              <div class="noti-page-list">
                <?php if(!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                <a href="/notification/go/<?= $n->id ?>" class="noti-page-item <?= $n->is_read == 0 ? 'noti-unread' : '' ?>">
                  <div class="noti-page-icon">
                    <i class="<?= $n->icon_class ?: 'ti-bell' ?>"></i>
                  </div>
                  <div class="noti-page-content">
                    <div class="noti-page-title"><?= esc($n->title) ?></div>
                    <?php if(!empty($n->manga_name)): ?>
                    <div class="noti-page-manga"><i class="ti-book"></i> <?= esc($n->manga_name) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($n->message)): ?>
                    <div class="noti-page-msg"><?= esc($n->message) ?></div>
                    <?php endif; ?>
                    <div class="noti-page-time"><i class="ti-time"></i> <?= time_elapsed_string($n->created_at) ?></div>
                  </div>
                  <?php if($n->is_read == 0): ?>
                  <div class="noti-page-dot"></div>
                  <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="up-empty">
                  <i class="ti-bell" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
                  <p><?= $filter === 'unread' ? 'No unread notifications' : ($filter === 'read' ? 'No read notifications' : 'No notifications yet') ?></p>
                </div>
                <?php endif; ?>
              </div>

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

  <style>
  /* Notification tabs */
  .noti-tabs{display:flex;gap:0;margin-bottom:16px;background:#1a2530;border-radius:10px;overflow:hidden;border:1px solid #2e3e45}
  .noti-tab{flex:1;text-align:center;padding:10px 16px;color:#6a7a8a;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;border-right:1px solid #2e3e45}
  .noti-tab:last-child{border-right:none}
  .noti-tab:hover{background:#1e2e38;color:#aab;text-decoration:none}
  .noti-tab.active{background:#1a3535;color:#4ecdc4}
  .noti-tab-count{display:inline-block;background:rgba(78,205,196,.12);color:#4ecdc4;padding:1px 7px;border-radius:10px;font-size:11px;margin-left:4px}
  .noti-tab.active .noti-tab-count{background:rgba(78,205,196,.25)}

  /* Notification list */
  .noti-page-list{background:#1a2530;border-radius:12px;overflow:hidden;border:1px solid #2e3e45}
  .noti-page-item{display:flex;align-items:flex-start;gap:14px;padding:16px 18px;border-bottom:1px solid #232e36;text-decoration:none;transition:background .15s;position:relative}
  .noti-page-item:last-child{border-bottom:none}
  .noti-page-item:hover{background:#1e2e38;text-decoration:none}
  .noti-page-item.noti-unread{background:#1a3030;border-left:3px solid #4ecdc4}
  .noti-page-item.noti-unread:hover{background:#1f3838}
  .noti-page-icon{flex:0 0 40px;height:40px;border-radius:50%;background:#141e28;display:flex;align-items:center;justify-content:center;color:#4ecdc4;font-size:16px}
  .noti-unread .noti-page-icon{background:#1a3a3a}
  .noti-page-content{flex:1;min-width:0}
  .noti-page-title{font-size:14px;font-weight:600;color:#ccc;margin-bottom:3px}
  .noti-unread .noti-page-title{color:#e8e8e8}
  .noti-page-manga{font-size:12px;color:#4ecdc4;margin-bottom:4px}
  .noti-page-manga i{margin-right:4px;font-size:11px}
  .noti-page-msg{font-size:13px;color:#6a7a8a;margin-bottom:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .noti-page-time{font-size:11px;color:#4a5a65}
  .noti-page-time i{margin-right:3px}
  .noti-page-dot{flex:0 0 8px;width:8px;height:8px;border-radius:50%;background:#4ecdc4;margin-top:6px}
  </style>

<script>
$(document).ready(function(){
  $('#markAllReadPage').click(function(){
    var btn = $(this);
    btn.prop('disabled', true).text('Marking...');
    $.ajax({
      url: '/notifications/read',
      type: 'POST',
      dataType: 'json',
      success: function(res) {
        if (res.status == 1) {
          $('.noti-page-item.noti-unread').removeClass('noti-unread');
          $('.noti-page-dot').remove();
          $('.notify_count, .notify_count_mb').text('0').hide();
          var allCount = parseInt($('.noti-tab:eq(0) .noti-tab-count').text()) || 0;
          $('.noti-tab:eq(1) .noti-tab-count').text('0');
          $('.noti-tab:eq(2) .noti-tab-count').text(allCount);
          btn.html('<i class="ti-check"></i> All read').css({'border-color':'#3a4a55','color':'#5a6a7a','cursor':'default'});
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="ti-check"></i> Mark all read');
      }
    });
  });
});
</script>
<?php include APPPATH . 'Views/include/footer.php'; ?>
