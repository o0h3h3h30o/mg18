<?php

namespace App\Models;

use CodeIgniter\Model;

class ComicTypeModel extends Model
{
    protected $table = 'comictype';
    protected $primaryKey = 'id';
    protected $allowedFields = ['label', 'created_at', 'updated_at'];
    protected $returnType = 'object';
}
