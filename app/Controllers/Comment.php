<?php

namespace App\Controllers;

use App\Models\CommentModel;

class Comment extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CommentModel();
    }

    /**
     * GET /api/comments?post_id=123&post_type=manga&page=1&sort=newest|oldest
     */
    public function list()
    {
        $postId   = (int) $this->request->getGet('post_id');
        $postType = $this->request->getGet('post_type') ?? 'manga'; // manga|chapter
        $page     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $sortParam = $this->request->getGet('sort') ?? 'newest';
        $perPage  = 10;
        $offset   = ($page - 1) * $perPage;

        if (!$postId || !in_array($postType, ['manga', 'chapter', 'manga_all'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid params']);
        }

        // Determine ORDER BY
        if ($sortParam === 'best') {
            $orderBy = 'c.likes DESC, c.created_at DESC';
        } elseif ($sortParam === 'oldest') {
            $orderBy = 'c.created_at ASC';
        } else {
            $orderBy = 'c.created_at DESC';
        }

        // Build WHERE condition based on post_type
        if ($postType === 'manga_all') {
            // Get all chapter IDs for this manga
            $chapterIds = $this->db->table('chapter')
                ->select('id')
                ->where('manga_id', $postId)
                ->get()->getResultArray();
            $chapterIds = array_column($chapterIds, 'id');

            // Build condition: manga comments + all chapter comments
            $whereParams = [$postId];
            $whereSql = "(c.post_type = 'manga' AND c.post_id = ?)";
            if (!empty($chapterIds)) {
                $ph = implode(',', array_fill(0, count($chapterIds), '?'));
                $whereSql .= " OR (c.post_type = 'chapter' AND c.post_id IN ({$ph}))";
                $whereParams = array_merge($whereParams, $chapterIds);
            }
        } else {
            $whereSql = "c.post_id = ? AND c.post_type = ?";
            $whereParams = [$postId, $postType];
        }

        // Get top-level comments
        $total = $this->db->query(
            "SELECT COUNT(*) as cnt FROM comments c WHERE ({$whereSql}) AND c.parent_comment IS NULL",
            $whereParams
        )->getRow()->cnt;

        $queryParams = array_merge($whereParams, [$perPage, $offset]);
        $chapterJoin = ($postType === 'manga_all')
            ? "LEFT JOIN chapter ch ON c.post_type = 'chapter' AND ch.id = c.post_id LEFT JOIN manga m ON ch.manga_id = m.id"
            : '';
        $chapterSelect = ($postType === 'manga_all')
            ? ", ch.name as chapter_name, ch.number as chapter_number, ch.slug as chapter_slug, m.slug as manga_slug"
            : '';
        $comments = $this->db->query(
            "SELECT c.*, u.username, u.avatar{$chapterSelect}
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             {$chapterJoin}
             WHERE ({$whereSql}) AND c.parent_comment IS NULL
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
            $queryParams
        )->getResult();

        // Get replies for these comments
        $commentIds = array_column($comments, 'id');
        $replies = [];
        if (!empty($commentIds)) {
            $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
            $replyRows = $this->db->query(
                "SELECT c.*, u.username, u.avatar{$chapterSelect}
                 FROM comments c
                 LEFT JOIN users u ON u.id = c.user_id
                 {$chapterJoin}
                 WHERE c.parent_comment IN ({$placeholders})
                 ORDER BY c.created_at ASC",
                $commentIds
            )->getResult();

            foreach ($replyRows as $r) {
                $replies[$r->parent_comment][] = $r;
            }
        }

        // Get current user's reactions for all comments on this page
        $userReactions = [];
        if ($this->is_logged && $this->user_info) {
            $allIds = $commentIds;
            foreach ($replies as $parentReplies) {
                foreach ($parentReplies as $r) {
                    $allIds[] = $r->id;
                }
            }
            if (!empty($allIds)) {
                $ph = implode(',', array_fill(0, count($allIds), '?'));
                $params = array_merge([$this->user_info->id], $allIds);
                $rows = $this->db->query(
                    "SELECT comment_id, type FROM comment_reactions WHERE user_id = ? AND comment_id IN ({$ph})",
                    $params
                )->getResult();
                foreach ($rows as $row) {
                    $userReactions[$row->comment_id] = $row->type;
                }
            }
        }

        // Format
        $result = [];
        foreach ($comments as $c) {
            $item = $this->formatComment($c, $userReactions);
            $item['replies'] = [];
            if (isset($replies[$c->id])) {
                foreach ($replies[$c->id] as $r) {
                    $item['replies'][] = $this->formatComment($r, $userReactions);
                }
            }
            $result[] = $item;
        }

        return $this->response->setJSON([
            'status'   => 'ok',
            'comments' => $result,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $perPage),
        ]);
    }

    /**
     * POST /api/comments
     */
    public function store()
    {
        if (!$this->is_logged || !isset($this->user_info->id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please login to comment.']);
        }

        $postId   = (int) $this->request->getPost('post_id');
        $postType = $this->request->getPost('post_type') ?? 'manga';
        $comment  = trim($this->request->getPost('comment') ?? '');
        $parentId = $this->request->getPost('parent_comment');
        $parentId = $parentId ? (int) $parentId : null;

        if (!$postId || !in_array($postType, ['manga', 'chapter'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid params']);
        }
        if (empty($comment) || mb_strlen($comment) > 2000) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Comment is empty or too long (max 2000 chars).']);
        }

        // Rate limit: max 2 comments per minute
        $recentCount = $this->db->table('comments')
            ->where('user_id', $this->user_info->id)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 minute')))
            ->countAllResults();
        if ($recentCount >= 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Too many comments. Please wait a moment.']);
        }

        // If reply, verify parent exists
        if ($parentId) {
            $parent = $this->model->find($parentId);
            if (!$parent || $parent->post_id != $postId || $parent->post_type != $postType) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parent comment']);
            }
        }

        $now = date('Y-m-d H:i:s');
        $id = $this->model->insert([
            'comment'        => $comment,
            'post_id'        => $postId,
            'post_type'      => $postType,
            'user_id'        => $this->user_info->id,
            'parent_comment' => $parentId,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        // Get manga info for notifications
        $mangaId = null; $mangaSlug = null; $mangaName = null;
        if ($postType === 'manga') {
            $manga = $this->db->table('manga')->select('id, slug, name')->where('id', $postId)->get()->getRow();
            if ($manga) {
                $mangaId   = $manga->id;
                $mangaSlug = $manga->slug;
                $mangaName = $manga->name;
            }
        }

        $shortComment = mb_strlen($comment) > 150 ? mb_substr($comment, 0, 150) . '...' : $comment;
        $notifiedUserIds = [(int) $this->user_info->id]; // track who we already notified (skip self)

        // Notify parent comment author on reply
        if ($parentId && $parent) {
            $parentUserId = (int) $parent->user_id;
            if (!in_array($parentUserId, $notifiedUserIds)) {
                $this->db->table('notifications')->insert([
                    'user_id'      => $parentUserId,
                    'actor_id'     => (int) $this->user_info->id,
                    'type'         => 'reply',
                    'comment_id'   => $id,
                    'manga_id'     => $mangaId,
                    'manga_slug'   => $mangaSlug,
                    'manga_name'   => $mangaName,
                    'chapter_slug' => '',
                    'preview'      => $shortComment,
                    'is_read'      => 0,
                ]);
                $notifiedUserIds[] = $parentUserId;
            }
        }

        // Notify @mentioned users
        if (preg_match_all('/@(\w+)/', $comment, $matches)) {
            $mentionedNames = array_unique($matches[1]);
            foreach ($mentionedNames as $mentionName) {
                $mentionUser = $this->db->table('users')
                    ->select('id')
                    ->where('username', $mentionName)
                    ->get()->getRow();
                if ($mentionUser && !in_array((int)$mentionUser->id, $notifiedUserIds)) {
                    $this->db->table('notifications')->insert([
                        'user_id'      => (int) $mentionUser->id,
                        'actor_id'     => (int) $this->user_info->id,
                        'type'         => 'mention',
                        'comment_id'   => $id,
                        'manga_id'     => $mangaId,
                        'manga_slug'   => $mangaSlug,
                        'manga_name'   => $mangaName,
                        'chapter_slug' => '',
                        'preview'      => $shortComment,
                        'is_read'      => 0,
                    ]);
                    $notifiedUserIds[] = (int) $mentionUser->id;
                }
            }
        }

        return $this->response->setJSON([
            'status'  => 'ok',
            'comment' => [
                'id'        => $id,
                'comment'   => esc($comment),
                'username'  => esc($this->user_info->username),
                'avatar'    => (!empty($this->user_info->avatar) && $this->user_info->avatar != '0')
                                ? '/uploads/users/' . $this->user_info->id . '-thumb.jpg'
                                : null,
                'user_id'   => $this->user_info->id,
                'parent_comment' => $parentId,
                'created_at' => $now,
                'time_ago'  => 'Just now',
                'likes'     => 0,
                'dislikes'  => 0,
                'user_reaction' => null,
            ],
        ]);
    }

    /**
     * POST /api/comments/delete
     */
    public function delete()
    {
        if (!$this->is_logged || !isset($this->user_info->id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not logged in']);
        }

        $commentId = (int) $this->request->getPost('comment_id');
        $comment = $this->model->find($commentId);

        if (!$comment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Comment not found']);
        }

        // Only owner or admin can delete
        $isAdmin = isset($this->user_info->role) && $this->user_info->role === 'admin';
        if ($comment->user_id != $this->user_info->id && !$isAdmin) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        // Delete reactions for this comment and its replies
        $replyIds = $this->db->table('comments')
            ->select('id')
            ->where('parent_comment', $commentId)
            ->get()->getResultArray();
        $deleteIds = array_column($replyIds, 'id');
        $deleteIds[] = $commentId;
        $ph = implode(',', array_fill(0, count($deleteIds), '?'));
        $this->db->query("DELETE FROM comment_reactions WHERE comment_id IN ({$ph})", $deleteIds);

        // Delete replies first, then comment
        $this->model->where('parent_comment', $commentId)->delete();
        $this->model->delete($commentId);

        return $this->response->setJSON(['status' => 'ok']);
    }

    /**
     * POST /api/comments/react
     * Toggle like/dislike on a comment
     */
    public function react()
    {
        if (!$this->is_logged || !isset($this->user_info->id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please login first.']);
        }

        $commentId = (int) $this->request->getPost('comment_id');
        $type = $this->request->getPost('type'); // 'like' or 'dislike'

        if (!$commentId || !in_array($type, ['like', 'dislike'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid params']);
        }

        // Check comment exists
        $comment = $this->model->find($commentId);
        if (!$comment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Comment not found']);
        }

        $userId = $this->user_info->id;

        // Check existing reaction
        $existing = $this->db->table('comment_reactions')
            ->where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->get()->getRow();

        if ($existing) {
            if ($existing->type === $type) {
                // Same type = toggle off (remove reaction)
                $this->db->table('comment_reactions')
                    ->where('id', $existing->id)
                    ->delete();
            } else {
                // Different type = switch reaction
                $this->db->table('comment_reactions')
                    ->where('id', $existing->id)
                    ->update(['type' => $type, 'created_at' => date('Y-m-d H:i:s')]);
            }
        } else {
            // New reaction
            $this->db->table('comment_reactions')->insert([
                'comment_id' => $commentId,
                'user_id'    => $userId,
                'type'       => $type,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Get updated counts
        $likes = $this->db->table('comment_reactions')
            ->where('comment_id', $commentId)
            ->where('type', 'like')
            ->countAllResults();
        $dislikes = $this->db->table('comment_reactions')
            ->where('comment_id', $commentId)
            ->where('type', 'dislike')
            ->countAllResults();

        // Update denormalized counts on comments table
        $this->db->table('comments')
            ->where('id', $commentId)
            ->update(['likes' => $likes, 'dislikes' => $dislikes]);

        // Get user's current reaction after toggle
        $currentReaction = $this->db->table('comment_reactions')
            ->where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->get()->getRow();

        return $this->response->setJSON([
            'status'        => 'ok',
            'likes'         => $likes,
            'dislikes'      => $dislikes,
            'user_reaction' => $currentReaction ? $currentReaction->type : null,
        ]);
    }

    /**
     * GET /api/comments/recent - 5 newest comments for homepage widget
     */
    public function recent()
    {
        $limit = min(10, max(1, (int) ($this->request->getGet('limit') ?? 5)));

        // Cache 60 seconds
        $cacheKey = 'recent_comments_' . $limit;
        if ($cached = cache($cacheKey)) {
            return $this->response->setJSON($cached);
        }

        $comments = $this->db->query(
            "SELECT c.id, c.comment, c.created_at, c.post_id, c.post_type, c.user_id,
                    u.username, u.avatar,
                    m.name as manga_title, m.slug as manga_slug,
                    ch.name as chapter_name, ch.number as chapter_number, ch.slug as chapter_slug,
                    COALESCE(m2.slug, m.slug) as link_manga_slug,
                    m2.name as chapter_manga_name
             FROM comments c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN manga m ON c.post_type = 'manga' AND m.id = c.post_id
             LEFT JOIN chapter ch ON c.post_type = 'chapter' AND ch.id = c.post_id
             LEFT JOIN manga m2 ON ch.manga_id = m2.id
             WHERE c.parent_comment IS NULL
             ORDER BY c.created_at DESC
             LIMIT ?",
            [$limit]
        )->getResult();

        $result = [];
        foreach ($comments as $c) {
            // Build link
            $link = '#';
            $mangaTitle = '';
            if ($c->post_type === 'manga' && $c->manga_slug) {
                $link = '/manhwa/' . $c->manga_slug;
                $mangaTitle = $c->manga_title;
            } elseif ($c->post_type === 'chapter' && $c->link_manga_slug && $c->chapter_slug) {
                $link = '/manhwa/' . $c->link_manga_slug . '/' . $c->chapter_slug;
                $chapterLabel = $c->chapter_name ?: ('Chapter ' . $c->chapter_number);
                $mangaTitle = ($c->chapter_manga_name ? $c->chapter_manga_name . ' - ' : '') . $chapterLabel;
            }

            $avatarUrl = (!empty($c->avatar) && $c->avatar != '0' && !empty($c->user_id))
                ? '/uploads/users/' . $c->user_id . '-thumb.jpg'
                : null;

            $result[] = [
                'id'         => $c->id,
                'comment'    => esc(mb_substr($c->comment, 0, 120)) . (mb_strlen($c->comment) > 120 ? '...' : ''),
                'username'   => esc($c->username ?? 'Unknown'),
                'avatar'     => $avatarUrl,
                'time_ago'   => $this->timeAgo($c->created_at),
                'link'       => $link,
                'manga_title' => esc($mangaTitle),
            ];
        }

        $response = ['status' => 'ok', 'comments' => $result];
        cache()->save($cacheKey, $response, 60);
        return $this->response->setJSON($response);
    }

    private function formatComment(object $c, array $userReactions = []): array
    {
        // Build chapter label + url for manga_all view
        $chapterLabel = null;
        $chapterUrl = null;
        if (isset($c->post_type) && $c->post_type === 'chapter') {
            if (!empty($c->chapter_name)) {
                $chapterLabel = $c->chapter_name;
            } elseif (isset($c->chapter_number)) {
                $chapterLabel = 'Chapter ' . $c->chapter_number;
            }
            if (!empty($c->manga_slug) && !empty($c->chapter_slug)) {
                $chapterUrl = '/manhwa/' . $c->manga_slug . '/' . $c->chapter_slug;
            }
        }

        $avatarUrl = (!empty($c->avatar) && $c->avatar != '0' && !empty($c->user_id))
            ? '/uploads/users/' . $c->user_id . '-thumb.jpg'
            : null;

        return [
            'id'             => $c->id,
            'comment'        => esc($c->comment),
            'username'       => esc($c->username ?? 'Unknown'),
            'avatar'         => $avatarUrl,
            'user_id'        => $c->user_id,
            'parent_comment' => $c->parent_comment,
            'created_at'     => $c->created_at,
            'time_ago'       => $this->timeAgo($c->created_at),
            'likes'          => (int) ($c->likes ?? 0),
            'dislikes'       => (int) ($c->dislikes ?? 0),
            'user_reaction'  => $userReactions[$c->id] ?? null,
            'chapter_label'  => $chapterLabel,
            'chapter_url'    => $chapterUrl,
        ];
    }

    private function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
        if ($diff < 31536000) return floor($diff / 2592000) . 'mo ago';
        return floor($diff / 31536000) . 'y ago';
    }
}
