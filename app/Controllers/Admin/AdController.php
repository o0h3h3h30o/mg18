<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdController extends BaseController
{
    private array $pages = [
        1 => 'Home',
        2 => 'Manga Detail',
        3 => 'Chapter Detail',
    ];

    private array $positions = [
        'TOP_LARGE',
        'TOP_SQRE_1',
        'TOP_SQRE_2',
        'BOTTOM_LARGE',
        'BOTTOM_SQRE_1',
        'BOTTOM_SQRE_2',
        'RIGHT_WIDE_1',
        'RIGHT_SQRE_1',
        'RIGHT_SQRE_2',
        'RIGHT_WIDE_2',
        'LEFT_WIDE_1',
        'LEFT_WIDE_2',
    ];

    // ========== ADS CRUD ==========

    public function index()
    {
        $ads = $this->db->table('ad')->orderBy('id', 'ASC')->get()->getResult();

        return view('admin/ad/index', [
            'title' => 'Ads',
            'ads'   => $ads,
        ]);
    }

    public function create()
    {
        return view('admin/ad/form', [
            'title' => 'Create Ad',
            'item'  => null,
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'bloc_id' => 'required|max_length[255]',
            'code'    => 'required',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->db->table('ad')->insert([
            'bloc_id'    => trim($this->request->getPost('bloc_id')),
            'code'       => $this->request->getPost('code'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/ads')->with('success', 'Ad created.');
    }

    public function edit($id)
    {
        $item = $this->db->table('ad')->where('id', $id)->get()->getRow();
        if (!$item) return redirect()->to('/admin/ads');

        return view('admin/ad/form', [
            'title' => 'Edit Ad',
            'item'  => $item,
        ]);
    }

    public function update($id)
    {
        if (!$this->validate([
            'bloc_id' => 'required|max_length[255]',
            'code'    => 'required',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->db->table('ad')->where('id', $id)->update([
            'bloc_id'    => trim($this->request->getPost('bloc_id')),
            'code'       => $this->request->getPost('code'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/ads')->with('success', 'Ad updated.');
    }

    public function delete($id)
    {
        $this->db->table('ad_placement')->where('ad_id', $id)->delete();
        $this->db->table('ad')->where('id', $id)->delete();
        return redirect()->to('/admin/ads')->with('success', 'Ad deleted.');
    }

    // ========== PLACEMENTS ==========

    public function placements()
    {
        $placements = $this->db->query(
            'SELECT ap.*, a.bloc_id as ad_name FROM ad_placement ap LEFT JOIN ad a ON a.id = ap.ad_id ORDER BY ap.placement_id, ap.placement'
        )->getResult();

        $ads = $this->db->table('ad')->orderBy('bloc_id', 'ASC')->get()->getResult();

        return view('admin/ad/placements', [
            'title'      => 'Ad Placements',
            'placements' => $placements,
            'pages'      => $this->pages,
            'positions'  => $this->positions,
            'ads'        => $ads,
        ]);
    }

    public function placementCreate()
    {
        $ads = $this->db->table('ad')->orderBy('bloc_id', 'ASC')->get()->getResult();

        return view('admin/ad/placement_form', [
            'title'     => 'Add Placement',
            'item'      => null,
            'pages'     => $this->pages,
            'positions' => $this->positions,
            'ads'       => $ads,
        ]);
    }

    public function placementStore()
    {
        if (!$this->validate([
            'ad_id'        => 'required|integer',
            'placement_id' => 'required|in_list[1,2,3]',
            'placement'    => 'required|in_list[' . implode(',', $this->positions) . ']',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->db->table('ad_placement')->insert([
            'ad_id'        => (int) $this->request->getPost('ad_id'),
            'placement_id' => (int) $this->request->getPost('placement_id'),
            'placement'    => $this->request->getPost('placement'),
        ]);
        return redirect()->to('/admin/placements')->with('success', 'Placement added.');
    }

    public function placementEdit($id)
    {
        $item = $this->db->table('ad_placement')->where('id', $id)->get()->getRow();
        if (!$item) return redirect()->to('/admin/placements');

        $ads = $this->db->table('ad')->orderBy('bloc_id', 'ASC')->get()->getResult();

        return view('admin/ad/placement_form', [
            'title'     => 'Edit Placement',
            'item'      => $item,
            'pages'     => $this->pages,
            'positions' => $this->positions,
            'ads'       => $ads,
        ]);
    }

    public function placementUpdate($id)
    {
        if (!$this->validate([
            'ad_id'        => 'required|integer',
            'placement_id' => 'required|in_list[1,2,3]',
            'placement'    => 'required|in_list[' . implode(',', $this->positions) . ']',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->db->table('ad_placement')->where('id', $id)->update([
            'ad_id'        => (int) $this->request->getPost('ad_id'),
            'placement_id' => (int) $this->request->getPost('placement_id'),
            'placement'    => $this->request->getPost('placement'),
        ]);
        return redirect()->to('/admin/placements')->with('success', 'Placement updated.');
    }

    public function placementDelete($id)
    {
        $this->db->table('ad_placement')->where('id', $id)->delete();
        return redirect()->to('/admin/placements')->with('success', 'Placement deleted.');
    }

    public function saveAll()
    {
        $json = $this->request->getJSON(true);
        $placements = $json['placements'] ?? [];

        // Validate first - collect valid rows before touching DB
        $validRows = [];
        foreach ($placements as $p) {
            $placementId = (int)($p['placement_id'] ?? 0);
            $position    = $p['placement'] ?? '';
            $adId        = (int)($p['ad_id'] ?? 0);

            if ($placementId < 1 || $adId < 1 || !in_array($position, $this->positions)) {
                continue;
            }

            $validRows[] = [
                'ad_id'        => $adId,
                'placement_id' => $placementId,
                'placement'    => $position,
            ];
        }

        if (empty($validRows)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No valid placements to save. Existing data kept.']);
        }

        // Use transaction to prevent data loss
        $this->db->transStart();
        $this->db->table('ad_placement')->truncate();
        foreach ($validRows as $row) {
            $this->db->table('ad_placement')->insert($row);
        }
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Save failed, data rolled back.']);
        }

        return $this->response->setJSON(['status' => 'ok', 'message' => count($validRows) . ' placements saved.']);
    }
}
