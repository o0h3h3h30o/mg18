<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default
$routes->get('/', 'Home::index');
$routes->get('home2', 'Home::home2');

// Manga reading
$routes->get('manhwa/(:segment)/(:segment)', 'Manga::read/$1');
$routes->get('manhwa/(:segment)', 'Manga::show/$1');
$routes->get('manga/(:segment)/(:segment)/(:num)', 'Manga::doujin/$1/$2');
$routes->get('manga/(:segment)', 'Manga::show/$1');

// Manga listing & filtering
$routes->get('manga-list/(:segment)/(:segment)', 'Manga::filter/$1/$2');
$routes->get('manga-list/(:segment)', 'Manga::filter/$1');
$routes->get('list-manga', 'Manga::listManga');
$routes->get('list-manga/(:segment)', 'Manga::listManga/$1');

// Latest releases
$routes->get('latest-release', 'Lasted::index/1');
$routes->get('latest-release/(:segment)', 'Lasted::index/$1');

// Auth
$routes->get('login', 'Auth::index');
$routes->get('admin-login', 'Auth::autoLogin');
$routes->get('register', 'Auth::register');
$routes->post('checkLogin', 'Auth::check');
$routes->post('subcribe', 'Auth::subcribe');
$routes->get('logout', 'Logout::index');

// User
$routes->get('profile', 'User::index');
$routes->get('bookmarks', 'User::bookmarks');
$routes->get('bookmarks/(:num)', 'User::bookmarks/$1');
$routes->get('history', 'User::history');
$routes->get('notification', 'User::notification');
$routes->get('notification/(:num)', 'User::notification/$1');
$routes->get('notification/go/(:num)', 'Home::goNotification/$1');
$routes->get('profile/edit', 'User::editProfile');
$routes->post('profile/update', 'User::updateProfile');
$routes->get('changePass', 'User::changePassword');
$routes->post('changePass', 'User::updatePassword');

// API & AJAX
$routes->post('apiAddChapter', 'Manga::apiAddChapter');
$routes->post('api/upsert-manga', 'Manga::apiUpsertManga');
$routes->post('api/insert-chapter', 'Manga::apiInsertChapter');
// Alias routes without slash (in case proxy blocks /api/ paths)
$routes->post('apiUpsertManga', 'Manga::apiUpsertManga');
$routes->post('apiInsertChapter', 'Manga::apiInsertChapter');
$routes->get('api/chapters-need-crawl', 'Manga::apiChaptersNeedCrawl');
$routes->get('apiChaptersNeedCrawl', 'Manga::apiChaptersNeedCrawl');
$routes->get('api/manga-by-source', 'Manga::apiMangaBySource');
$routes->get('search', 'Home::search');
$routes->get('apisearch', 'Home::search2');
$routes->post('api/track-view', 'Manga::trackView');
$routes->get('notifications', 'Home::getNotification');
$routes->post('notifications/read', 'Home::markNotificationsRead');
$routes->post('item_rating', 'Manga::votes');
$routes->post('item_bookmark', 'Manga::bookmarks');
$routes->post('item_unbookmark', 'Manga::unbookmarks');
$routes->post('report-chapter', 'Manga::reportChapter');
$routes->get('captcha/report', 'Manga::reportCaptcha');

// Comments
$routes->get('api/comments', 'Comment::list');
$routes->post('api/comments', 'Comment::store');
$routes->post('api/comments/delete', 'Comment::delete');
$routes->post('api/comments/react', 'Comment::react');
$routes->get('api/comments/recent', 'Comment::recent');

// Feed/Sitemap
$routes->get('feed', 'Home::feed');
$routes->get('sitemap.xml', 'Sitemap::index');
$routes->get('sitemap-pages.xml', 'Sitemap::pages');
$routes->get('sitemap-manga.xml', 'Sitemap::manga');
$routes->get('sitemap-category.xml', 'Sitemap::category');
$routes->get('sitemap-chapter-(:num).xml', 'Sitemap::chapter/$1');

