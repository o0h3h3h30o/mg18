<?php

if (!function_exists('getDateTime')) {
    function getDateTime($time)
    {
        return date('M d, y', $time);
    }
}

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('time_elapsed_string_2')) {
    function time_elapsed_string_2($datetime, $full = false)
    {
        $data_time = date('Y-m-d H:i:s', $datetime);
        $now = new DateTime();
        $ago = new DateTime($data_time);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('obfuscate_email')) {
    function obfuscate_email($email)
    {
        $em = explode('@', $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = (int)floor(strlen($name) / 2);
        return substr($name, 0, $len) . str_repeat('*', $len) . '@' . end($em);
    }
}

if (!function_exists('convert_avif_to_jpeg')) {
    function convert_avif_to_jpeg(string $filePath): string
    {
        $mime = @mime_content_type($filePath);
        if ($mime !== 'image/avif') return $filePath;

        $jpegPath = $filePath . '.jpg';

        if (function_exists('imagecreatefromavif')) {
            $img = @imagecreatefromavif($filePath);
            if ($img) {
                imagejpeg($img, $jpegPath, 90);
                imagedestroy($img);
                @unlink($filePath);
                return $jpegPath;
            }
        }

        if (class_exists('Imagick')) {
            try {
                $im = new \Imagick($filePath . '[0]');
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(90);
                $im->writeImage($jpegPath);
                $im->destroy();
                @unlink($filePath);
                return $jpegPath;
            } catch (\Exception $e) {}
        }

        $magick = trim(shell_exec('which magick 2>/dev/null') ?: shell_exec('which convert 2>/dev/null') ?: '');
        if ($magick) {
            $cmd = escapeshellarg($magick) . ' ' . escapeshellarg($filePath) . '[0] -quality 90 ' . escapeshellarg($jpegPath) . ' 2>&1';
            exec($cmd, $out, $ret);
            log_message('debug', "AVIF convert magick: cmd={$cmd} ret={$ret} out=" . implode(' ', $out));
            if ($ret === 0 && file_exists($jpegPath)) {
                @unlink($filePath);
                return $jpegPath;
            }
        }

        $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '');
        if ($ffmpeg) {
            $cmd = escapeshellarg($ffmpeg) . ' -i ' . escapeshellarg($filePath) . ' -frames:v 1 -q:v 2 ' . escapeshellarg($jpegPath) . ' -y 2>&1';
            exec($cmd, $out, $ret);
            log_message('debug', "AVIF convert ffmpeg: cmd={$cmd} ret={$ret} out=" . implode(' ', $out));
            if ($ret === 0 && file_exists($jpegPath)) {
                @unlink($filePath);
                return $jpegPath;
            }
        }

        log_message('error', "AVIF convert failed for {$filePath}: no method worked (GD=" . (function_exists('imagecreatefromavif') ? 'yes' : 'no') . " Imagick=" . (class_exists('Imagick') ? 'yes' : 'no') . " magick={$magick} ffmpeg={$ffmpeg})");
        return $filePath;
    }
}

if (!function_exists('normalize_image_to_jpeg')) {
    /**
     * Force-convert any GD-readable image (avif/webp/png/gif/jpg) to JPEG
     * so the CI4 Image service (GDHandler) can handle it.
     *
     * Returns the JPEG path on success, empty string on failure
     * (caller should fall back to convert_avif_to_jpeg or skip).
     */
    function normalize_image_to_jpeg(string $filePath): string
    {
        $mime = @mime_content_type($filePath);
        // Already JPEG → just ensure .jpg extension so getimagesize works.
        if ($mime === 'image/jpeg') {
            if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'jpg') {
                $jpg = $filePath . '.jpg';
                @rename($filePath, $jpg);
                return file_exists($jpg) ? $jpg : $filePath;
            }
            return $filePath;
        }

        $jpegPath = $filePath . '.jpg';

        // 1) Pure-GD path: imagecreatefromstring handles whatever GD was built with.
        $bin = @file_get_contents($filePath);
        if ($bin !== false) {
            $img = @imagecreatefromstring($bin);
            if ($img) {
                // Flatten alpha to white background (avoids black backgrounds when
                // converting PNG/WebP transparency to JPEG).
                $w = imagesx($img); $h = imagesy($img);
                $flat = imagecreatetruecolor($w, $h);
                $white = imagecolorallocate($flat, 255, 255, 255);
                imagefilledrectangle($flat, 0, 0, $w, $h, $white);
                imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);
                imagejpeg($flat, $jpegPath, 90);
                imagedestroy($img);
                imagedestroy($flat);
                if (file_exists($jpegPath) && filesize($jpegPath) > 0) {
                    @unlink($filePath);
                    return $jpegPath;
                }
            }
        }

        // 2) Imagick fallback
        if (class_exists('Imagick')) {
            try {
                $im = new \Imagick($filePath . '[0]');
                $im->setImageBackgroundColor('white');
                $im = $im->flattenImages();
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(90);
                $im->writeImage($jpegPath);
                $im->destroy();
                if (file_exists($jpegPath) && filesize($jpegPath) > 0) {
                    @unlink($filePath);
                    return $jpegPath;
                }
            } catch (\Exception $e) {}
        }

        log_message('error', "normalize_image_to_jpeg failed for {$filePath} (mime={$mime})");
        return '';
    }
}
