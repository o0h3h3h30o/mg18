<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'show_home', 'created_at', 'updated_at'];
    protected $returnType = 'object';
}
