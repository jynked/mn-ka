<?php

namespace Database\Seeders;

use App\Models\SeoSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\URL;

class SeoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем базовый URL для robots.txt и sitemap
        $baseUrl = config('app.url', 'https://lagom.ru');
        
        // Создаем или обновляем глобальные SEO настройки
        SeoSettings::updateOrCreate(
            ['id' => 1],
            [
                // Основные настройки сайта
                'site_name' => 'МНКА - Профессиональные услуги по работе с земельными участками',
                'site_description' => 'Профессиональные услуги по подбору и оформлению земельных участков. Кадастровые работы, консультации, оформление документов. Помогаем найти идеальный участок под ваш проект и решить все юридические вопросы.',
                'site_keywords' => 'земельные участки, подбор участка, оформление земли, кадастровые услуги, недвижимость, покупка земли, кадастр, юридическое сопровождение, оформление документов, МНКА',
                
                // Open Graph настройки
                'default_og_image' => $baseUrl . '/img/og-image-default.jpg',
                'og_type' => 'website',
                'og_site_name' => 'МНКА',
                
                // Twitter Cards настройки
                'twitter_card' => 'summary_large_image',
                'twitter_site' => '@lagom',
                'twitter_creator' => '@lagom',
                
                // Контактная информация организации (для Schema.org)
                'organization_name' => 'МНКА',
                'organization_logo' => $baseUrl . '/img/logo.png',
                'organization_phone' => '+7 (999) 123-45-67',
                'organization_email' => 'info@lagom.ru',
                'organization_address' => 'г. Москва, ул. Примерная, д. 1',
                
                // Настройки индексации поисковыми системами
                'allow_indexing' => true,
                'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\nDisallow: /_nuxt/\n\nSitemap: {$baseUrl}/sitemap.xml",
                
                // Дополнительные Schema.org данные (можно расширить позже)
                'additional_schema' => null,
            ]
        );

        $this->command->info('✅ SEO settings created successfully!');
        $this->command->info('   Site Name: МНКА - Профессиональные услуги по работе с земельными участками');
        $this->command->info('   Indexing: Enabled');
        $this->command->info('   Sitemap: ' . $baseUrl . '/sitemap.xml');
    }
}