// Crawl
$routes->get('crawl', 'Crawl::index');
$routes->get('crawlChapter', 'Crawl::crawlChapter');
$routes->get('crawlChapter2', 'Crawl::crawlChapter2');
$routes->get('mangadistrict', 'Crawl::mangadistrict');

// CLI crawl routes
$routes->cli('crawl/index', 'Crawl::index');
$routes->cli('crawl/crawlChapter', 'Crawl::crawlChapter');
$routes->cli('crawl/crawlChapter2', 'Crawl::crawlChapter2');
$routes->cli('crawl/mangadistrict', 'Crawl::mangadistrict');
$routes->cli('crawl/resetView', 'Crawl::resetView');

// Admin panel
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');

    // Categories
    $routes->get('categories', 'CategoryController::index');
    $routes->get('categories/create', 'CategoryController::create');
    $routes->post('categories/store', 'CategoryController::store');
    $routes->get('categories/edit/(:num)', 'CategoryController::edit/$1');
    $routes->post('categories/update/(:num)', 'CategoryController::update/$1');
    $routes->post('categories/delete/(:num)', 'CategoryController::delete/$1');

    // Manga
    $routes->get('manga', 'MangaController::index');
    $routes->get('manga/create', 'MangaController::create');
    $routes->post('manga/store', 'MangaController::store');
    $routes->get('manga/edit/(:num)', 'MangaController::edit/$1');
    $routes->post('manga/update/(:num)', 'MangaController::update/$1');
    $routes->post('manga/delete/(:num)', 'MangaController::delete/$1');
    $routes->post('manga/upload-cover/(:num)', 'MangaController::uploadCover/$1');
    $routes->get('api/search-authors', 'MangaController::searchAuthors');
    $routes->get('api/search-tags', 'MangaController::searchTags');
    $routes->post('manga/fetch-manga18fx', 'MangaController::fetchManga18fx');
    $routes->post('manga/parse-manga18fx', 'MangaController::parseManga18fxFromHtml');

    // Chapters
    $routes->get('chapters/(:num)', 'ChapterController::index/$1');
    $routes->get('chapters/create/(:num)', 'ChapterController::create/$1');
    $routes->post('chapters/store/(:num)', 'ChapterController::store/$1');
    $routes->get('chapters/edit/(:num)', 'ChapterController::edit/$1');
    $routes->post('chapters/update/(:num)', 'ChapterController::update/$1');
    $routes->post('chapters/delete/(:num)', 'ChapterController::delete/$1');
    $routes->get('chapters/recrawl/(:num)', 'ChapterController::recrawl/$1');
    $routes->post('chapters/bulk-delete/(:num)', 'ChapterController::bulkDelete/$1');
    $routes->get('chapters/fetch-source/(:num)', 'ChapterController::fetchFromSource/$1');
    $routes->post('chapters/import/(:num)', 'ChapterController::importChapters/$1');

    // Pages (chapter images)
    $routes->get('pages/(:num)', 'PageController::index/$1');
    $routes->post('pages/upload/(:num)', 'PageController::upload/$1');
    $routes->post('pages/upload-zip/(:num)', 'PageController::uploadZip/$1');
    $routes->post('pages/upload-bulk/(:num)', 'PageController::uploadBulk/$1');
    $routes->get('pages/edit/(:num)', 'PageController::edit/$1');
    $routes->post('pages/update/(:num)', 'PageController::update/$1');
    $routes->post('pages/delete/(:num)', 'PageController::delete/$1');
    $routes->post('pages/delete-all/(:num)', 'PageController::deleteAll/$1');
    $routes->post('pages/delete-batch', 'PageController::deleteBatch');
    $routes->post('pages/download-external/(:num)', 'PageController::downloadExternal/$1');

    // Tags
    $routes->get('tags', 'TagController::index');
    $routes->get('tags/create', 'TagController::create');
    $routes->post('tags/store', 'TagController::store');
    $routes->get('tags/edit/(:num)', 'TagController::edit/$1');
    $routes->post('tags/update/(:num)', 'TagController::update/$1');
    $routes->post('tags/delete/(:num)', 'TagController::delete/$1');

    // Authors
    $routes->get('authors', 'AuthorController::index');
    $routes->get('authors/create', 'AuthorController::create');
    $routes->post('authors/store', 'AuthorController::store');
    $routes->get('authors/edit/(:num)', 'AuthorController::edit/$1');
    $routes->post('authors/update/(:num)', 'AuthorController::update/$1');
    $routes->post('authors/delete/(:num)', 'AuthorController::delete/$1');

    // Comic Types
    $routes->get('comictypes', 'ComicTypeController::index');
    $routes->get('comictypes/create', 'ComicTypeController::create');
    $routes->post('comictypes/store', 'ComicTypeController::store');
    $routes->get('comictypes/edit/(:num)', 'ComicTypeController::edit/$1');
    $routes->post('comictypes/update/(:num)', 'ComicTypeController::update/$1');
    $routes->post('comictypes/delete/(:num)', 'ComicTypeController::delete/$1');

    // Statuses
    $routes->get('statuses', 'StatusController::index');
    $routes->get('statuses/create', 'StatusController::create');
    $routes->post('statuses/store', 'StatusController::store');
    $routes->get('statuses/edit/(:num)', 'StatusController::edit/$1');
    $routes->post('statuses/update/(:num)', 'StatusController::update/$1');
    $routes->post('statuses/delete/(:num)', 'StatusController::delete/$1');

    // Users
    $routes->get('users', 'UserController::index');
    $routes->get('users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->post('users/delete/(:num)', 'UserController::delete/$1');

    // Comments
    $routes->get('comments', 'CommentController::index');
    $routes->post('comments/delete/(:num)', 'CommentController::delete/$1');

    // Chapter Reports
    $routes->get('reports', 'ReportController::index');
    $routes->match(['get', 'post'], 'reports/resolve/(:num)', 'ReportController::resolve/$1');
    $routes->match(['get', 'post'], 'reports/dismiss/(:num)', 'ReportController::dismiss/$1');
    $routes->match(['get', 'post'], 'reports/delete/(:num)', 'ReportController::delete/$1');
    $routes->post('reports/bulk-resolve', 'ReportController::bulkResolve');
    $routes->post('reports/bulk-dismiss', 'ReportController::bulkDismiss');

    // Ads
    $routes->get('ads', 'AdController::index');
    $routes->get('ads/create', 'AdController::create');
    $routes->post('ads/store', 'AdController::store');
    $routes->get('ads/edit/(:num)', 'AdController::edit/$1');
    $routes->post('ads/update/(:num)', 'AdController::update/$1');
    $routes->post('ads/delete/(:num)', 'AdController::delete/$1');

    // Ad Placements
    $routes->get('placements', 'AdController::placements');
    $routes->get('placements/create', 'AdController::placementCreate');
    $routes->post('placements/store', 'AdController::placementStore');
    $routes->get('placements/edit/(:num)', 'AdController::placementEdit/$1');
    $routes->post('placements/update/(:num)', 'AdController::placementUpdate/$1');
    $routes->post('placements/delete/(:num)', 'AdController::placementDelete/$1');
    $routes->post('placements/save-all', 'AdController::saveAll');

    // Settings
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings/save', 'SettingsController::save');

    // Admin utilities
    $routes->get('updateChapter', '\App\Controllers\Home::updateChapter');
    $routes->get('updateRate', '\App\Controllers\Home::updateRate');
    $routes->get('updatePublishChapter', '\App\Controllers\Home::updatePublishChapter');
    $routes->get('reset_day', '\App\Controllers\Home::resetDay');
    $routes->get('reset_month', '\App\Controllers\Home::resetMonth');
});
