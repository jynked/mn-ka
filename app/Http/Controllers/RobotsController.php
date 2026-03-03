<?php

namespace App\Http\Controllers;

use App\Models\SeoSettings;
use Illuminate\Http\Request;

class RobotsController extends Controller
{
    /**
     * Генерация robots.txt
     */
    public function index()
    {
        $settings = SeoSettings::getSettings();
        
        // Если есть кастомный robots.txt в настройках, используем его
        if ($settings->robots_txt) {
            return response($settings->robots_txt)
                ->header('Content-Type', 'text/plain');
        }
        
        // Иначе генерируем стандартный robots.txt
        $robotsTxt = $this->generateDefaultRobotsTxt($settings);
        
        return response($robotsTxt)
            ->header('Content-Type', 'text/plain');
    }
    
    /**
     * Генерация стандартного robots.txt
     */
    private function generateDefaultRobotsTxt(SeoSettings $settings): string
    {
        $lines = [];
        
        $lines[] = 'User-agent: *';
        
        if ($settings->allow_indexing) {
            // Разрешаем индексацию, но закрываем админку
            $lines[] = 'Disallow: /admin/';
            $lines[] = 'Disallow: /api/';
            $lines[] = 'Allow: /';
        } else {
            // Запрещаем индексацию всего сайта
            $lines[] = 'Disallow: /';
        }
        
        $lines[] = '';
        $lines[] = '# Sitemap';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');
        
        return implode("\n", $lines);
    }
}
