<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\CaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    /**
     * Генерация sitemap.xml
     */
    public function index()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Главная страница
        $xml .= $this->addUrl(url('/'), now(), 'daily', '1.0');
        
        // Статические страницы
        $xml .= $this->addUrl(url('/products'), now(), 'weekly', '0.9');
        $xml .= $this->addUrl(url('/services'), now(), 'weekly', '0.9');
        $xml .= $this->addUrl(url('/cases'), now(), 'weekly', '0.8');
        $xml .= $this->addUrl(url('/about'), now(), 'monthly', '0.7');
        $xml .= $this->addUrl(url('/contacts'), now(), 'monthly', '0.7');
        
        // Продукты
        try {
            $products = Product::where('is_active', true)->get();
            foreach ($products as $product) {
                $xml .= $this->addUrl(
                    url('/products/' . $product->slug),
                    $product->updated_at,
                    'weekly',
                    '0.8'
                );
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки, если таблица не существует
        }
        
        // Услуги
        try {
            $services = Service::where('is_active', true)->get();
            foreach ($services as $service) {
                $xml .= $this->addUrl(
                    url('/services/' . $service->slug),
                    $service->updated_at,
                    'weekly',
                    '0.8'
                );
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }
        
        // Кейсы
        try {
            $cases = CaseModel::where('is_active', true)->get();
            foreach ($cases as $case) {
                $xml .= $this->addUrl(
                    url('/cases/' . $case->slug),
                    $case->updated_at,
                    'monthly',
                    '0.7'
                );
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }
        
        // Динамические страницы из админки
        try {
            $pages = Page::active()->get();
            foreach ($pages as $page) {
                $xml .= $this->addUrl(
                    url('/' . $page->slug),
                    $page->updated_at,
                    'monthly',
                    '0.6'
                );
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }
        
        $xml .= '</urlset>';
        
        return response($xml)
            ->header('Content-Type', 'application/xml');
    }
    
    /**
     * Добавить URL в sitemap
     */
    private function addUrl(string $loc, $lastmod = null, string $changefreq = 'monthly', string $priority = '0.5'): string
    {
        $xml = '<url>';
        $xml .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        
        if ($lastmod) {
            $xml .= '<lastmod>' . $lastmod->format('Y-m-d') . '</lastmod>';
        }
        
        $xml .= '<changefreq>' . $changefreq . '</changefreq>';
        $xml .= '<priority>' . $priority . '</priority>';
        $xml .= '</url>';
        
        return $xml;
    }
}
