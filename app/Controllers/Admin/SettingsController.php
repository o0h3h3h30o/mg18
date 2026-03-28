<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        $options = $this->db->table('options')->get()->getResult();
        $settings = [];
        foreach ($options as $opt) {
            $settings[$opt->key] = $opt->value;
        }

        $data = [
            'title'    => 'Settings',
            'settings' => $settings,
        ];

        return view('admin/settings/index', $data);
    }

    public function save()
    {
        $post = $this->request->getPost();
        unset($post['csrf_test_name'], $post[csrf_token()]);

        foreach ($post as $key => $value) {
            // Convert dot notation back: site__name => site.name
            $dbKey = str_replace('__', '.', $key);
            $value = trim($value);

            $exists = $this->db->table('options')->where('key', $dbKey)->countAllResults();
            if ($exists) {
                $this->db->table('options')->where('key', $dbKey)->update([
                    'value'      => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->table('options')->insert([
                    'key'        => $dbKey,
                    'value'      => $value,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to('/admin/settings')->with('success', 'Settings saved successfully.');
    }
}
