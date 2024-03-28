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

    protected $searchables = [
        'name',
        'username',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = FLUENT_MAIL_DB_PREFIX . 'items';
    }

    public function get($data)
    {
        $db = $this->getDb();
        $page    = isset($data['page']) ? (int)$data['page'] : 1;
        $perPage = isset($data['per_page']) ? (int)$data['per_page'] : 15;
        $offset  = ($page - 1) * $perPage;

        $query = $db->table($this->table)
            // ->innerJoin(
            //     FLUENT_MAIL_DB_PREFIX . 'folders',
            //     $this->table . '.folder_id',
            //     '=',
            //     FLUENT_MAIL_DB_PREFIX . 'folders.id'
            // )
            // ->innerJoin(
            //     FLUENT_MAIL_DB_PREFIX . 'organizations',
            //     $this->table . '.organization_id',
            //     '=',
            //     FLUENT_MAIL_DB_PREFIX . 'organizations.id'
            // )
            // ->select(
            //     FLUENT_MAIL_DB_PREFIX . 'items.*',
            //     FLUENT_MAIL_DB_PREFIX . 'folders.name as folder_name',
            //     FLUENT_MAIL_DB_PREFIX . 'organizations.name as organization_name'
            // )
            ->limit($perPage)
            ->offset($offset)
            ->orderBy(FLUENT_MAIL_DB_PREFIX . 'items.id', 'DESC');

        if (!empty($data['status'])) {
            $query->where('status', sanitize_text_field($data['status']));
        }

        if (!empty($data['search'])) {
            $search = trim(sanitize_text_field($data['search']));
            $query->where(function ($q) use ($search) {
                $searchColumns = $this->searchables;

                $columnSearch = false;
                if (strpos($search, ':')) {
                    $searchArray = explode(':', $search);
                    $column = array_shift($searchArray);
                    if (in_array($column, $this->fillables)) {
                        $columnSearch = true;
                        $q->where($column, 'LIKE', '%' . trim(implode(':', $searchArray)) . '%');
                    }
                }

                if (!$columnSearch) {
                    $firstColumn = array_shift($searchColumns);
                    $q->where($firstColumn, 'LIKE', '%' . $search . '%');
                    foreach ($searchColumns as $column) {
                        $q->orWhere($column, 'LIKE', '%' . $search . '%');
                    }
                }
            });
        }
        if (isset($data['user_id'])) {
            $query->where('user_id', '=', $data['user_id']);
        }

        // echo "<pre>";
        // print_r($data);
        $result = $query->paginate();
        $result['data'] = $this->formatResult($result['data']);

        return $result;
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

        // {
        //     id: i,
        //     name: `Rahat Holland ${i}`,
        //     username: "myusername",
        //     password: "mypassword",
        //     organisation:{
        //         name: "Staff Asia",
        //         id: 1
        //     }

        // }
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
                'login_url' => $row->login_url
            ];
        }

        // foreach ($result as $key => $row) {
        //     $temp[$key] = [
        //         'id' => $row->id,
        //         'name' => $row->name,
        //         'username' => $row->username,
        //         'password' => $row->password,
        //         'organisation' => [
        //             'name' => "Staff Asia",
        //             'id' => $row->organization_id
        //         ],
        //         'master_pass_secured' => $row->master_pass_secured,
        //         'folder' => [
        //             'name' => "Staff Asia",
        //             'id' => $row->folder_id
        //         ],
        //     ];
        // }

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

    public function update($id, $data)
    {
        try {
            return $this->getDb()->table($this->table)
                ->where('id', $id)
                ->update($data);
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
