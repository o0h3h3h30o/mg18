<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['comment', 'post_id', 'post_type', 'user_id', 'parent_comment', 'likes', 'dislikes', 'created_at', 'updated_at'];
    protected $returnType = 'object';
}
