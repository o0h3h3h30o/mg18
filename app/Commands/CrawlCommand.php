<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CrawlCommand extends BaseCommand
{
    protected $group       = 'Crawl';
    protected $name        = 'crawl';
    protected $description = 'Run crawl tasks. Usage: php spark crawl [action] [--dry]';
    protected $usage       = 'crawl [index|chapter|chapter2|mangadistrict] [--dry] [--url=...]';

    public function run(array $params)
    {
        $action = $params[0] ?? 'index';

        // Build a fake request so controller can work
        $controller = new \App\Controllers\Crawl();

        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'chapter':
                $controller->crawlChapter();
                break;
            case 'chapter2':
                $controller->crawlChapter2();
                break;
            case 'mangadistrict':
                $url = CLI::getOption('url') ?? '';
                if (!$url) {
                    CLI::error('Usage: php spark crawl mangadistrict --url=https://mangadistrict.com/series/xxx/');
                    return;
                }
                $_GET['url'] = $url;
                $controller->mangadistrict();
                break;
            default:
                CLI::error("Unknown action: {$action}");
                CLI::write('Available: index, chapter, chapter2, mangadistrict');
        }
    }
}
