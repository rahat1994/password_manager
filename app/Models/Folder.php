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

        $result = $query->get();
        $result['data'] = $this->formatResult($result['data']);

        return $result;
    }

    protected function formatResult($result)
    {
        $result = is_array($result) ? $result : func_get_args();

        foreach ($result as $key => $row) {
            $result[$key]            = array_map('maybe_unserialize', (array) $row);
            $result[$key]['id']      = (int)$result[$key]['id'];
            $result[$key]['name'] = (int)$result[$key]['name'];
        }

        return $result;
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
