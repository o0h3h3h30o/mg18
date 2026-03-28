<?php

namespace App\Models;

use CodeIgniter\Model;

class MangaModel extends Model
{
    protected $table = 'manga';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'slug', 'otherNames', 'summary', 'cover', 'hot', 'caution',
        'views', 'rate', 'type_id', 'status_id', 'user_id', 'is_new', 'is_public',
        'new_slug', 'genres', 'chapter_1', 'chap_1_slug', 'time_chap_1',
        'chapter_2', 'chap_2_slug', 'time_chap_2', 'create_at', 'update_at',
        'view_day', 'view_month', 'from_manga18fx', 'is_crawling',
        '_authors', '_artists', 'flag_chap_1', 'flag_chap_2', 'rating', 'releaseDate',
    ];
    protected $returnType = 'object';
}
