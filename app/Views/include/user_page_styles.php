<style>
/* === User Page Shared Styles === */

/* Header */
.up-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #2e3e45;
}
.up-head h5{margin:0;font-size:18px;font-weight:700;color:#e8e8e8}
.up-head h5 i{margin-right:8px;color:#4ecdc4}
.up-count{font-size:12px;color:#5a6a7a;font-weight:500}
.up-action-btn{
  background:transparent;border:1px solid #4ecdc4;color:#4ecdc4;
  padding:6px 16px;border-radius:20px;cursor:pointer;font-size:12px;
  font-weight:600;transition:all .2s;
}
.up-action-btn:hover{background:#4ecdc4;color:#000}

/* List */
.up-list{
  display:flex;flex-direction:column;gap:0;
  background:#1a2530;border-radius:12px;overflow:hidden;border:1px solid #2e3e45;
}

/* Item */
.up-item{
  display:flex;align-items:center;gap:14px;
  padding:14px 18px;border-bottom:1px solid #232e36;
  transition:background .15s;
}
.up-item:last-child{border-bottom:none}
.up-item:hover{background:#1e2e38}

/* Cover */
.up-item-cover{flex-shrink:0;display:block}
.up-item-cover img{
  width:48px;height:64px;border-radius:6px;object-fit:cover;display:block;
  transition:transform .2s;
}
.up-item-cover:hover img{transform:scale(1.05)}

/* Info */
.up-item-info{flex:1;min-width:0}
.up-item-name{
  display:block;font-size:14px;font-weight:600;color:#e8e8e8;
  text-decoration:none;margin-bottom:4px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.up-item-name:hover{color:#4ecdc4;text-decoration:none}

/* Meta */
.up-item-meta{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.up-meta-tag{font-size:12px;color:#5a6a7a}
.up-meta-tag i{margin-right:3px}
.up-meta-chap{
  font-size:12px;font-weight:600;color:#4ecdc4;
  text-decoration:none;padding:2px 10px;
  background:rgba(78,205,196,.1);border-radius:12px;
}
.up-meta-chap:hover{background:rgba(78,205,196,.2);color:#4ecdc4;text-decoration:none}

/* Continue btn (history page) */
.up-continue-btn{
  display:inline-flex;align-items:center;gap:4px;
  margin-top:6px;font-size:11px;font-weight:600;color:#4ecdc4;
  text-decoration:none;padding:3px 12px;
  border:1px solid rgba(78,205,196,.3);border-radius:14px;
  transition:all .2s;
}
.up-continue-btn:hover{background:rgba(78,205,196,.1);border-color:#4ecdc4;color:#4ecdc4;text-decoration:none}
.up-continue-btn i{font-size:10px}

/* Delete btn */
.up-item-del{
  flex-shrink:0;width:34px;height:34px;border-radius:8px;
  background:transparent;border:1px solid transparent;
  color:#5a6a7a;font-size:14px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
.up-item-del:hover{border-color:#ff6b6b;color:#ff6b6b;background:rgba(255,107,107,.08)}

/* Empty state */
.up-empty{
  text-align:center;padding:50px 20px;color:#4a5a65;font-size:14px;
  background:#1a2530;border-radius:12px;border:1px solid #2e3e45;
}
.up-empty p{margin:0}

/* Mobile */
@media(max-width:767px){
  .up-item{padding:12px 14px;gap:10px}
  .up-item-cover img{width:40px;height:54px;border-radius:4px}
  .up-item-name{font-size:13px}
  .up-head h5{font-size:16px}
  .up-item-meta{gap:8px}
}
@media(max-width:480px){
  .up-head{flex-direction:column;gap:10px;align-items:flex-start}
}
</style>
