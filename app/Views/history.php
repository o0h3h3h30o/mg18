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
                <button class="up-action-btn" id="btnClearAll" style="display:none">Clear All</button>
              </div>

              <div id="historyList" class="up-list" style="display:none"></div>

              <div id="historyEmpty" class="up-empty" style="display:none">
                <i class="ti-timer" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
                <p>No reading history yet. Start reading!</p>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include APPPATH . 'Views/include/user_page_styles.php'; ?>

<script>
(function(){
  var cdnUrl = '<?= esc($cdnUrl, 'js') ?>';
  var h = {};
  try { h = JSON.parse(localStorage.getItem('reading_history') || '{}'); } catch(e){}

  var keys = Object.keys(h);
  if (!keys.length) {
    document.getElementById('historyEmpty').style.display = '';
    return;
  }

  // Sort by time desc
  keys.sort(function(a, b){ return (h[b].time || 0) - (h[a].time || 0); });

  var list = document.getElementById('historyList');
  var html = '';
  keys.forEach(function(mangaId){
    var d = h[mangaId];
    var ago = timeAgo(d.time);
    html += '<div class="up-item" data-manga="' + mangaId + '">'
      + '<a href="/manhwa/' + d.slug + '" class="up-item-cover">'
      + '<img src="' + cdnUrl + '/manga/' + d.slug + '/cover/cover_thumb_2.webp" alt="">'
      + '</a>'
      + '<div class="up-item-info">'
      + '<a href="/manhwa/' + d.slug + '" class="up-item-name">' + escHtml(d.name) + '</a>'
      + '<div class="up-item-meta">'
      + '<a href="/manhwa/' + d.slug + '/' + d.chapter_slug + '" class="up-meta-chap">' + escHtml(d.chapter_name) + '</a>'
      + '<span class="up-meta-tag"><i class="ti-time"></i> ' + ago + '</span>'
      + '</div>'
      + '<a href="/manhwa/' + d.slug + '/' + d.chapter_slug + '" class="up-continue-btn"><i class="ti-control-play"></i> Continue</a>'
      + '</div>'
      + '<button class="up-item-del btn-delete-history" data-id="' + mangaId + '" title="Remove"><i class="ti-trash"></i></button>'
      + '</div>';
  });
  list.innerHTML = html;
  list.style.display = '';
  document.getElementById('btnClearAll').style.display = '';

  // Delete single
  list.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-delete-history');
    if (!btn) return;
    var id = btn.getAttribute('data-id');
    var item = btn.closest('.up-item');
    item.style.transition = 'all .3s';
    item.style.opacity = '0';
    item.style.transform = 'translateX(20px)';
    setTimeout(function(){ item.remove(); }, 300);
    try {
      var data = JSON.parse(localStorage.getItem('reading_history') || '{}');
      delete data[id];
      localStorage.setItem('reading_history', JSON.stringify(data));
      if (!Object.keys(data).length) {
        list.style.display = 'none';
        document.getElementById('btnClearAll').style.display = 'none';
        document.getElementById('historyEmpty').style.display = '';
      }
    } catch(e){}
  });

  // Clear all
  document.getElementById('btnClearAll').addEventListener('click', function(){
    if (!confirm('Clear all reading history?')) return;
    localStorage.removeItem('reading_history');
    list.innerHTML = '';
    list.style.display = 'none';
    this.style.display = 'none';
    document.getElementById('historyEmpty').style.display = '';
  });

  function timeAgo(ts) {
    var s = Math.floor((Date.now() - ts) / 1000);
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s/60) + 'm ago';
    if (s < 86400) return Math.floor(s/3600) + 'h ago';
    return Math.floor(s/86400) + 'd ago';
  }
  function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }
})();
</script>
<?php include APPPATH . 'Views/include/footer.php'; ?>
