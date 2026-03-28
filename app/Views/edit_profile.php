<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/profile">Profile</a></li>
            <li class="active">Edit Profile</li>
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
              <div class="user_title">
                <h5>Edit Profile</h5>
              </div>

              <?php if(session()->getFlashdata('success')): ?>
              <div class="ep-alert ep-alert-success">
                <span class="ep-alert-icon">&#10003;</span>
                <?= session()->getFlashdata('success') ?>
              </div>
              <?php endif; ?>

              <?php if(session()->getFlashdata('error')): ?>
              <div class="ep-alert ep-alert-error">
                <span class="ep-alert-icon">!</span>
                <?= session()->getFlashdata('error') ?>
              </div>
              <?php endif; ?>

              <div class="ep-card">
                <form action="/profile/update" method="post" id="editProfileForm" enctype="multipart/form-data">
                  <?= csrf_field() ?>

                  <div class="ep-form-group">
                    <label class="ep-label">Avatar</label>
                    <div class="ep-avatar-upload">
                      <?php
                        $avatarUrl = ($user_info->avatar == 1)
                          ? '/uploads/users/' . $user_info->id . '.jpg?t=' . time()
                          : null;
                      ?>
                      <div class="ep-avatar-preview" id="avatarPreview">
                        <?php if($avatarUrl): ?>
                          <img src="<?= $avatarUrl ?>" id="avatarImg">
                        <?php else: ?>
                          <span class="ep-avatar-placeholder" id="avatarPlaceholder"><?= strtoupper(substr($user_info->username ?: $user_info->email, 0, 1)) ?></span>
                        <?php endif; ?>
                        <div class="ep-avatar-overlay">
                          <i class="ti-camera"></i>
                        </div>
                      </div>
                      <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp" style="display:none">
                      <div class="ep-avatar-info">
                        <button type="button" class="ep-btn ep-btn-outline ep-btn-sm" onclick="document.getElementById('avatarInput').click()">
                          <i class="ti-upload"></i> Upload Photo
                        </button>
                        <span class="ep-avatar-hint">JPG, PNG or WebP. Max 2MB.</span>
                      </div>
                    </div>
                  </div>

                  <div class="ep-form-group">
                    <label class="ep-label">Username</label>
                    <div class="ep-input-wrap" style="opacity:.6;cursor:not-allowed;">
                      <span class="ep-input-icon"><i class="ti-user"></i></span>
                      <input type="text" class="ep-input" value="<?= esc($user_info->username) ?>" disabled style="cursor:not-allowed;">
                    </div>
                    <span style="font-size:11px;color:#5a6a7a;margin-top:4px;display:block;">Username cannot be changed</span>
                  </div>

                  <div class="ep-form-group">
                    <label class="ep-label">Email</label>
                    <div class="ep-input-wrap">
                      <span class="ep-input-icon"><i class="ti-email"></i></span>
                      <input type="email" name="email" class="ep-input" value="<?= esc($user_info->email) ?>" placeholder="Enter email" required maxlength="255">
                    </div>
                  </div>

                  <div class="ep-form-actions">
                    <button type="submit" class="ep-btn ep-btn-primary">
                      <i class="ti-check"></i> Save Changes
                    </button>
                    <a href="/profile" class="ep-btn ep-btn-secondary">Cancel</a>
                  </div>
                </form>
              </div>

              <div class="ep-card" style="margin-top:20px;">
                <div class="ep-card-header">
                  <h6>Security</h6>
                </div>
                <a href="/changePass" class="ep-btn ep-btn-outline">
                  <i class="ti-lock"></i> Change Password
                </a>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
  .ep-avatar-circle{
    width:80px;height:80px;border-radius:50%;
    background:linear-gradient(135deg,#4ecdc4,#2a8a82);
    display:flex;align-items:center;justify-content:center;
    font-size:32px;font-weight:700;color:#fff;margin:0 auto;
  }
  .ep-alert{
    padding:12px 16px;border-radius:8px;margin-bottom:20px;
    display:flex;align-items:center;gap:10px;font-size:14px;
  }
  .ep-alert-success{background:rgba(78,205,196,.12);border:1px solid rgba(78,205,196,.3);color:#4ecdc4}
  .ep-alert-error{background:rgba(255,80,80,.12);border:1px solid rgba(255,80,80,.3);color:#ff6b6b}
  .ep-alert-icon{
    width:24px;height:24px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:12px;flex-shrink:0;
  }
  .ep-alert-success .ep-alert-icon{background:rgba(78,205,196,.2)}
  .ep-alert-error .ep-alert-icon{background:rgba(255,80,80,.2)}
  .ep-card{
    background:#1a2530;border:1px solid #2e3e45;border-radius:12px;padding:24px;
  }
  .ep-card-header{margin-bottom:16px}
  .ep-card-header h6{font-size:16px;font-weight:700;color:#e8e8e8;margin:0}
  .ep-form-group{margin-bottom:20px}
  .ep-label{
    display:block;font-size:13px;font-weight:600;color:#8a9aaa;
    margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;
  }
  .ep-input-wrap{
    position:relative;display:flex;align-items:center;
    background:#141e28;border:1px solid #2e3e45;border-radius:8px;
    transition:border-color .2s;
  }
  .ep-input-wrap:focus-within{border-color:#4ecdc4;box-shadow:0 0 0 3px rgba(78,205,196,.1)}
  .ep-input-icon{
    padding:0 12px;color:#5a6a7a;font-size:16px;flex-shrink:0;
  }
  .ep-input{
    flex:1;background:transparent;border:none;outline:none;
    padding:12px 14px 12px 0;font-size:14px;color:#e8e8e8;
    font-family:inherit;
  }
  .ep-input::placeholder{color:#4a5a6a}
  .ep-form-actions{display:flex;gap:12px;margin-top:28px}
  .ep-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;
    text-decoration:none;border:none;cursor:pointer;transition:all .2s;
    font-family:inherit;
  }
  .ep-btn-primary{
    background:linear-gradient(135deg,#4ecdc4,#44b3ab);color:#fff;
    box-shadow:0 4px 12px rgba(78,205,196,.3);
  }
  .ep-btn-primary:hover{
    transform:translateY(-1px);box-shadow:0 6px 20px rgba(78,205,196,.4);
    color:#fff;
  }
  .ep-btn-secondary{background:#2e3e45;color:#aab}
  .ep-btn-secondary:hover{background:#3a4e55;color:#ddd}
  .ep-btn-outline{
    background:transparent;border:1px solid #2e3e45;color:#8a9aaa;
  }
  .ep-btn-outline:hover{border-color:#4ecdc4;color:#4ecdc4}
  .ep-btn-sm{padding:7px 14px;font-size:12px}
  .ep-avatar-upload{display:flex;align-items:center;gap:20px}
  .ep-avatar-preview{
    width:90px;height:90px;border-radius:50%;overflow:hidden;
    position:relative;cursor:pointer;flex-shrink:0;
    border:3px solid #2e3e45;transition:border-color .2s;
  }
  .ep-avatar-preview:hover{border-color:#4ecdc4}
  .ep-avatar-preview img{width:100%;height:100%;object-fit:cover;display:block}
  .ep-avatar-placeholder{
    width:100%;height:100%;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#3a5a60,#2a3a45);
    color:#4ecdc4;font-size:34px;font-weight:700;
  }
  .ep-avatar-overlay{
    position:absolute;inset:0;background:rgba(0,0,0,.5);
    display:flex;align-items:center;justify-content:center;
    opacity:0;transition:opacity .2s;font-size:22px;color:#fff;
  }
  .ep-avatar-preview:hover .ep-avatar-overlay{opacity:1}
  .ep-avatar-info{display:flex;flex-direction:column;gap:8px}
  .ep-avatar-hint{font-size:11px;color:#5a6a7a}
  </style>

  <script>
  document.getElementById('avatarPreview').onclick = function(){
    document.getElementById('avatarInput').click();
  };
  document.getElementById('avatarInput').onchange = function(){
    var file = this.files[0];
    if(!file) return;
    if(file.size > 2*1024*1024){
      alert('File too large. Max 2MB.');
      this.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function(e){
      var preview = document.getElementById('avatarPreview');
      var existing = preview.querySelector('img');
      var placeholder = document.getElementById('avatarPlaceholder');
      if(placeholder) placeholder.style.display = 'none';
      if(existing){
        existing.src = e.target.result;
      } else {
        var img = document.createElement('img');
        img.src = e.target.result;
        img.id = 'avatarImg';
        preview.insertBefore(img, preview.firstChild);
      }
    };
    reader.readAsDataURL(file);
  };
  </script>

<?php include APPPATH . 'Views/include/footer.php'; ?>
