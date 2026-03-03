<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterSettings extends Model
{
    use HasFactory;

    protected $table = 'footer_settings';

    protected $fillable = [
        'title',
        'department_label',
        'department_phone',
        'objects_label',
        'objects_phone',
        'issues_label',
        'issues_email',
        'social_networks',
        'vk_icon_id',
        'vk_icon_svg',
        'instagram_icon_id',
        'instagram_icon_svg',
        'telegram_icon_id',
        'telegram_icon_svg',
        'menu_items',
        'privacy_policy_link',
        'copyright',
    ];

    protected $casts = [
        'social_networks' => 'array',
        'menu_items' => 'array',
    ];

    /**
     * Иконка VK.
     */
    public function vkIcon()
    {
        return $this->belongsTo(Media::class, 'vk_icon_id');
    }

    /**
     * Иконка Instagram.
     */
    public function instagramIcon()
    {
        return $this->belongsTo(Media::class, 'instagram_icon_id');
    }

    /**
     * Иконка Telegram.
     */
    public function telegramIcon()
    {
        return $this->belongsTo(Media::class, 'telegram_icon_id');
    }

    /**
     * Получить настройки футера (singleton)
     */
    public static function getSettings(): self
    {
        $defaultVkSvg = '<svg width="57" height="35" viewBox="0 0 57 35" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M54.7096 34.9972H48.4685C46.1082 34.9972 45.3971 33.0212 41.1662 28.6453C37.4686 24.962 35.9069 24.5041 34.9716 24.5041C33.6779 24.5041 33.3251 24.8715 33.3251 26.7118V32.5124C33.3251 34.0813 32.8329 35 28.8672 35C25.0199 34.7329 21.2891 33.5248 17.9821 31.4754C14.675 29.426 11.8864 26.5938 9.84575 23.2122C5.00222 16.9781 1.63114 9.66288 0 1.8472C0 0.880432 0.355541 0.00412229 2.13872 0.00412229H8.37436C9.97703 0.00412229 10.5541 0.74192 11.1831 2.44648C14.2107 11.6562 19.377 19.6674 21.4747 19.6674C22.2788 19.6674 22.6288 19.2999 22.6288 17.2279V7.7411C22.3635 3.41325 20.14 3.04859 20.14 1.48254C20.1682 1.06938 20.351 0.683824 20.6498 0.407771C20.9485 0.131718 21.3396 -0.0130839 21.74 0.00412229H31.542C32.8821 0.00412229 33.3251 0.693863 33.3251 2.35037V15.1558C33.3251 16.5381 33.8995 16.9961 34.3042 16.9961C35.1083 16.9961 35.7264 16.5381 37.2005 15.0173C40.3591 11.0334 42.9405 6.59598 44.8638 1.84437C45.0598 1.27326 45.4315 0.784298 45.922 0.452423C46.4125 0.120547 46.9948 -0.0359369 47.5796 0.00694889H53.818C55.6887 0.00694889 56.0852 0.973718 55.6887 2.3532C53.4191 7.60625 50.6116 12.5929 47.3143 17.2279C46.6415 18.2879 46.3735 18.8391 47.3143 20.0829C47.9324 21.0497 50.1204 22.938 51.589 24.733C53.7277 26.9379 55.5027 29.4877 56.8455 32.2806C57.3816 34.0785 56.49 34.9972 54.7096 34.9972Z" fill="white"></path></svg>';
        
        $defaultInstagramSvg = '<svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.15 0H24.85C30.45 0 35 4.55 35 10.15V24.85C35 27.5419 33.9306 30.1236 32.0271 32.0271C30.1236 33.9306 27.5419 35 24.85 35H10.15C4.55 35 0 30.45 0 24.85V10.15C0 7.45805 1.06937 4.87636 2.97287 2.97287C4.87636 1.06937 7.45805 0 10.15 0ZM9.8 3.5C8.12914 3.5 6.52671 4.16375 5.34523 5.34523C4.16375 6.52671 3.5 8.12914 3.5 9.8V25.2C3.5 28.6825 6.3175 31.5 9.8 31.5H25.2C26.8709 31.5 28.4733 30.8363 29.6548 29.6548C30.8363 28.4733 31.5 26.8709 31.5 25.2V9.8C31.5 6.3175 28.6825 3.5 25.2 3.5H9.8ZM26.6875 6.125C27.2677 6.125 27.8241 6.35547 28.2343 6.7657C28.6445 7.17594 28.875 7.73234 28.875 8.3125C28.875 8.89266 28.6445 9.44906 28.2343 9.8593C27.8241 10.2695 27.2677 10.5 26.6875 10.5C26.1073 10.5 25.5509 10.2695 25.1407 9.8593C24.7305 9.44906 24.5 8.89266 24.5 8.3125C24.5 7.73234 24.7305 7.17594 25.1407 6.7657C25.5509 6.35547 26.1073 6.125 26.6875 6.125ZM17.5 8.75C19.8206 8.75 22.0462 9.67187 23.6872 11.3128C25.3281 12.9538 26.25 15.1794 26.25 17.5C26.25 19.8206 25.3281 22.0462 23.6872 23.6872C22.0462 25.3281 19.8206 26.25 17.5 26.25C15.1794 26.25 12.9538 25.3281 11.3128 23.6872C9.67187 22.0462 8.75 19.8206 8.75 17.5C8.75 15.1794 9.67187 12.9538 11.3128 11.3128C12.9538 9.67187 15.1794 8.75 17.5 8.75ZM17.5 12.25C16.1076 12.25 14.7723 12.8031 13.7877 13.7877C12.8031 14.7723 12.25 16.1076 12.25 17.5C12.25 18.8924 12.8031 20.2277 13.7877 21.2123C14.7723 22.1969 16.1076 22.75 17.5 22.75C18.8924 22.75 20.2277 22.1969 21.2123 21.2123C22.1969 20.2277 22.75 18.8924 22.75 17.5C22.75 16.1076 22.1969 14.7723 21.2123 13.7877C20.2277 12.8031 18.8924 12.25 17.5 12.25Z" fill="white"></path></svg>';
        
        $defaultTelegramSvg = '<svg width="41" height="35" viewBox="0 0 41 35" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M37.5059 0.309016C37.5059 0.309016 41.2984 -1.20598 40.9811 2.47301C40.8767 3.988 39.9288 9.29098 39.1908 15.026L36.6624 32.0159C36.6624 32.0159 36.4516 34.5049 34.5548 34.9379C32.659 35.3699 29.8144 33.4229 29.2873 32.9899C28.8655 32.6649 21.3859 27.7949 18.7521 25.4149C18.0141 24.7649 17.1707 23.4669 18.8575 21.9519L29.9198 11.13C31.184 9.82998 32.4482 6.79999 27.1806 10.48L12.4302 20.7599C12.4302 20.7599 10.7443 21.8429 7.58438 20.8689L0.735366 18.7039C0.735366 18.7039 -1.79299 17.0809 2.52669 15.458C13.0628 10.372 26.0219 5.178 37.5049 0.308016" fill="white"></path></svg>';
        
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'title' => 'Контакты',
                'department_label' => 'Отдел',
                'department_phone' => null,
                'objects_label' => 'Объекты',
                'objects_phone' => null,
                'issues_label' => 'Вопросы',
                'issues_email' => null,
                'social_networks' => [
                    'vk' => null,
                    'instagram' => null,
                    'telegram' => null,
                ],
                'vk_icon_svg' => $defaultVkSvg,
                'instagram_icon_svg' => $defaultInstagramSvg,
                'telegram_icon_svg' => $defaultTelegramSvg,
                'menu_items' => [],
                'privacy_policy_link' => '/police',
                'copyright' => 'MNKA 2025. Все права защищены',
            ]);
        } else {
            // Устанавливаем дефолтные SVG если поля пустые
            $needsUpdate = false;
            $updates = [];
            
            if (empty($settings->vk_icon_svg)) {
                $updates['vk_icon_svg'] = $defaultVkSvg;
                $needsUpdate = true;
            }
            
            if (empty($settings->instagram_icon_svg)) {
                $updates['instagram_icon_svg'] = $defaultInstagramSvg;
                $needsUpdate = true;
            }
            
            if (empty($settings->telegram_icon_svg)) {
                $updates['telegram_icon_svg'] = $defaultTelegramSvg;
                $needsUpdate = true;
            }
            
            if ($needsUpdate) {
                $settings->update($updates);
                $settings->refresh();
            }
        }
        
        return $settings;
    }
}



