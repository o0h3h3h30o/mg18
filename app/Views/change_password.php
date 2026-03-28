<?php include APPPATH . 'Views/include/header.php'; ?>
  <div class="user_main">
    <div class="section_breadcrumb">
      <div class="container">
        <div class="section_breadcrumb-content">
          <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/profile">Profile</a></li>
            <li class="active">Change Password</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="user_main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-3 col-sm-4 col-xs-12">
            <?php $active_page = 'changePass'; include APPPATH . 'Views/include/user_sidebar.php'; ?>
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="user_right">
              <div class="up-head">
                <h5><i class="ti-lock"></i> Change Password</h5>
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
                <form action="/changePass" method="post">
                  <?= csrf_field() ?>

                  <div class="ep-form-group">
                    <label class="ep-label">Current Password</label>
                    <div class="ep-input-wrap">
                      <span class="ep-input-icon"><i class="ti-lock"></i></span>
                      <input type="password" name="current_password" class="ep-input" placeholder="Enter current password" required>
                      <span class="ep-toggle-pw" onclick="togglePw(this)"><i class="ti-eye"></i></span>
                    </div>
                  </div>

                  <div class="ep-form-group">
                    <label class="ep-label">New Password</label>
                    <div class="ep-input-wrap">
                      <span class="ep-input-icon"><i class="ti-key"></i></span>
                      <input type="password" name="new_password" class="ep-input" placeholder="Enter new password (min 6 chars)" required minlength="6">
                      <span class="ep-toggle-pw" onclick="togglePw(this)"><i class="ti-eye"></i></span>
                    </div>
                  </div>

                  <div class="ep-form-group">
                    <label class="ep-label">Confirm New Password</label>
                    <div class="ep-input-wrap">
                      <span class="ep-input-icon"><i class="ti-key"></i></span>
                      <input type="password" name="confirm_password" class="ep-input" placeholder="Confirm new password" required minlength="6">
                      <span class="ep-toggle-pw" onclick="togglePw(this)"><i class="ti-eye"></i></span>
                    </div>
                  </div>

                  <div class="ep-form-actions">
                    <button type="submit" class="ep-btn ep-btn-primary">
                      <i class="ti-check"></i> Update Password
                    </button>
                    <a href="/profile" class="ep-btn ep-btn-secondary">Cancel</a>
                  </div>
                </form>
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
  .ep-toggle-pw{
    padding:0 14px;cursor:pointer;color:#5a6a7a;font-size:16px;
    transition:color .2s;flex-shrink:0;
  }
  .ep-toggle-pw:hover{color:#4ecdc4}
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
  </style>

  <script>
  function togglePw(el){
    var input = el.parentElement.querySelector('.ep-input');
    if(input.type === 'password'){
      input.type = 'text';
      el.innerHTML = '<i class="ti-eye" style="color:#4ecdc4"></i>';
    } else {
      input.type = 'password';
      el.innerHTML = '<i class="ti-eye"></i>';
    }
  }
  </script>

<?php include APPPATH . 'Views/include/footer.php'; ?>
