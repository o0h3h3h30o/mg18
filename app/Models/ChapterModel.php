<?php

namespace App\Models;

use CodeIgniter\Model;

class ChapterModel extends Model
{
    protected $table = 'chapter';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'slug', 'name', 'number', 'volume', 'manga_id', 'user_id',
        'created_at', 'updated_at', 'view', 'is_show', 'is_scramble',
        'is_18comic', 'need_login', 'source_url', 'is_crawling', 'flag', 'imgs',
    ];
    protected $returnType = 'object';
}
