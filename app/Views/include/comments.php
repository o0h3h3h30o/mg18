<!-- Comment Section -->
<?php
  // Generate unique instance ID to support multiple comment sections on same page
  $cmtInst = $comment_post_type . '_' . $comment_post_id;
?>
<style>
.cmt-section{background:#1e2226;border:1px solid #3a3f45;border-radius:8px;padding:20px 24px;margin:0;}
.cmt-section *{box-sizing:border-box;}

/* Notice bar */
.cmt-section .cmt-notice{background:rgba(60,120,80,0.15);border:2px dashed rgba(80,180,120,0.4);border-radius:6px;padding:14px 20px;text-align:center;color:#a0d4b8;font-size:14px;margin-bottom:20px;display:block;}
.cmt-section .cmt-notice a{color:#6ee7b7;text-decoration:underline;font-weight:500;}

/* Header bar */
.cmt-section .cmt-header{display:flex!important;justify-content:space-between!important;align-items:center!important;margin-bottom:16px;padding:0;}
.cmt-section .cmt-count{color:#ccc;font-size:15px;font-weight:400;margin:0;padding:0;}
.cmt-section .cmt-sort{display:flex!important;gap:0;margin:0;padding:0;}
.cmt-section .cmt-sort button{background:none!important;border:none!important;color:#666;font-size:14px;cursor:pointer;padding:4px 10px;font-weight:500;display:inline-block;width:auto;min-width:0;}
.cmt-section .cmt-sort button:hover{color:#bbb;}
.cmt-section .cmt-sort button.active{color:#fff;font-weight:700;}

/* Write form */
.cmt-section .cmt-form{display:flex!important;gap:12px;margin-bottom:28px;align-items:flex-start;padding:0;}
.cmt-section .cmt-form-avatar{width:38px;height:38px;min-width:38px;border-radius:50%;background:#3a4a55;display:flex!important;align-items:center;justify-content:center;color:#788;font-size:20px;flex-shrink:0;overflow:hidden;margin:0;padding:0;}
.cmt-section .cmt-form-avatar img{width:100%;height:100%;object-fit:cover;}
.cmt-section .cmt-form-body{flex:1;min-width:0;}
.cmt-section .cmt-form textarea{width:100%!important;background:#3a4a55!important;border:1px solid #4a5a65!important;border-radius:4px;color:#ccc!important;padding:14px 16px!important;font-size:14px;resize:vertical;min-height:80px;box-sizing:border-box;font-family:inherit;line-height:1.5;box-shadow:none!important;outline:none!important;}
.cmt-section .cmt-form textarea:focus{border-color:#5a7a6a!important;background:#3e4e58!important;}
.cmt-section .cmt-form textarea::placeholder{color:#778;}
.cmt-section .cmt-form-actions{display:flex!important;justify-content:flex-end;gap:8px;margin-top:8px;}
.cmt-section .cmt-btn{padding:7px 20px;border-radius:4px;border:none;cursor:pointer;font-size:13px;font-weight:600;width:auto!important;min-width:0!important;display:inline-block;}
.cmt-section .cmt-btn-primary{background:#e54040!important;color:#fff!important;}
.cmt-section .cmt-btn-primary:hover{background:#d03030!important;}
.cmt-section .cmt-btn-cancel{background:#3a4555!important;color:#999!important;}
.cmt-section .cmt-btn-cancel:hover{background:#4a5565!important;color:#ccc!important;}
.cmt-section .cmt-btn:disabled{opacity:0.4;cursor:not-allowed;}
.cmt-section .cmt-login-msg{text-align:center;padding:20px;color:#888;font-size:14px;}
.cmt-section .cmt-login-msg a{color:#6ee7b7;font-weight:600;text-decoration:none;}
.cmt-section .cmt-error{color:#e54040;font-size:13px;margin-top:4px;display:none;}

/* Comment list */
.cmt-section .cmt-list{list-style:none!important;padding:0!important;margin:0!important;transition:opacity 0.2s ease;}
.cmt-section .cmt-item{padding:0;list-style:none!important;display:block;background:transparent;border:none;border-radius:0;margin-bottom:10px;}

/* Comment row */
.cmt-section .cmt-row{display:flex!important;flex-direction:row!important;gap:12px;align-items:flex-start;border:1px solid #3a3f45;border-radius:6px;padding:14px 16px;background:#252a2e;}
.cmt-section .cmt-avatar{width:38px;height:38px;min-width:38px;border-radius:50%;background:#3a4a55;display:flex!important;align-items:center;justify-content:center;color:#8a9aaa;font-size:16px;font-weight:700;flex-shrink:0;overflow:hidden;margin:0;padding:0;}
.cmt-section .cmt-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.cmt-section .cmt-content{flex:1!important;min-width:0;text-align:left!important;}
.cmt-section .cmt-username{font-weight:700;color:#e8e8e8;font-size:14px;display:flex!important;align-items:center;gap:8px;text-align:left;}
.cmt-section .cmt-chapter-badge{font-size:11px;font-weight:600;color:#6ee7b7;background:rgba(110,231,183,0.12);border:1px solid rgba(110,231,183,0.3);border-radius:3px;padding:1px 8px;white-space:nowrap;text-decoration:none;}
a.cmt-chapter-badge:hover{background:rgba(110,231,183,0.25);color:#a0f0d0;}
.cmt-section .cmt-time{color:#666;font-size:12px;display:block;margin-top:1px;margin-bottom:6px;text-align:left;}
.cmt-section .cmt-body{color:#ccc;font-size:14px;line-height:1.65;word-break:break-word;white-space:pre-wrap;margin-bottom:10px;text-align:left;}
.cmt-section .cmt-mention{color:#6ee7b7;font-weight:600;cursor:default;}

/* Actions */
.cmt-section .cmt-actions{display:flex!important;align-items:center;gap:4px;justify-content:flex-start!important;}
.cmt-section .cmt-actions button{background:none!important;border:none!important;color:#666;font-size:13px;cursor:pointer;padding:2px 8px;display:inline-flex!important;align-items:center;gap:5px;width:auto!important;min-width:0!important;box-shadow:none!important;}
.cmt-section .cmt-actions button:hover{color:#bbb;}
.cmt-section .cmt-actions .cmt-act-like.active{color:#6ee7b7;}
.cmt-section .cmt-actions .cmt-act-dislike.active{color:#e54040;}
.cmt-section .cmt-actions .cmt-act-reply:hover{color:#6ee7b7;}

/* Show replies button inline */
.cmt-section .cmt-show-replies{background:none!important;border:none!important;color:#5a9aaa;font-size:13px;cursor:pointer;padding:2px 8px;font-weight:500;width:auto!important;display:inline-flex!important;align-items:center;gap:5px;min-width:0!important;box-shadow:none!important;}
.cmt-section .cmt-show-replies:hover{color:#7ac0d0;}

/* Replies */
.cmt-section .cmt-replies{margin-left:50px;margin-top:8px;position:relative;padding:0!important;}
.cmt-section .cmt-replies::before{display:none;}
.cmt-section .cmt-replies .cmt-item{padding:0;background:transparent;border:none;border-radius:0;margin-bottom:6px;}
.cmt-section .cmt-replies .cmt-row{border:1px solid #33383d;border-radius:5px;padding:10px 12px;background:transparent;}
.cmt-section .cmt-replies .cmt-avatar{width:32px;height:32px;min-width:32px;font-size:13px;}
.cmt-section .cmt-replies .cmt-username{font-size:13px;}
.cmt-section .cmt-replies .cmt-body{font-size:13px;}
.cmt-section .cmt-replies .cmt-actions button{font-size:12px;}

/* Reply form */
.cmt-section .cmt-reply-form{margin:8px 0 6px 50px;display:flex!important;gap:10px;align-items:flex-start;}
.cmt-section .cmt-reply-form textarea{width:100%!important;min-height:50px;font-size:13px;background:#3a4a55!important;border:1px solid #4a5a65!important;border-radius:4px;color:#ccc!important;padding:10px 12px!important;box-sizing:border-box;font-family:inherit;resize:vertical;box-shadow:none!important;}
.cmt-section .cmt-reply-form textarea:focus{border-color:#5a7a6a!important;outline:none!important;}

/* Load more */
.cmt-section .cmt-load-more{text-align:center;padding:16px 0;}
.cmt-section .cmt-load-more button{background:transparent!important;border:1px solid rgba(255,255,255,0.12)!important;color:#888;padding:10px 32px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:500;width:auto!important;}
.cmt-section .cmt-load-more button:hover{background:rgba(255,255,255,0.06)!important;color:#ccc;border-color:rgba(255,255,255,0.2)!important;}
.cmt-section .cmt-empty{text-align:center;color:#666;padding:30px;font-size:14px;}

/* Responsive */
@media(max-width:480px){
  .cmt-section .cmt-form{gap:8px;}
  .cmt-section .cmt-form-avatar{width:32px;height:32px;min-width:32px;font-size:16px;}
  .cmt-section .cmt-avatar{width:32px;height:32px;min-width:32px;font-size:14px;}
  .cmt-section .cmt-replies{margin-left:34px;}
  .cmt-section .cmt-reply-form{margin-left:34px;}
}
</style>

<div class="cmt-section" id="cmtSection_<?= $cmtInst ?>" data-post-id="<?= $comment_post_id ?>" data-post-type="<?= $comment_post_type ?>" data-inst="<?= $cmtInst ?>">

  <!-- Notice bar -->
  <div class="cmt-notice">
    Note: Please take a moment to <a href="javascript:void(0)">read the comment rules</a> before posting.
  </div>

  <!-- Header -->
  <div class="cmt-header">
    <div class="cmt-count" id="cmtCount_<?= $cmtInst ?>">0 comments</div>
    <div class="cmt-sort">
      <button onclick="CMT['<?= $cmtInst ?>'].sort('best')" id="sortBest_<?= $cmtInst ?>">Best</button>
      <button class="active" onclick="CMT['<?= $cmtInst ?>'].sort('newest')" id="sortNewest_<?= $cmtInst ?>">Newest</button>
      <button onclick="CMT['<?= $cmtInst ?>'].sort('oldest')" id="sortOldest_<?= $cmtInst ?>">Oldest</button>
    </div>
  </div>

  <!-- Post form - loaded via AJAX to avoid CF cache issues -->
  <div id="cmtFormWrap_<?= $cmtInst ?>"></div>

  <!-- Comments list -->
  <ul class="cmt-list" id="cmtList_<?= $cmtInst ?>"></ul>
  <div class="cmt-load-more" id="cmtMore_<?= $cmtInst ?>" style="display:none;">
    <button onclick="CMT['<?= $cmtInst ?>'].load()">Show more comments</button>
  </div>
</div>

<script>
if(typeof CMT==='undefined') window.CMT={};
(function(){
  var inst = '<?= $cmtInst ?>';
  var section = document.getElementById('cmtSection_'+inst);
  var postId = section.dataset.postId;
  var postType = section.dataset.postType;
  var postTypeForWrite = postType === 'manga_all' ? 'manga' : postType;
  var currentPage = 0;
  var totalPages = 1;
  var currentSort = 'newest';
  var currentUserId = 0;
  var isAdmin = false;
  var userInitial = '?';
  var userAvatar = '';

  function $(id){ return document.getElementById(id); }

  var api = {};

  api.sort = function(sort){
    if(currentSort === sort) return;
    currentSort = sort;
    section.querySelectorAll('.cmt-sort button').forEach(function(b){b.classList.remove('active');});
    $('sort'+sort.charAt(0).toUpperCase()+sort.slice(1)+'_'+inst).classList.add('active');
    var list = $('cmtList_'+inst);
    list.style.opacity = '0.4';
    list.style.pointerEvents = 'none';
    currentPage = 0;
    fetch('/api/comments?post_id='+postId+'&post_type='+postType+'&page=1&sort='+currentSort)
      .then(function(r){return r.json()})
      .then(function(d){
        if(d.status!=='ok') return;
        currentPage = 1;
        totalPages = d.pages;
        $('cmtCount_'+inst).textContent = d.total+' comment'+(d.total!==1?'s':'');
        list.innerHTML = '';
        if(!d.comments.length) list.innerHTML = '<div class="cmt-empty">No comments yet. Be the first!</div>';
        d.comments.forEach(function(c){ appendComment(c, false); });
        $('cmtMore_'+inst).style.display = currentPage < totalPages ? '' : 'none';
        list.style.opacity = '1';
        list.style.pointerEvents = '';
      })
      .catch(function(){ list.style.opacity='1'; list.style.pointerEvents=''; });
  };

  api.load = function(){
    currentPage++;
    fetch('/api/comments?post_id='+postId+'&post_type='+postType+'&page='+currentPage+'&sort='+currentSort)
      .then(function(r){return r.json()})
      .then(function(d){
        if(d.status!=='ok') return;
        // Get user info from first load
        if(d.me && !currentUserId){
          currentUserId = d.me.id;
          isAdmin = d.me.role === 'admin';
          userInitial = (d.me.username||'?').charAt(0).toUpperCase();
          userAvatar = d.me.avatar || '';
          loadCommentForm();
        }
        totalPages = d.pages;
        $('cmtCount_'+inst).textContent = d.total+' comment'+(d.total!==1?'s':'');
        if(!d.comments.length && currentPage===1){
          $('cmtList_'+inst).innerHTML='<div class="cmt-empty">No comments yet. Be the first!</div>';
        }
        d.comments.forEach(function(c){ appendComment(c, false); });
        $('cmtMore_'+inst).style.display = currentPage < totalPages ? '' : 'none';
      });
  };

  api.post = function(){
    var input = $('cmtInput_'+inst);
    var btn = $('cmtBtn_'+inst);
    var errEl = $('cmtError_'+inst);
    var text = input.value.trim();
    if(!text){ errEl.textContent='Please write something.'; errEl.style.display='block'; return; }
    errEl.style.display='none';
    btn.disabled=true; btn.textContent='Posting...';
    var fd = new FormData();
    fd.append('post_id', postId);
    fd.append('post_type', postTypeForWrite);
    fd.append('comment', text);
    fetch('/api/comments',{method:'POST',body:fd})
      .then(function(r){return r.json()})
      .then(function(d){
        btn.disabled=false; btn.textContent='Comment';
        if(d.status==='error'){ errEl.textContent=d.message; errEl.style.display='block'; return; }
        input.value='';
        var emptyEl = section.querySelector('.cmt-empty');
        if(emptyEl) emptyEl.remove();
        appendComment(d.comment, true);
        var countEl = $('cmtCount_'+inst);
        var m = countEl.textContent.match(/\d+/);
        var n = m ? parseInt(m[0])+1 : 1;
        countEl.textContent = n+' comment'+(n!==1?'s':'');
      })
      .catch(function(){btn.disabled=false;btn.textContent='Comment';});
  };

  api.reply = function(parentId){
    var input = $('replyInput_'+inst+'_'+parentId);
    var btn = $('replyBtn_'+inst+'_'+parentId);
    var errEl = $('replyError_'+inst+'_'+parentId);
    var text = input.value.trim();
    if(!text){ errEl.textContent='Please write something.'; errEl.style.display='block'; return; }
    errEl.style.display='none';
    btn.disabled=true; btn.textContent='Posting...';
    var fd = new FormData();
    fd.append('post_id', postId);
    fd.append('post_type', postTypeForWrite);
    fd.append('comment', text);
    fd.append('parent_comment', parentId);
    fetch('/api/comments',{method:'POST',body:fd})
      .then(function(r){return r.json()})
      .then(function(d){
        btn.disabled=false; btn.textContent='Reply';
        if(d.status==='error'){ errEl.textContent=d.message; errEl.style.display='block'; return; }
        input.value='';
        api.cancelReply(parentId);
        appendReply(parentId, d.comment);
      })
      .catch(function(){btn.disabled=false;btn.textContent='Reply';});
  };

  api.showReplyForm = function(parentId, replyToUser){
    section.querySelectorAll('.cmt-reply-form').forEach(function(f){f.remove();});
    var item = $('cmt_'+inst+'_'+parentId);
    if(!item) return;
    var mention = replyToUser ? '@' + decodeURIComponent(replyToUser) + ' ' : '';
    var html = '<div class="cmt-reply-form" id="replyForm_'+inst+'_'+parentId+'">'
      +'<div class="cmt-avatar" style="width:30px;height:30px;min-width:30px;font-size:13px;">'+userInitial+'</div>'
      +'<div style="flex:1;min-width:0;">'
      +'<textarea id="replyInput_'+inst+'_'+parentId+'" placeholder="Write a reply..." maxlength="2000">'+mention+'</textarea>'
      +'<div class="cmt-error" id="replyError_'+inst+'_'+parentId+'"></div>'
      +'<div class="cmt-form-actions">'
      +'<button class="cmt-btn cmt-btn-cancel" onclick="CMT[\''+inst+'\'].cancelReply('+parentId+')">Cancel</button>'
      +'<button class="cmt-btn cmt-btn-primary" onclick="CMT[\''+inst+'\'].reply('+parentId+')" id="replyBtn_'+inst+'_'+parentId+'">Reply</button>'
      +'</div></div></div>';
    var cmtRow = item.querySelector('.cmt-row');
    if(cmtRow) cmtRow.insertAdjacentHTML('afterend', html);
    else item.insertAdjacentHTML('beforeend', html);
    var inp = $('replyInput_'+inst+'_'+parentId);
    inp.focus();
    inp.setSelectionRange(inp.value.length, inp.value.length);
  };

  api.cancelReply = function(parentId){
    var f = $('replyForm_'+inst+'_'+parentId);
    if(f) f.remove();
  };

  api.toggleReplies = function(parentId){
    var repliesDiv = $('replies_'+inst+'_'+parentId);
    var toggleBtn = $('toggle_'+inst+'_'+parentId);
    if(!repliesDiv || !toggleBtn) return;

    var allReplies = [];
    try { allReplies = JSON.parse(repliesDiv.dataset.allReplies || '[]'); } catch(e){}
    var shown = parseInt(repliesDiv.dataset.shown || '0');
    var total = allReplies.length;
    var isHidden = repliesDiv.style.display === 'none';

    if(shown >= total && shown > 0){
      if(isHidden){
        repliesDiv.style.display = '';
        toggleBtn.innerHTML = '<i class="fa fa-caret-up"></i> Hide replies ('+total+')';
      } else {
        repliesDiv.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fa fa-caret-down"></i> '+total+' replies';
      }
      return;
    }

    repliesDiv.style.display = '';
    var end = Math.min(shown + 5, total);
    for(var i = shown; i < end; i++){
      repliesDiv.insertAdjacentHTML('beforeend', buildHtml(allReplies[i], true));
    }
    repliesDiv.dataset.shown = end;
    var remaining = total - end;
    if(remaining > 0){
      toggleBtn.innerHTML = '<i class="fa fa-caret-down"></i> Show more replies ('+remaining+' left)';
    } else {
      toggleBtn.innerHTML = '<i class="fa fa-caret-up"></i> Hide replies ('+total+')';
    }
  };

  api.like = function(id, type){
    if(!currentUserId){ alert('Please login first.'); return; }
    var fd = new FormData();
    fd.append('comment_id', id);
    fd.append('type', type);
    fetch('/api/comments/react',{method:'POST',body:fd})
      .then(function(r){return r.json()})
      .then(function(d){
        if(d.status==='ok'){
          var el;
          el = $('likes_'+inst+'_'+id); if(el) el.textContent = d.likes||0;
          el = $('dislikes_'+inst+'_'+id); if(el) el.textContent = d.dislikes||0;
          el = $('likebtn_'+inst+'_'+id); if(el) el.classList.toggle('active', d.user_reaction==='like');
          el = $('dislikebtn_'+inst+'_'+id); if(el) el.classList.toggle('active', d.user_reaction==='dislike');
        }
      });
  };

  api.delete = function(id){
    if(!confirm('Delete this comment?')) return;
    var fd = new FormData();
    fd.append('comment_id', id);
    fetch('/api/comments/delete',{method:'POST',body:fd})
      .then(function(r){return r.json()})
      .then(function(d){
        if(d.status==='ok'){
          var el = $('cmt_'+inst+'_'+id);
          if(el) el.remove();
        }
      });
  };

  function highlightMentions(text){
    return text.replace(/@(\w+)/g, '<span class="cmt-mention">@$1</span>');
  }

  function buildHtml(c, isReply){
    var initial = (c.username||'?').charAt(0).toUpperCase();
    var avatarHtml = (c.avatar && c.avatar!=='0' && c.avatar!==0)
      ? '<img src="'+c.avatar+'" alt="">'
      : initial;

    var likeActive = (c.user_reaction==='like') ? ' active' : '';
    var dislikeActive = (c.user_reaction==='dislike') ? ' active' : '';
    var I = inst; // shorthand

    var acts = '';
    acts += '<button class="cmt-act-like'+likeActive+'" id="likebtn_'+I+'_'+c.id+'" onclick="CMT[\''+I+'\'].like('+c.id+',\'like\')" title="Like"><i class="fa fa-thumbs-up"></i> <span id="likes_'+I+'_'+c.id+'">'+(c.likes||0)+'</span></button>';
    acts += '<button class="cmt-act-dislike'+dislikeActive+'" id="dislikebtn_'+I+'_'+c.id+'" onclick="CMT[\''+I+'\'].like('+c.id+',\'dislike\')" title="Dislike"><i class="fa fa-thumbs-down"></i> <span id="dislikes_'+I+'_'+c.id+'">'+(c.dislikes||0)+'</span></button>';

    if(currentUserId && !isReply){
      acts += '<button class="cmt-act-reply" onclick="CMT[\''+I+'\'].showReplyForm('+c.id+',\''+encodeURIComponent(c.username)+'\')"><i class="fa fa-reply"></i> Reply</button>';
    }
    if(currentUserId && isReply){
      acts += '<button class="cmt-act-reply" onclick="CMT[\''+I+'\'].showReplyForm('+(c.parent_comment||c.id)+',\''+encodeURIComponent(c.username)+'\')"><i class="fa fa-reply"></i> Reply</button>';
    }

    var replyCount = (c.replies && c.replies.length) ? c.replies.length : 0;
    if(!isReply && replyCount > 0){
      acts += '<button class="cmt-show-replies" id="toggle_'+I+'_'+c.id+'" onclick="CMT[\''+I+'\'].toggleReplies('+c.id+')"><i class="fa fa-caret-down"></i> '+replyCount+' replies</button>';
    }

    var repliesHtml = '';
    if(!isReply){
      repliesHtml = '<div class="cmt-replies" id="replies_'+I+'_'+c.id+'" style="display:none;"></div>';
    }

    return '<li class="cmt-item" id="cmt_'+I+'_'+c.id+'">'
      +'<div class="cmt-row">'
      +'<div class="cmt-avatar">'+avatarHtml+'</div>'
      +'<div class="cmt-content">'
      +'<span class="cmt-username">'+c.username+(c.chapter_label ? ' '+(c.chapter_url ? '<a href="'+c.chapter_url+'" class="cmt-chapter-badge">'+c.chapter_label+'</a>' : '<span class="cmt-chapter-badge">'+c.chapter_label+'</span>') : '')+'</span>'
      +'<span class="cmt-time">'+c.time_ago+'</span>'
      +'<div class="cmt-body">'+highlightMentions(c.comment)+'</div>'
      +'<div class="cmt-actions">'+acts+'</div>'
      +'</div>'
      +'</div>'
      +repliesHtml
      +'</li>';
  }

  function appendComment(c, prepend){
    var list = $('cmtList_'+inst);
    var html = buildHtml(c, false);
    if(prepend) list.insertAdjacentHTML('afterbegin', html);
    else list.insertAdjacentHTML('beforeend', html);
    if(c.replies && c.replies.length){
      var repliesDiv = $('replies_'+inst+'_'+c.id);
      repliesDiv.dataset.allReplies = JSON.stringify(c.replies);
      repliesDiv.dataset.shown = '0';
    }
  }

  function appendReply(parentId, c){
    var repliesDiv = $('replies_'+inst+'_'+parentId);
    var toggleBtn = $('toggle_'+inst+'_'+parentId);
    if(!repliesDiv){
      var item = $('cmt_'+inst+'_'+parentId);
      item.insertAdjacentHTML('beforeend','<div class="cmt-replies" id="replies_'+inst+'_'+parentId+'"></div>');
      repliesDiv = $('replies_'+inst+'_'+parentId);
      repliesDiv.dataset.allReplies = '[]';
      repliesDiv.dataset.shown = '0';
    }
    if(!toggleBtn){
      var actionsDiv = section.querySelector('#cmt_'+inst+'_'+parentId+' > .cmt-row .cmt-actions');
      if(actionsDiv){
        actionsDiv.insertAdjacentHTML('beforeend','<button class="cmt-show-replies" id="toggle_'+inst+'_'+parentId+'" onclick="CMT[\''+inst+'\'].toggleReplies('+parentId+')"><i class="fa fa-caret-up"></i> Hide replies (1)</button>');
        toggleBtn = $('toggle_'+inst+'_'+parentId);
      }
    }
    var allReplies = [];
    try { allReplies = JSON.parse(repliesDiv.dataset.allReplies || '[]'); } catch(e){}
    allReplies.push(c);
    repliesDiv.dataset.allReplies = JSON.stringify(allReplies);
    repliesDiv.insertAdjacentHTML('beforeend', buildHtml(c, true));
    repliesDiv.dataset.shown = String(parseInt(repliesDiv.dataset.shown||'0') + 1);
    repliesDiv.style.display = '';
    var total = allReplies.length;
    if(toggleBtn) toggleBtn.innerHTML = '<i class="fa fa-caret-up"></i> Hide replies ('+total+')';
  }

  // Render comment form based on cookie (bypass CF cache)
  function loadCommentForm(){
    var wrap = $('cmtFormWrap_'+inst);
    if(!wrap) return;
    if(document.cookie.indexOf('is_logged=1') !== -1){
      var avatarHtml = userAvatar ? '<img src="'+userAvatar+'" alt="">' : '<i class="fa fa-user"></i>';
      wrap.innerHTML = '<div class="cmt-form">'
        +'<div class="cmt-form-avatar">'+avatarHtml+'</div>'
        +'<div class="cmt-form-body">'
        +'<textarea id="cmtInput_'+inst+'" placeholder="Write your message" maxlength="2000"></textarea>'
        +'<div class="cmt-error" id="cmtError_'+inst+'"></div>'
        +'<div class="cmt-form-actions">'
        +'<button class="cmt-btn cmt-btn-primary" onclick="CMT[\''+inst+'\'].post()" id="cmtBtn_'+inst+'">Comment</button>'
        +'</div></div></div>';
    } else {
      wrap.innerHTML = '<div class="cmt-login-msg"><a href="/login">Login</a> to leave a comment.</div>';
    }
  }

  api.refresh = function(){
    currentPage = 0;
    $('cmtList_'+inst).innerHTML = '';
    api.load();
  };

  CMT[inst] = api;
  loadCommentForm();
  api.load();
  setInterval(function(){ api.refresh(); }, 30000);
})();
</script>
