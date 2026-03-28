<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table = 'status';
    protected $primaryKey = 'id';
    protected $allowedFields = ['label', 'created_at', 'updated_at'];
    protected $returnType = 'object';
}
