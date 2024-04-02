<?php

namespace FluentMail\App\Models;

use Exception;
use FluentMail\Includes\Support\Arr;

class Collection extends Model
{
    private $table = null;
    protected $fillables = [
        'name',
        'user_id',
        'organization_id',
        'created_at'
    ];

    public function __construct()
    {
        parent::__construct();
        // $this->table = FLUENT_MAIL_DB_PREFIX . 'collections';
    }

    public function add($data)
    {
        try {
            $data = array_merge($data, [
                'created_at' => current_time('mysql')
            ]);

            return $this->getDb()->table($this->table)
                ->insert($data);
        } catch (Exception $e) {
            return $e;
        }
    }
}
