<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CrawlCommand extends BaseCommand
{
    protected $group       = 'Crawl';
    protected $name        = 'crawl';
    protected $description = 'Run crawl tasks';
    protected $usage       = 'crawl [index|chapter|chapter2|mangadistrict|resetday|resetmonth] [--url=...]';

    public function run(array $params)
    {
        $action = $params[0] ?? 'index';

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
            case 'resetday':
                $controller->resetDay();
                break;
            case 'resetmonth':
                $controller->resetMonth();
                break;
            default:
                CLI::error("Unknown action: {$action}");
                CLI::write('Available: index, chapter, chapter2, mangadistrict, resetday, resetmonth');
        }
    }
}
