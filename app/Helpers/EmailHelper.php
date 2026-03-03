<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Список невалидных доменов (тестовые, локальные и т.д.)
     */
    protected static array $invalidDomains = [
        'telegram.local',
        'local',
        'test',
        'example',
        'localhost',
    ];

    /**
     * Проверить, является ли email валидным для отправки
     * 
     * @param string|null $email
     * @return bool
     */
    public static function isValidForSending(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        // Проверяем базовую валидность email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Извлекаем домен из email
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = strtolower($parts[1]);

        // Проверяем, не является ли домен тестовым/локальным
        foreach (self::$invalidDomains as $invalidDomain) {
            if (str_ends_with($domain, '.' . $invalidDomain) || $domain === $invalidDomain) {
                return false;
            }
        }

        return true;
    }

    /**
     * Отфильтровать массив email адресов, оставив только валидные
     * 
     * @param array $emails
     * @return array
     */
    public static function filterValidEmails(array $emails): array
    {
        return array_filter($emails, function ($email) {
            return self::isValidForSending($email);
        });
    }
}

