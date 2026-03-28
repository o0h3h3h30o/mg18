<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        $status = $this->request->getGet('status') ?? 'pending';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('chapter_report r')
            ->select('r.*, m.name as manga_name, m.slug as manga_slug, c.number as chapter_number, c.slug as chapter_slug, u.username')
            ->join('manga m', 'm.id = r.manga_id', 'left')
            ->join('chapter c', 'c.id = r.chapter_id', 'left')
            ->join('users u', 'u.id = r.user_id', 'left');

        if ($status !== 'all') {
            $builder->where('r.status', $status);
        }

        $total = $builder->countAllResults(false);
        $reports = $builder->orderBy('r.created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()->getResult();

        // Count by status
        $counts = [
            'pending'  => $this->db->table('chapter_report')->where('status', 'pending')->countAllResults(),
            'resolved' => $this->db->table('chapter_report')->where('status', 'resolved')->countAllResults(),
            'dismissed' => $this->db->table('chapter_report')->where('status', 'dismissed')->countAllResults(),
        ];

        return view('admin/report/index', [
            'title'   => 'Chapter Reports',
            'reports' => $reports,
            'status'  => $status,
            'counts'  => $counts,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
        ]);
    }

    public function resolve($id)
    {
        // Get report info before updating
        $report = $this->db->table('chapter_report')->where('id', $id)->get()->getRow();

        $this->db->table('chapter_report')->where('id', $id)->update([
            'status'      => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify the reporter if they were logged in
        if ($report && !empty($report->user_id)) {
            // Get manga/chapter info for the link
            $chapter = $this->db->table('chapter')
                ->select('chapter.slug as chapter_slug, chapter.number, manga.id as manga_id, manga.slug as manga_slug, manga.name as manga_name')
                ->join('manga', 'manga.id = chapter.manga_id')
                ->where('chapter.id', $report->chapter_id)
                ->get()->getRow();

            $link = '/';
            $chapterLabel = 'Chapter';
            if ($chapter) {
                $link = '/manhwa/' . $chapter->manga_slug . '/' . $chapter->chapter_slug;
                $chapterLabel = $chapter->manga_name . ' - Chapter ' . $chapter->number;
            }

            $this->db->table('notifications')->insert([
                'user_id'      => $report->user_id,
                'actor_id'     => (int) $this->user_info->id,
                'type'         => 'report_resolved',
                'manga_id'     => $chapter ? (int)$report->manga_id : null,
                'manga_slug'   => $chapter ? $chapter->manga_slug : null,
                'manga_name'   => $chapter ? $chapter->manga_name : null,
                'chapter_slug' => $chapter ? $chapter->chapter_slug : '',
                'preview'      => $chapterLabel . ' issue has been fixed. Thank you!',
                'is_read'      => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Report resolved.');
    }

    public function dismiss($id)
    {
        $this->db->table('chapter_report')->where('id', $id)->update([
            'status'      => 'dismissed',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('success', 'Report dismissed.');
    }

    public function delete($id)
    {
        $this->db->table('chapter_report')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Report deleted.');
    }

    public function bulkResolve()
    {
        $ids = $this->request->getPost('report_ids');
        if (!empty($ids) && is_array($ids)) {
            $this->db->table('chapter_report')
                ->whereIn('id', array_map('intval', $ids))
                ->update(['status' => 'resolved', 'resolved_at' => date('Y-m-d H:i:s')]);
            return redirect()->back()->with('success', count($ids) . ' reports resolved.');
        }
        return redirect()->back()->with('error', 'No reports selected.');
    }

    public function bulkDismiss()
    {
        $ids = $this->request->getPost('report_ids');
        if (!empty($ids) && is_array($ids)) {
            $this->db->table('chapter_report')
                ->whereIn('id', array_map('intval', $ids))
                ->update(['status' => 'dismissed', 'resolved_at' => date('Y-m-d H:i:s')]);
            return redirect()->back()->with('success', count($ids) . ' reports dismissed.');
        }
        return redirect()->back()->with('error', 'No reports selected.');
    }
}
