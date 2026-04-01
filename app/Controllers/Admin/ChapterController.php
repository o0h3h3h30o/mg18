<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChapterModel;

class ChapterController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ChapterModel();
    }

    public function index($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        $chapters = $this->db->table('chapter')
            ->where('manga_id', $mangaId)
            ->orderBy('CAST(`number` AS DECIMAL(10,2)) DESC', '', false)
            ->get()->getResult();

        $data = [
            'title'    => 'Chapters - ' . $manga->name,
            'manga'    => $manga,
            'chapters' => $chapters,
        ];
        return view('admin/chapter/index', $data);
    }

    public function create($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        // Suggest next chapter number
        $lastNumber = $this->db->table('chapter')
            ->where('manga_id', $mangaId)
            ->orderBy('CAST(`number` AS DECIMAL(10,2)) DESC', '', false)
            ->limit(1)->get()->getRow();
        $nextNumber = $lastNumber ? (float)$lastNumber->number + 1 : 1;

        $data = [
            'title' => 'Create Chapter - ' . $manga->name,
            'item'  => null,
            'manga' => $manga,
            'pages' => [],
            'nextNumber' => $nextNumber,
        ];
        return view('admin/chapter/form', $data);
    }

    public function store($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        if (!$this->validate([
            'name'   => 'required|max_length[500]',
            'number' => 'required|max_length[20]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $number = trim($this->request->getPost('number'));
        $name = trim($this->request->getPost('name'));
        $slug = trim($this->request->getPost('slug')) ?: url_title($name ?: 'chapter-' . $number, '-', true);

        // Check duplicate number
        $existNumber = $this->db->table('chapter')->where('manga_id', $mangaId)->where('number', $number)->get()->getRow();
        if ($existNumber) {
            return redirect()->back()->withInput()->with('error', 'Chapter number "' . $number . '" đã tồn tại.');
        }

        // Check duplicate slug
        $existSlug = $this->db->table('chapter')->where('manga_id', $mangaId)->where('slug', $slug)->get()->getRow();
        if ($existSlug) {
            return redirect()->back()->withInput()->with('error', 'Slug "' . $slug . '" đã tồn tại.');
        }

        $id = $this->model->insert([
            'manga_id'    => $mangaId,
            'name'        => $name,
            'number'      => $number,
            'slug'        => $slug,
            'is_show'     => (int) $this->request->getPost('is_show'),
            'need_login'  => (int) $this->request->getPost('need_login'),
            'source_url'  => trim($this->request->getPost('source_url') ?? ''),
            'is_crawling' => (int) $this->request->getPost('is_crawling'),
            'view'        => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Update manga latest chapter info
        $this->db->table('manga')->where('id', $mangaId)->update([
            'chapter_1'   => $name ?: 'Chapter ' . $number,
            'chap_1_slug' => $slug,
            'time_chap_1' => date('Y-m-d H:i:s'),
            'update_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/chapters/edit/' . $id)->with('success', 'Chapter created. Now add pages/images below.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga');

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();

        $pages = $this->db->table('page')
            ->where('chapter_id', $id)
            ->orderBy('slug', 'ASC')
            ->get()->getResult();

        $data = [
            'title' => 'Edit Chapter',
            'item'  => $item,
            'manga' => $manga,
            'pages' => $pages,
        ];
        return view('admin/chapter/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'name'        => 'required|max_length[500]',
            'number'      => 'required|max_length[20]',
            'slug'        => 'required|max_length[500]',
            'is_show'     => 'permit_empty|in_list[0,1]',
            'need_login'  => 'permit_empty|in_list[0,1]',
            'source_url'  => 'permit_empty|max_length[256]',
            'is_crawling' => 'permit_empty|in_list[0,1,2]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->model->update($id, [
            'name'        => trim($this->request->getPost('name')),
            'number'      => trim($this->request->getPost('number')),
            'slug'        => trim($this->request->getPost('slug')),
            'is_show'     => (int) $this->request->getPost('is_show'),
            'need_login'  => (int) $this->request->getPost('need_login'),
            'source_url'  => trim($this->request->getPost('source_url') ?? ''),
            'is_crawling' => (int) $this->request->getPost('is_crawling'),
        ]);

        $item = $this->model->find($id);
        return redirect()->to('/admin/chapters/' . $item->manga_id)->with('success', 'Chapter updated.');
    }

    public function recrawl($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga')->with('error', 'Chapter not found.');

        if (empty($item->source_url)) {
            return redirect()->to('/admin/chapters/edit/' . $id)->with('error', 'No source_url set. Please enter a source URL first.');
        }

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();

        try {
            // Delete existing pages from DB
            $this->db->table('page')->where('chapter_id', $id)->delete();

            // Delete chapter folder on disk
            if ($manga) {
                $chapterDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $item->slug;
                if (is_dir($chapterDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($chapterDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $f) {
                        $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
                    }
                    rmdir($chapterDir);
                }
            }

            // Reset chapter to be picked up by crawler
            $this->model->update($id, [
                'is_show'     => 0,
                'is_crawling' => 0,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Re-crawl chapter failed: ' . $e->getMessage());
            return redirect()->to('/admin/chapters/edit/' . $id)->with('error', 'Re-crawl failed: ' . $e->getMessage());
        }

        return redirect()->to('/admin/chapters/edit/' . $id)->with('success', 'Chapter queued for re-crawl. Pages deleted, is_show=0, is_crawling=0. Crawler will pick it up.');
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga')->with('error', 'Chapter not found.');

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();
        $mangaId = $item->manga_id;

        try {
            // Delete related comments
            $this->db->table('comments')->where('post_type', 'chapter')->where('post_id', $id)->delete();

            // Delete pages from DB
            $this->db->table('page')->where('chapter_id', $id)->delete();

            // Delete chapter from DB
            $this->model->delete($id);

            // Delete chapter folder on disk
            if ($manga) {
                $chapterDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $item->slug;
                if (is_dir($chapterDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($chapterDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $f) {
                        $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
                    }
                    rmdir($chapterDir);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Delete chapter failed: ' . $e->getMessage());
            return redirect()->to('/admin/chapters/' . $mangaId)->with('error', 'Delete failed: ' . $e->getMessage());
        }

        return redirect()->to('/admin/chapters/' . $mangaId)->with('success', 'Chapter deleted.');
    }
}
