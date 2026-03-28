<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthorModel extends Model
{
    protected $table = 'author';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'created_at', 'updated_at'];
    protected $returnType = 'object';
}
