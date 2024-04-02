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
        $this->table = FLUENT_MAIL_DB_PREFIX . 'collections';
    }

    public function get($data)
    {
        $db = $this->getDb();

        $query = $db->table($this->table)
            ->orderBy('id', 'DESC');

        if (isset($data['user_id'])) {
            $query->where('user_id', '=', $data['user_id']);
        }

        $result = $query->get();
        $result = $this->formatResult($result);

        return ['data' => $result];
    }

    protected function formatResult($result)
    {
        if (is_array($result)) {
            $result = $result;
        } else {
            // convert stdclass object to array
            $result = json_decode(json_encode($result), true);
        }
        $temp = [];
        foreach ($result as $key => $row) {
            $temp[$key] = [
                'id' => $row->id,
                'name' => $row->name
            ];
        }

        return $temp;
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
