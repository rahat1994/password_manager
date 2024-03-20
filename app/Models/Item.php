<?php

namespace FluentMail\App\Models;

use Exception;
use FluentMail\Includes\Support\Arr;

class Item extends Model
{
    private $table = null;
    protected $fillables = [
        'name',
        'username',
        'password',
        'key',
        'note',
        'master_pass_secured',
        'is_favourite',
        'in_trash',
        'deleted',
        'organization_id',
        'folder_id',
        'collection_id',
        'user_id',
        'login_url'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = FLUENT_MAIL_DB_PREFIX . 'items';
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
                'name' => $row->name,
                'username' => $row->username,
                'password' => $row->password,
                'key' => $row->key,
                'note' => $row->note,
                'master_pass_secured' => $row->master_pass_secured,
                'is_favourite' => $row->is_favourite,
                'in_trash' => $row->in_trash,
                'deleted' => $row->deleted,
                'organization_id' => $row->organization_id,
                'folder_id' => $row->folder_id,
                'collection_id' => $row->collection_id,
                'user_id' => $row->user_id,
                'login_url' => $row->login_url
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
