<div class="user_left">
  <div class="user_login">
    <div class="user_avatar">
      <?php if($user_info->avatar == 1): ?>
        <img src="/uploads/users/<?= $user_info->id ?>-thumb.jpg?t=<?= time() ?>" alt="" style="border-radius:50%;width:64px;height:64px;object-fit:cover;margin:0 auto;display:block;">
      <?php else: ?>
        <div class="us-avatar-letter"><?= strtoupper(substr($user_info->username ?: $user_info->email, 0, 1)) ?></div>
      <?php endif; ?>
    </div>
    <div class="user_name">
      <h5><?= esc($user_info->username ?: $user_info->email) ?></h5>
      <span>Member</span>
    </div>
  </div>
  <div class="user_menu">
    <ul>
      <li <?= ($active_page ?? '') === 'profile' ? 'class="active"' : '' ?>><a href="/profile"><i class="ti-info-alt"></i> Profile</a></li>
      <li <?= ($active_page ?? '') === 'bookmarks' ? 'class="active"' : '' ?>><a href="/bookmarks"><i class="ti-heart"></i> Bookmarks</a></li>
      <li <?= ($active_page ?? '') === 'history' ? 'class="active"' : '' ?>><a href="/history"><i class="ti-timer"></i> History</a></li>
      <li <?= ($active_page ?? '') === 'notification' ? 'class="active"' : '' ?>><a href="/notification"><i class="ti-bell"></i> Notifications</a></li>
      <li <?= ($active_page ?? '') === 'changePass' ? 'class="active"' : '' ?>><a href="/changePass"><i class="ti-lock"></i> Change Password</a></li>
      <li><a href="/logout"><i class="lnr lnr-exit"></i> Logout</a></li>
    </ul>
  </div>
</div>
<style>
.us-avatar-letter{
  width:64px;height:64px;border-radius:50%;
  background:linear-gradient(135deg,#4ecdc4,#2a8a82);
  display:flex;align-items:center;justify-content:center;
  font-size:26px;font-weight:700;color:#fff;margin:0 auto;
}
</style>
