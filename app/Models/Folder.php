<?php

namespace FluentMail\App\Models;

use Exception;
use FluentMail\Includes\Support\Arr;

class Folder extends Model
{
    private $table = null;
    protected $fillables = [
        'to',
        'from',
        'subject',
        'body',
        'status',
        'response',
        'extra',
        'created_at'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = FLUENT_MAIL_DB_PREFIX . 'folders';
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
        $result['data'] = $this->formatResult($result);

        return $result['data'];
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

    public function delete(array $id)
    {
        if ($id && $id[0] == 'all') {
            return $this->db->query("TRUNCATE TABLE {$this->table}");
        }

        $ids = array_filter($id, 'intval');

        if ($ids) {
            return $this->getDb()->table(FLUENT_MAIL_DB_PREFIX . 'email_logs')
                ->whereIn('id', $ids)
                ->delete();
        }

        return false;
    }
}
