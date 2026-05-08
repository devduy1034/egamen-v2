<?php


namespace LARAVEL\Controllers\Web;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Models\SlugModel;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        header("Content-Type: text/xml; charset=utf-8");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        echo '<url><loc>' . config('app.asset') . '</loc><lastmod>' . date('c', time()) . '</lastmod><changefreq>daily</changefreq><priority>0.1</priority></url>';
        $this->createSitemap("c", "daily", "1", "vi");
        echo '</urlset>';
    }

    private function createSitemap($time = '', $changefreq = '', $priority = '', $lang = 'vi')
    {
        $sitemap = SlugModel::select('*')
            ->where('slugvi', '!=', '')
            ->get();

        $menu = config('type.seo.sitemap');
        
        foreach ($menu as $key => $value) {
            $urlSm = config('app.asset') . $key;
            echo '<url>';
            echo '<loc>' . $urlSm . '</loc>';
            echo '<lastmod>' . date('c', time()) . '</lastmod>';
            echo '<changefreq>' . $changefreq . '</changefreq>';
            echo '<priority>' . $priority . '</priority>';
            echo '</url>';
        }

        if (!empty($sitemap)) {
            foreach (config('app.slugs') as $k => $v) {
                foreach ($sitemap as $value) {
                    if (!empty($value['slug' . $k])) {
                        $urlSm = config('app.asset') . ((config('app.langconfig') == 'link') ? ($k . '/') : '') . $value['slug' . $k];
                        echo '<url>';
                        echo '<loc>' . $urlSm . '</loc>';
                        echo '<lastmod>' . date('c', $value['date_created']) . '</lastmod>';
                        echo '<changefreq>' . $changefreq . '</changefreq>';
                        echo '<priority>' . $priority . '</priority>';
                        echo '</url>';
                    }
                }
            }
        }
    }
}