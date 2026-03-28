<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Manga extends BaseConfig
{
    /**
     * CDN URL for manga images (no trailing slash)
     */
    public string $cdnUrl = 'https://s1.manga18.club';

    /**
     * Local save path for manga images
     */
    public string $savePath = '/var/www/manga18/manga/';

    public function __construct()
    {
        parent::__construct();
        $this->cdnUrl = env('MANGA_CDN_URL', $this->cdnUrl);
        $this->savePath = env('MANGA_SAVE_PATH', $this->savePath);
    }

    /**
     * Build full image URL for a page
     * Pattern: {cdnUrl}/manga/{manga_slug}/chapters/{chapter_slug}/{image}
     */
    public function getImageUrl(string $mangaSlug, string $chapterSlug, string $image, bool $external = false): string
    {
        if ($external) {
            return $image;
        }
        return $this->cdnUrl . '/manga/' . $mangaSlug . '/chapters/' . $chapterSlug . '/' . $image;
    }

    /**
     * Build local save path for a chapter
     */
    public function getSavePath(string $mangaSlug, string $chapterSlug): string
    {
        return $this->savePath . $mangaSlug . '/chapters/' . $chapterSlug . '/';
    }
}
