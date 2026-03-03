<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Product;
use App\Models\ProjectCase;
use App\Models\Page;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Поиск по сайту
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50); // Максимум 50 результатов
        
        if (empty(trim($query))) {
            return response()->json([
                'data' => [
                    'services' => [],
                    'products' => [],
                    'cases' => [],
                    'pages' => [],
                ],
                'total' => 0,
            ]);
        }

        $searchTerm = '%' . $query . '%';
        $results = [
            'services' => [],
            'products' => [],
            'cases' => [],
            'pages' => [],
        ];

        // Поиск по услугам
        $services = Service::where('is_active', true)
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm);
            })
            ->select('id', 'name', 'slug', 'description', 'order')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'url' => '/services/' . $service->slug,
                    'description' => $service->description ? (is_array($service->description) ? implode(' ', $service->description) : $service->description) : '',
                    'type' => 'service',
                    'type_label' => 'Услуга',
                ];
            });

        // Поиск по продуктам
        $products = Product::where('is_active', true)
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm);
            })
            ->select('id', 'name', 'slug', 'description', 'order')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'url' => '/products/' . $product->slug,
                    'description' => $product->description ? (is_array($product->description) ? implode(' ', $product->description) : $product->description) : '',
                    'type' => 'product',
                    'type_label' => 'Продукт',
                ];
            });

        // Поиск по кейсам
        $cases = ProjectCase::where('is_active', true)
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm);
            })
            ->select('id', 'name', 'slug', 'description', 'order')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($case) {
                $description = '';
                if ($case->description) {
                    if (is_array($case->description)) {
                        $description = implode(' ', array_filter($case->description));
                    } else {
                        $description = $case->description;
                    }
                }
                return [
                    'id' => $case->id,
                    'name' => $case->name,
                    'slug' => $case->slug,
                    'url' => '/cases/' . $case->slug,
                    'description' => $description,
                    'type' => 'case',
                    'type_label' => 'Кейс',
                ];
            });

        // Поиск по страницам
        $pages = Page::where('is_active', true)
            ->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('slug', 'like', $searchTerm)
                  ->orWhere('content', 'like', $searchTerm);
            })
            ->select('id', 'title', 'slug', 'content')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($page) {
                $content = $page->content ? (is_array($page->content) ? implode(' ', $page->content) : $page->content) : '';
                // Убираем HTML теги для описания
                $content = strip_tags($content);
                $content = mb_substr($content, 0, 200);
                return [
                    'id' => $page->id,
                    'name' => $page->title,
                    'slug' => $page->slug,
                    'url' => '/' . $page->slug,
                    'description' => $content,
                    'type' => 'page',
                    'type_label' => 'Страница',
                ];
            });

        $results = [
            'services' => $services,
            'products' => $products,
            'cases' => $cases,
            'pages' => $pages,
        ];

        $total = $services->count() + $products->count() + $cases->count() + $pages->count();

        return response()->json([
            'data' => $results,
            'total' => $total,
            'query' => $query,
        ]);
    }

    /**
     * Автодополнение для поиска (быстрые подсказки)
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 5), 10); // Максимум 10 подсказок
        
        if (empty(trim($query)) || strlen($query) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $searchTerm = '%' . $query . '%';
        $suggestions = [];

        // Быстрый поиск по названиям
        $services = Service::where('is_active', true)
            ->where('name', 'like', $searchTerm)
            ->select('id', 'name', 'slug')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'url' => '/services/' . $service->slug,
                    'type' => 'service',
                    'type_label' => 'Услуга',
                ];
            });

        $products = Product::where('is_active', true)
            ->where('name', 'like', $searchTerm)
            ->select('id', 'name', 'slug')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'url' => '/products/' . $product->slug,
                    'type' => 'product',
                    'type_label' => 'Продукт',
                ];
            });

        $cases = ProjectCase::where('is_active', true)
            ->where('name', 'like', $searchTerm)
            ->select('id', 'name', 'slug')
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function($case) {
                return [
                    'id' => $case->id,
                    'name' => $case->name,
                    'url' => '/cases/' . $case->slug,
                    'type' => 'case',
                    'type_label' => 'Кейс',
                ];
            });

        // Объединяем все результаты и ограничиваем общее количество
        $allSuggestions = $services->concat($products)->concat($cases)->take($limit);

        return response()->json([
            'data' => $allSuggestions->values(),
        ]);
    }
}





