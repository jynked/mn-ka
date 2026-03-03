<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'site_description',
        'site_keywords',
        'default_og_image',
        'og_type',
        'og_site_name',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'organization_name',
        'organization_logo',
        'organization_phone',
        'organization_email',
        'organization_address',
        'allow_indexing',
        'robots_txt',
        'additional_schema',
        'custom_js_code',
    ];

    protected $casts = [
        'allow_indexing' => 'boolean',
        'additional_schema' => 'array',
    ];

    /**
     * Получить настройки SEO (singleton)
     */
    public static function getSettings(): self
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'site_name' => config('app.name', 'МНКА'),
                'site_description' => 'Профессиональные услуги по подбору и оформлению земельных участков',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
                'allow_indexing' => true,
            ]);
        }

        return $settings;
    }

    /**
     * Получить Schema.org разметку для организации
     */
    public function getOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->organization_name ?: $this->site_name,
            'logo' => $this->organization_logo ? url($this->organization_logo) : null,
            'telephone' => $this->organization_phone,
            'email' => $this->organization_email,
            'address' => $this->organization_address ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $this->organization_address,
            ] : null,
        ];
    }

    /**
     * Получить базовую Schema.org разметку для сайта
     */
    public function getWebSiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->site_name,
            'description' => $this->site_description,
            'url' => url('/'),
        ];
    }
}
