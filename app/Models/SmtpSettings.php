<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmtpSettings extends Model
{
    use HasFactory;

    protected $table = 'smtp_settings';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Получить расшифрованный пароль
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        return $this->password ? Crypt::decryptString($this->password) : null;
    }

    /**
     * Установить зашифрованный пароль
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Получить настройки SMTP (singleton)
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'mailer' => 'smtp',
                'host' => 'smtp.beget.com',
                'port' => 465,
                'username' => 'info@proffi-center.ru',
                'password' => 'V*TZpQMKM3tr',
                'encryption' => 'ssl',
                'from_email' => 'info@proffi-center.ru',
                'from_name' => 'mn-ka.ru',
                'is_active' => true,
            ]);
        }
        
        return $settings;
    }

    /**
     * Применить настройки SMTP к конфигурации Laravel
     */
    public function applyToConfig(): void
    {
        if (!$this->is_active) {
            return;
        }

        config([
            'mail.default' => $this->mailer,
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->getDecryptedPasswordAttribute(),
            'mail.mailers.smtp.encryption' => $this->encryption,
            'mail.from.address' => $this->from_email,
            'mail.from.name' => $this->from_name,
        ]);
    }
}
