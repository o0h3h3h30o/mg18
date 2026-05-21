<?php

namespace App\Traits;

/**
 * Merge a banner image into the top and/or bottom of a page image.
 * Banner is scaled to match the page width (aspect-preserving).
 *
 * Banner file lookup order:
 *   public/1.jpg → public/img/banner.png → public/img/banner.jpg
 *
 * Used by:
 *   - Admin\PageController (ZIP upload)
 *   - Crawl (after chapter crawl completes)
 */
trait BannerMerger
{
    /**
     * Merge banner into image. Reads source from $srcPath, writes to $destPath.
     * If $top is true, banner is prepended above the original.
     * If $bottom is true, banner is appended below.
     * Returns true on success.
     */
    protected function mergeBanner(string $srcPath, string $destPath, string $mime, bool $top, bool $bottom): bool
    {
        if (!$top && !$bottom) return false;

        // Check image size — skip if too large to avoid memory exhaustion
        $srcInfo = @getimagesize($srcPath);
        if ($srcInfo) {
            $estimatedMem = $srcInfo[0] * $srcInfo[1] * 4 * 3; // src + banner + final
            $memLimit = (int) ini_get('memory_limit') * 1024 * 1024;
            $memAvail = $memLimit - memory_get_usage(true);
            if ($estimatedMem > $memAvail * 0.7) {
                log_message('warning', "BannerMerger: skip {$srcPath} — too large ({$srcInfo[0]}x{$srcInfo[1]}, need ~" . round($estimatedMem/1024/1024) . "MB)");
                return false;
            }
        }

        $candidates = [
            FCPATH . '1.jpg',
            FCPATH . 'img/banner.png',
            FCPATH . 'img/banner.jpg',
        ];
        $bannerFile = null;
        foreach ($candidates as $c) {
            if (is_file($c)) { $bannerFile = $c; break; }
        }
        if (!$bannerFile) return false;

        switch ($mime) {
            case 'image/jpeg': $src = @imagecreatefromjpeg($srcPath); break;
            case 'image/png':  $src = @imagecreatefrompng($srcPath); break;
            case 'image/webp': $src = @imagecreatefromwebp($srcPath); break;
            case 'image/gif':  $src = @imagecreatefromgif($srcPath); break;
            default: return false;
        }
        if (!$src) return false;

        $bannerInfo = @getimagesize($bannerFile);
        if (!$bannerInfo) { imagedestroy($src); return false; }
        switch ($bannerInfo[2]) {
            case IMAGETYPE_PNG:  $banner = @imagecreatefrompng($bannerFile); break;
            case IMAGETYPE_JPEG: $banner = @imagecreatefromjpeg($bannerFile); break;
            default: imagedestroy($src); return false;
        }
        if (!$banner) { imagedestroy($src); return false; }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $bW = imagesx($banner);
        $bH = imagesy($banner);

        $scaledH = (int) round($bH * ($srcW / $bW));
        $scaledBanner = imagecreatetruecolor($srcW, $scaledH);
        $white = imagecolorallocate($scaledBanner, 255, 255, 255);
        imagefilledrectangle($scaledBanner, 0, 0, $srcW, $scaledH, $white);
        imagecopyresampled($scaledBanner, $banner, 0, 0, 0, 0, $srcW, $scaledH, $bW, $bH);
        imagedestroy($banner);

        $topH    = $top    ? $scaledH : 0;
        $bottomH = $bottom ? $scaledH : 0;
        $finalH  = $topH + $srcH + $bottomH;
        $final = imagecreatetruecolor($srcW, $finalH);
        $whiteFinal = imagecolorallocate($final, 255, 255, 255);
        imagefilledrectangle($final, 0, 0, $srcW, $finalH, $whiteFinal);

        $y = 0;
        if ($top) {
            imagecopy($final, $scaledBanner, 0, $y, 0, 0, $srcW, $scaledH);
            $y += $scaledH;
        }
        imagecopy($final, $src, 0, $y, 0, 0, $srcW, $srcH);
        $y += $srcH;
        if ($bottom) {
            imagecopy($final, $scaledBanner, 0, $y, 0, 0, $srcW, $scaledH);
        }
        imagedestroy($src);
        imagedestroy($scaledBanner);

        $ok = false;
        switch ($mime) {
            case 'image/jpeg': $ok = imagejpeg($final, $destPath, 90); break;
            case 'image/png':  $ok = imagepng($final, $destPath, 6); break;
            case 'image/webp': $ok = imagewebp($final, $destPath, 90); break;
            case 'image/gif':  $ok = imagegif($final, $destPath); break;
        }
        imagedestroy($final);
        return $ok;
    }

    /**
     * Apply banner to the explicit first/last file paths.
     * Caller passes an ordered array of saved page paths (in display order).
     * Banner is added to the top of the first and bottom of the last file.
     *
     * Use this when you control the order of pages (e.g. crawler index loop)
     * so we don't depend on directory scanning + alphabetical sort, which
     * breaks with mixed extensions / leftover files from previous crawls.
     */
    protected function applyBannerToFiles(array $orderedPaths): void
    {
        $orderedPaths = array_values(array_filter($orderedPaths, 'is_file'));
        if (empty($orderedPaths)) return;

        $first = $orderedPaths[0];
        $last  = end($orderedPaths);
        $isSame = ($first === $last);

        $firstMime = mime_content_type($first);
        $lastMime  = mime_content_type($last);

        if ($isSame) {
            $this->mergeBanner($first, $first, $firstMime, true, true);
        } else {
            $this->mergeBanner($first, $first, $firstMime, true, false);
            $this->mergeBanner($last,  $last,  $lastMime,  false, true);
        }
    }
}
