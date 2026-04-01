<?php

namespace App\Controllers;

class Lasted extends BaseController
{
    public function index($page = null)
    {
        $page = (int)($page ?? 1);
        if ($page <= 1) {
            return redirect()->to('/');
        }

        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(1);

        // Batch SEO options in 1 query
        $seoOptions = $this->getOptionsByKeys(['seo.title', 'seo.description', 'seo.keywords']);
        $data['heading_title'] = $seoOptions['seo.title'] ?? '';
        $data['seo_title'] = $seoOptions['seo.title'] ?? '';
        $data['seo_description'] = $seoOptions['seo.description'] ?? '';
        $data['seo_keyword'] = $seoOptions['seo.keywords'] ?? '';

        $data['top_day'] = $this->getTopDay();
        $data['top_month'] = $this->getTopMonth();

        $page = (int)($page ?? 0);
        $offset = max(($page * 32) - 32, 0);

        // Use SQL_CALC_FOUND_ROWS to get total + results in 1 round trip
        $listChapters = $this->db->query(
            'SELECT SQL_CALC_FOUND_ROWS m.id as manga_id, m.is_new, m.slug as manga_slug,
             m.cover as manga_cover, m.name as manga_name, m.hot,
             m.chapter_1, m.chapter_2, m.chap_1_slug, m.chap_2_slug,
             m.time_chap_1, m.flag_chap_1, m.flag_chap_2, m.time_chap_2
             FROM manga m WHERE m.is_public = 1 ORDER BY update_at DESC LIMIT ?, 32',
            [$offset]
        )->getResult();
        $total = $this->db->query('SELECT FOUND_ROWS() as total')->getRow()->total;

        $data['listChapters'] = $listChapters;
        $data['bookmarks'] = [];
        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 32);
        $data['base_url'] = '/latest-release/';

        return view('lasted', $data);
    }
}
