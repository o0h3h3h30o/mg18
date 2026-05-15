<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Traits\BannerMerger;

class PageController extends BaseController
{
    use BannerMerger;

    public function index($chapterId)
    {
        $chapter = $this->db->table('chapter')->where('id', $chapterId)->get()->getRow();
        if (!$chapter) return redirect()->to('/admin/manga');

        $manga = $this->db->table('manga')->where('id', $chapter->manga_id)->get()->getRow();

        $pages = $this->db->table('page')
            ->where('chapter_id', $chapterId)
            ->orderBy('slug', 'ASC')
            ->get()->getResult();

        return view('admin/page/index', [
            'title'   => 'Pages - Chapter ' . ($chapter->number ?? $chapter->name),
            'chapter' => $chapter,
            'manga'   => $manga,
            'pages'   => $pages,
        ]);
    }

    /**
     * AJAX upload single image (local storage)
     */
    public function upload($chapterId)
    {
        $chapter = $this->db->table('chapter')->where('id', $chapterId)->get()->getRow();
        if (!$chapter) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Chapter not found']);
        }

        $manga = $this->db->table('manga')->where('id', $chapter->manga_id)->get()->getRow();

        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'No valid image uploaded.']);
        }

        $slug = trim($this->request->getPost('slug')) ?: $this->getNextSlug($chapterId);

        $uploadDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $chapter->slug;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate real image via MIME
        $mime = mime_content_type($file->getTempName());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Invalid image type: ' . $mime]);
        }

        $ext = $file->getExtension();
        $slug = basename($slug); // prevent path traversal
        $filename = $slug . '.' . $ext;
        $file->move($uploadDir, $filename);

        $this->db->table('page')->insert([
            'chapter_id' => $chapterId,
            'slug'       => $slug,
            'image'      => $filename,
            'external'   => 0,
        ]);

        $pageId = $this->db->insertID();
        $imgUrl = config('Manga')->cdnUrl . '/manga/' . $manga->slug . '/chapters/' . $chapter->slug . '/' . $filename;

        return $this->response->setJSON([
            'status'  => 1,
            'msg'     => 'Uploaded',
            'page_id' => $pageId,
            'slug'    => $slug,
            'image'   => $filename,
            'img_url' => $imgUrl,
        ]);
    }

    /**
     * Upload ZIP file, extract images (local storage)
     */
    public function uploadZip($chapterId)
    {
        $chapter = $this->db->table('chapter')->where('id', $chapterId)->get()->getRow();
        if (!$chapter) return redirect()->to('/admin/manga');

        $manga = $this->db->table('manga')->where('id', $chapter->manga_id)->get()->getRow();

        $file = $this->request->getFile('zipfile');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'No valid ZIP file uploaded.');
        }

        // Extract to temp dir
        $tmpDir = WRITEPATH . 'tmp/zip_' . time() . '_' . mt_rand(1000, 9999);
        mkdir($tmpDir, 0755, true);
        $zipPath = $tmpDir . '/upload.zip';
        $file->move($tmpDir, 'upload.zip');

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->cleanDir($tmpDir);
            return redirect()->back()->with('error', 'Failed to open ZIP file.');
        }
        // Check for path traversal in ZIP entries
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (str_contains($entryName, '..') || str_starts_with($entryName, '/')) {
                $zip->close();
                $this->cleanDir($tmpDir);
                return redirect()->back()->with('error', 'ZIP contains unsafe paths.');
            }
        }
        $zip->extractTo($tmpDir . '/extracted');
        $zip->close();

        // Find all image files recursively (verify MIME type)
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $images = [];
        $this->findImages($tmpDir . '/extracted', $allowedExts, $images);
        sort($images); // Sort by filename

        if (empty($images)) {
            $this->cleanDir($tmpDir);
            return redirect()->back()->with('error', 'No images found in ZIP file.');
        }

        // Destination directory
        $uploadDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $chapter->slug;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $nextSlug = $this->getNextSlug($chapterId);
        $batch = [];
        $savedPaths = [];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $appendBanner  = (int) $this->request->getPost('append_banner') === 1;
        $prependBanner = (int) $this->request->getPost('prepend_banner') === 1;

        foreach ($images as $imgPath) {
            // Verify real MIME type
            $fileMime = @mime_content_type($imgPath);
            if (!in_array($fileMime, $allowedMimes)) continue;

            $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
            $filename = $nextSlug . '.' . $ext;
            $destPath = $uploadDir . '/' . $filename;

            copy($imgPath, $destPath);

            $batch[] = [
                'chapter_id' => $chapterId,
                'slug'       => (string) $nextSlug,
                'image'      => $filename,
                'external'   => 0,
            ];
            $savedPaths[] = $destPath;
            $nextSlug++;
        }

        // Apply banner using saved order (first/last by upload order, not alpha sort)
        if (!empty($savedPaths) && ($appendBanner || $prependBanner)) {
            $first = $savedPaths[0];
            $last  = end($savedPaths);
            $isSame = ($first === $last);
            $firstMime = mime_content_type($first);
            $lastMime  = mime_content_type($last);

            if ($isSame) {
                $this->mergeBanner($first, $first, $firstMime, $prependBanner, $appendBanner);
            } else {
                if ($prependBanner) $this->mergeBanner($first, $first, $firstMime, true, false);
                if ($appendBanner)  $this->mergeBanner($last,  $last,  $lastMime,  false, true);
            }
        }

        if ($batch) {
            $this->db->table('page')->insertBatch($batch);
        }

        // Cleanup temp
        $this->cleanDir($tmpDir);

        return redirect()->to('/admin/chapters/edit/' . $chapterId)->with('success', count($batch) . ' images extracted and uploaded from ZIP.');
    }

    /**
     * Bulk URL paste (external storage)
     */
    public function uploadBulk($chapterId)
    {
        $chapter = $this->db->table('chapter')->where('id', $chapterId)->get()->getRow();
        if (!$chapter) return redirect()->to('/admin/manga');

        $urls = trim($this->request->getPost('urls'));
        if (!$urls) {
            return redirect()->back()->with('error', 'No URLs provided.');
        }

        $lines = array_filter(array_map('trim', explode("\n", $urls)));
        if (empty($lines)) {
            return redirect()->back()->with('error', 'No valid URLs found.');
        }

        $nextSlug = $this->getNextSlug($chapterId);
        $batch = [];
        foreach ($lines as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
            $batch[] = [
                'chapter_id' => $chapterId,
                'slug'       => (string) $nextSlug,
                'image'      => $url,
                'external'   => 1,
            ];
            $nextSlug++;
        }

        if (empty($batch)) {
            return redirect()->back()->with('error', 'No valid URLs found.');
        }

        $this->db->table('page')->insertBatch($batch);

        return redirect()->to('/admin/chapters/edit/' . $chapterId)->with('success', count($batch) . ' external image URLs added.');
    }

    /**
     * Get next slug number for pages in a chapter
     */
    private function getNextSlug($chapterId): int
    {
        $max = $this->db->table('page')
            ->selectMax('CAST(slug AS UNSIGNED)', 'max_slug')
            ->where('chapter_id', $chapterId)
            ->get()->getRow();
        return ($max && $max->max_slug) ? (int)$max->max_slug + 1 : 1;
    }

    /**
     * Recursively find image files
     */
    private function findImages(string $dir, array $exts, array &$results)
    {
        if (!is_dir($dir)) return;
        $files = scandir($dir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . '/' . $f;
            if (is_dir($path)) {
                $this->findImages($path, $exts, $results);
            } else {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, $exts)) {
                    $results[] = $path;
                }
            }
        }
    }

    /**
     * Recursively remove directory
     */
    private function cleanDir(string $dir)
    {
        if (!is_dir($dir)) return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($dir);
    }

    public function edit($id)
    {
        $page = $this->db->table('page')->where('id', $id)->get()->getRow();
        if (!$page) return redirect()->to('/admin/manga');

        $chapter = $this->db->table('chapter')->where('id', $page->chapter_id)->get()->getRow();
        $manga = $this->db->table('manga')->where('id', $chapter->manga_id)->get()->getRow();

        $allPages = $this->db->table('page')
            ->where('chapter_id', $page->chapter_id)
            ->orderBy('slug', 'ASC')
            ->get()->getResult();

        return view('admin/page/form', [
            'title'    => 'Edit Page',
            'item'     => $page,
            'chapter'  => $chapter,
            'manga'    => $manga,
            'allPages' => $allPages,
        ]);
    }

    public function update($id)
    {
        $page = $this->db->table('page')->where('id', $id)->get()->getRow();
        if (!$page) return redirect()->to('/admin/manga');

        if (!$this->validate([
            'slug'  => 'required|max_length[500]',
            'image' => 'required|max_length[1000]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->db->table('page')->where('id', $id)->update([
            'slug'     => trim($this->request->getPost('slug')),
            'image'    => trim($this->request->getPost('image')),
            'external' => (int) $this->request->getPost('external'),
        ]);

        return redirect()->to('/admin/pages/' . $page->chapter_id)->with('success', 'Page updated.');
    }

    public function delete($id)
    {
        $page = $this->db->table('page')->where('id', $id)->get()->getRow();
        if (!$page) return redirect()->to('/admin/manga');

        $chapterId = $page->chapter_id;
        $this->db->table('page')->where('id', $id)->delete();

        // Redirect back to chapter edit if came from there
        $referer = $this->request->getHeaderLine('Referer');
        $parsedPath = parse_url($referer, PHP_URL_PATH) ?: '';
        if (str_contains($parsedPath, '/admin/chapters/edit/')) {
            return redirect()->to($parsedPath)->with('success', 'Page deleted.');
        }
        return redirect()->to('/admin/pages/' . $chapterId)->with('success', 'Page deleted.');
    }

    public function deleteAll($chapterId)
    {
        $chapter = $this->db->table('chapter')->where('id', $chapterId)->get()->getRow();
        if (!$chapter) return redirect()->to('/admin/manga');

        $count = $this->db->table('page')->where('chapter_id', $chapterId)->countAllResults(false);
        $this->db->table('page')->where('chapter_id', $chapterId)->delete();

        return redirect()->to('/admin/chapters/edit/' . $chapterId)->with('success', "All {$count} pages deleted.");
    }

    /**
     * AJAX: Download external image to local storage
     */
    public function downloadExternal($pageId)
    {
        $page = $this->db->table('page')->where('id', $pageId)->get()->getRow();
        if (!$page || !$page->external) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Page not found or not external']);
        }

        $chapter = $this->db->table('chapter')->where('id', $page->chapter_id)->get()->getRow();
        $manga = $this->db->table('manga')->where('id', $chapter->manga_id)->get()->getRow();

        $url = $page->image;

        // Percent-encode any non-ASCII bytes in path/query so URLs containing
        // raw Korean/Japanese/Vietnamese characters are valid HTTP request lines.
        // Already-encoded sequences (%XX) are preserved.
        $url = $this->encodeUrlAscii($url);

        // Pick the proper referer. CDN image URLs (manread.xyz,
        // cdn.mangadistrict.com, etc.) usually require the original site as
        // referer — sending the CDN domain itself yields 403/placeholder.
        $referer = $this->resolveReferer($url, $chapter->source_url ?? '');

        // Download image with curl
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_REFERER        => $referer,
        ]);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!$imageData || $httpCode !== 200) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Download failed: ' . ($error ?: "HTTP {$httpCode}")]);
        }

        // Determine extension from content type or URL
        $ext = 'jpg';
        if (str_contains($contentType, 'png')) $ext = 'png';
        elseif (str_contains($contentType, 'webp')) $ext = 'webp';
        elseif (str_contains($contentType, 'gif')) $ext = 'gif';
        else {
            $urlExt = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (in_array($urlExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $ext = $urlExt === 'jpeg' ? 'jpg' : $urlExt;
            }
        }

        // Save to local
        $uploadDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $chapter->slug;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $page->slug . '.' . $ext;

        // Optimize if needed (same logic as crawler)
        $finalFilename = $this->saveAndOptimize($imageData, $uploadDir, $filename);

        // Update page record
        $this->db->table('page')->where('id', $pageId)->update([
            'image'    => $finalFilename,
            'external' => 0,
        ]);

        $imgUrl = config('Manga')->cdnUrl . '/manga/' . $manga->slug . '/chapters/' . $chapter->slug . '/' . $finalFilename;

        return $this->response->setJSON([
            'status'  => 1,
            'msg'     => 'Downloaded & saved locally',
            'img_url' => $imgUrl,
            'image'   => $finalFilename,
        ]);
    }

    /**
     * Save image data with optional optimization
     */
    private function saveAndOptimize(string $imageData, string $dir, string $filename): string
    {
        $maxWidth    = 720;
        $targetBytes = 1024 * 1024; // 1MB
        $size = strlen($imageData);
        $filePath = rtrim($dir, '/') . '/' . $filename;

        // Under 300KB: save as-is
        if ($size < 300 * 1024) {
            file_put_contents($filePath, $imageData);
            return $filename;
        }

        // Save original first
        file_put_contents($filePath, $imageData);

        $info = @getimagesizefromstring($imageData);
        if (!$info) return $filename;

        $origW = $info[0];
        $origH = $info[1];
        $estimatedMemory = $origW * $origH * 4 * 2;
        $memoryLimit = (int) ini_get('memory_limit') * 1024 * 1024;
        $memoryAvailable = $memoryLimit - memory_get_usage(true);
        if ($estimatedMemory > $memoryAvailable * 0.8) return $filename;

        $src = @imagecreatefromstring($imageData);
        if (!$src) return $filename;

        // Step 1: Resize if width > 720px
        $curW = $origW;
        $curH = $origH;
        if ($curW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($curH * ($maxWidth / $curW));
            $dst = imagecreatetruecolor($newW, $newH);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $curW, $curH);
            imagedestroy($src);
            $src = $dst;
            $curW = $newW;
            $curH = $newH;
        }

        // Step 2: Save as WebP, try decreasing quality until under target
        $webpName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $webpPath = rtrim($dir, '/') . '/' . $webpName;

        foreach ([85, 75, 65, 50] as $quality) {
            imagewebp($src, $webpPath, $quality);
            clearstatcache(true, $webpPath);
            if (filesize($webpPath) <= $targetBytes) break;
        }

        // Step 3: Still over 1MB after q50 — progressively shrink resolution
        clearstatcache(true, $webpPath);
        if (filesize($webpPath) > $targetBytes) {
            foreach ([0.75, 0.5, 0.35] as $scale) {
                $shrinkW = (int) round($curW * $scale);
                $shrinkH = (int) round($curH * $scale);
                if ($shrinkW < 360) break;

                $shrunk = imagecreatetruecolor($shrinkW, $shrinkH);
                imagealphablending($shrunk, false);
                imagesavealpha($shrunk, true);
                imagecopyresampled($shrunk, $src, 0, 0, 0, 0, $shrinkW, $shrinkH, $curW, $curH);
                imagewebp($shrunk, $webpPath, 50);
                imagedestroy($shrunk);
                clearstatcache(true, $webpPath);
                if (filesize($webpPath) <= $targetBytes) break;
            }
        }

        imagedestroy($src);

        clearstatcache(true, $webpPath);
        $newSize = filesize($webpPath);
        if ($newSize < $size) {
            @unlink($filePath);
            return $webpName;
        }

        @unlink($webpPath);
        return $filename;
    }

    public function deleteBatch()
    {
        $pageIds = $this->request->getPost('page_ids');
        $chapterId = $this->request->getPost('chapter_id');

        if (!empty($pageIds) && is_array($pageIds)) {
            $this->db->table('page')->whereIn('id', array_map('intval', $pageIds))->delete();
            $count = count($pageIds);
            return redirect()->to('/admin/chapters/edit/' . $chapterId)->with('success', "{$count} pages deleted.");
        }

        return redirect()->to('/admin/chapters/edit/' . $chapterId)->with('error', 'No pages selected.');
    }

    /**
     * Pick the correct Referer header for an image URL.
     * CDN image hosts (manread.xyz, cdn.mangadistrict.com…) require the
     * original site as referer — sending the CDN itself returns 403 or a
     * placeholder. We map by:
     *   1. CDN/image-URL substring (most reliable)
     *   2. The chapter's source_url host as a fallback
     *   3. The image URL's own host (last resort)
     */
    private function resolveReferer(string $imgUrl, string $sourceUrl = ''): string
    {
        $hay = $imgUrl . '|' . $sourceUrl;
        if (str_contains($hay, 'manread.xyz') || str_contains($hay, 'manhwaread')) {
            return 'https://manhwaread.com/';
        }
        if (str_contains($hay, 'mangadistrict') || str_contains($imgUrl, 'cdn.mangadistrict')) {
            return 'https://mangadistrict.com/';
        }
        if (str_contains($hay, 'newtoki') || str_contains($hay, 'manatoki')) {
            return 'https://newtoki468.com/';
        }
        if (str_contains($hay, 'manga18fx')) {
            return 'https://manga18fx.com/';
        }
        if (str_contains($hay, 'toonflix')) {
            return 'https://toonflix.app/';
        }
        // Fallback: root of the image URL host
        $scheme = parse_url($imgUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($imgUrl, PHP_URL_HOST) ?: '';
        return $host ? "{$scheme}://{$host}/" : '';
    }

    /**
     * Normalize an image URL so the resulting string is a valid HTTP request
     * line. Steps:
     *   1. Decode HTML entities (&#039;, &amp;, &quot; ...) in case the URL
     *      came from raw HTML and was stored without entity-decode.
     *   2. Trim whitespace.
     *   3. Percent-encode bytes that aren't safe in a request URI:
     *      - All non-ASCII bytes (0x80-0xFF)  → Korean / Japanese / Vietnamese
     *      - SPACE (0x20)                     → would otherwise be interpreted
     *        as the URI/version separator
     *      - Apostrophe (0x27) and double-quote (0x22) → safe in RFC 3986 but
     *        a few CDNs / WAFs reject them; encoding is harmless
     *      - Already-encoded %XX sequences are preserved (% is in the keep set
     *        and the regex doesn't match it).
     */
    private function encodeUrlAscii(string $url): string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return preg_replace_callback(
            '/[\x80-\xff\x20\x22\x27]/',
            static fn($m) => rawurlencode($m[0]),
            $url
        );
    }
}
