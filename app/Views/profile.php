<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li class="active">Profile</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="user_main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-3 col-sm-4 col-xs-12">
            <?php $active_page = 'profile'; include APPPATH . 'Views/include/user_sidebar.php'; ?>
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="user_right">

              <!-- Profile Card -->
              <div class="pf-card pf-hero">
                <div class="pf-hero-top">
                  <div class="pf-hero-avatar">
                    <?php if($user_info->avatar == 1): ?>
                      <img src="/uploads/users/<?= $user_info->id ?>.jpg?t=<?= time() ?>" alt="">
                    <?php else: ?>
                      <span><?= strtoupper(substr($user_info->username ?: $user_info->email, 0, 1)) ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="pf-hero-info">
                    <h4 class="pf-hero-name"><?= esc($user_info->username ?: $user_info->email) ?></h4>
                    <p class="pf-hero-email"><?= esc($user_info->email) ?></p>
                    <a href="/profile/edit" class="pf-edit-btn"><i class="ti-pencil"></i> Edit Profile</a>
                  </div>
                </div>
                <!-- Stats -->
                <div class="pf-stats">
                  <a href="/bookmarks" class="pf-stat">
                    <span class="pf-stat-num"><?= number_format($stats['bookmarks']) ?></span>
                    <span class="pf-stat-label">Bookmarks</span>
                  </a>
                  <a href="/history" class="pf-stat">
                    <span class="pf-stat-num"><?= number_format($stats['history']) ?></span>
                    <span class="pf-stat-label">Read</span>
                  </a>
                  <a class="pf-stat">
                    <span class="pf-stat-num"><?= number_format($stats['comments']) ?></span>
                    <span class="pf-stat-label">Comments</span>
                  </a>
                </div>
              </div>

              <!-- Info Card -->
              <div class="pf-card">
                <div class="pf-card-head">
                  <h5><i class="ti-id-badge"></i> Information</h5>
                </div>
                <div class="pf-info-grid">
                  <div class="pf-info-item">
                    <span class="pf-info-label">Username</span>
                    <span class="pf-info-value"><?= esc($user_info->username) ?></span>
                  </div>
                  <div class="pf-info-item">
                    <span class="pf-info-label">Email</span>
                    <span class="pf-info-value"><?= esc($user_info->email) ?></span>
                  </div>
                  <div class="pf-info-item">
                    <span class="pf-info-label">Joined</span>
                    <span class="pf-info-value"><?= date('M d, Y', strtotime($user_info->created_at ?? 'now')) ?></span>
                  </div>
                </div>
              </div>

              <!-- Recent Bookmarks -->
              <div class="pf-card">
                <div class="pf-card-head">
                  <h5><i class="ti-heart"></i> Recent Bookmarks</h5>
                  <a href="/bookmarks" class="pf-see-all">See All <i class="ti-angle-right"></i></a>
                </div>
                <?php if(count($bookmarks) > 0): ?>
                <div class="pf-bm-list">
                  <?php foreach ($bookmarks as $value): ?>
                  <a href="/manhwa/<?= $value->slug ?>" class="pf-bm-item">
                    <img src="<?=$cdnUrl?>/manga/<?= $value->slug ?>/cover/cover_thumb_2.webp" alt="" class="pf-bm-cover">
                    <div class="pf-bm-info">
                      <span class="pf-bm-name"><?= esc($value->name) ?></span>
                      <span class="pf-bm-time"><?= time_elapsed_string($value->created_at) ?></span>
                    </div>
                  </a>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="pf-empty">No bookmarks yet. Start exploring!</div>
                <?php endif; ?>
              </div>

              <!-- Quick Actions -->
              <div class="pf-card">
                <div class="pf-card-head">
                  <h5><i class="ti-settings"></i> Quick Actions</h5>
                </div>
                <div class="pf-actions">
                  <a href="/profile/edit" class="pf-action-btn"><i class="ti-pencil-alt"></i> Edit Profile</a>
                  <a href="/changePass" class="pf-action-btn"><i class="ti-lock"></i> Change Password</a>
                  <a href="/history" class="pf-action-btn"><i class="ti-timer"></i> Reading History</a>
                  <a href="/notification" class="pf-action-btn"><i class="ti-bell"></i> Notifications</a>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
  .pf-avatar-letter{
    width:80px;height:80px;border-radius:50%;
    background:linear-gradient(135deg,#4ecdc4,#2a8a82);
    display:flex;align-items:center;justify-content:center;
    font-size:32px;font-weight:700;color:#fff;margin:0 auto;
  }
  .pf-card{
    background:#1a2530;border:1px solid #2e3e45;border-radius:12px;
    padding:20px 24px;margin-bottom:16px;
  }
  .pf-card-head{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #2e3e45;
  }
  .pf-card-head h5{margin:0;font-size:15px;font-weight:700;color:#e8e8e8}
  .pf-card-head h5 i{margin-right:8px;color:#4ecdc4}
  .pf-see-all{font-size:12px;color:#4ecdc4;text-decoration:none;font-weight:500}
  .pf-see-all:hover{color:#6ee7d7;text-decoration:none}

  /* Hero */
  .pf-hero{overflow:hidden}
  .pf-hero-top{display:flex;align-items:center;gap:20px;margin-bottom:20px}
  .pf-hero-avatar{
    width:80px;height:80px;border-radius:50%;overflow:hidden;flex-shrink:0;
    border:3px solid #4ecdc4;box-shadow:0 0 20px rgba(78,205,196,.2);
  }
  .pf-hero-avatar img{width:100%;height:100%;object-fit:cover;display:block}
  .pf-hero-avatar span{
    width:100%;height:100%;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#3a5a60,#2a3a45);
    color:#4ecdc4;font-size:30px;font-weight:700;
  }
  .pf-hero-name{margin:0 0 4px;font-size:20px;font-weight:700;color:#fff}
  .pf-hero-email{margin:0 0 10px;font-size:13px;color:#6a7a8a}
  .pf-edit-btn{
    display:inline-flex;align-items:center;gap:5px;
    padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;
    background:transparent;border:1px solid #4ecdc4;color:#4ecdc4;
    text-decoration:none;transition:all .2s;
  }
  .pf-edit-btn:hover{background:#4ecdc4;color:#000;text-decoration:none}

  /* Stats */
  .pf-stats{
    display:flex;border-top:1px solid #2e3e45;padding-top:16px;
  }
  .pf-stat{
    flex:1;text-align:center;text-decoration:none;
    padding:8px 0;border-right:1px solid #2e3e45;
    transition:background .2s;border-radius:6px;
  }
  .pf-stat:last-child{border-right:none}
  .pf-stat:hover{background:rgba(78,205,196,.05)}
  .pf-stat-num{display:block;font-size:22px;font-weight:700;color:#4ecdc4;line-height:1.2}
  .pf-stat-label{display:block;font-size:11px;color:#6a7a8a;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}

  /* Info Grid */
  .pf-info-grid{display:flex;flex-direction:column;gap:12px}
  .pf-info-item{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 14px;background:#141e28;border-radius:8px;
  }
  .pf-info-label{font-size:13px;color:#6a7a8a;font-weight:500}
  .pf-info-value{font-size:13px;color:#e8e8e8;font-weight:600}

  /* Bookmarks */
  .pf-bm-list{display:flex;flex-direction:column;gap:10px}
  .pf-bm-item{
    display:flex;align-items:center;gap:12px;
    padding:10px 12px;background:#141e28;border-radius:8px;
    text-decoration:none;transition:all .2s;border:1px solid transparent;
  }
  .pf-bm-item:hover{border-color:#2e3e45;background:#1a2a35;transform:translateX(4px)}
  .pf-bm-cover{width:40px;height:54px;border-radius:4px;object-fit:cover;flex-shrink:0}
  .pf-bm-info{min-width:0;flex:1}
  .pf-bm-name{
    display:block;font-size:13px;font-weight:600;color:#e8e8e8;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  }
  .pf-bm-time{display:block;font-size:11px;color:#6a7a8a;margin-top:3px}

  /* Empty */
  .pf-empty{text-align:center;padding:24px;color:#5a6a7a;font-size:13px}

  /* Quick Actions */
  .pf-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .pf-action-btn{
    display:flex;align-items:center;gap:8px;
    padding:12px 16px;background:#141e28;border-radius:8px;
    color:#8a9aaa;font-size:13px;font-weight:500;text-decoration:none;
    transition:all .2s;border:1px solid transparent;
  }
  .pf-action-btn i{color:#4ecdc4;font-size:16px}
  .pf-action-btn:hover{border-color:#4ecdc4;color:#e8e8e8;background:#1a2a35}

  @media(max-width:480px){
    .pf-hero-top{flex-direction:column;text-align:center}
    .pf-actions{grid-template-columns:1fr}
    .pf-info-item{flex-direction:column;gap:4px;text-align:center}
  }
  </style>
<?php include APPPATH . 'Views/include/footer.php'; ?>
