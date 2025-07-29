<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class AsaasConfig extends Model
{
    protected $table = 'asaas_configs';

    public static function getFormasPagamento(): array
    {
        $config = self::where('key', 'formasdepagamento')->first();
        return $config ? json_decode($config->value, true) : [];
    }

    public static function get(string $key)
    {
        return optional(self::where('key', $key)->first())->value;
    }

    public static function allAsObject(): object
    {
        $configs = self::all();
        $result = [];

        foreach ($configs as $config) {
            $decoded = json_decode($config->value, true);
            $result[$config->key] = $decoded === null ? $config->value : $decoded;
        }

        return (object)$result;
    }

    public static function notificacaoConfig(): object
    {
        $config = self::where('key', 'like', 'notificacao%')->get();
        $result = [];

        foreach ($config as $item) {
            $decoded = json_decode($item->value, true);
            $result[$item->key] = $decoded === null ? $item->value : $decoded;
        }

        if (empty($result)) {
            // default values
            $result = [
                'notificacaoAtiva' => '0',
                'notificacaoEventos' => [
                    'PAYMENT_OVERDUE',
                    'PAYMENT_DUEDATE_WARNING',
                ],
                'notificacaoCanais' => [
                    'emailEnabledForCustomer',
                    'smsEnabledForCustomer',
                ],
            ];
        }

        return (object)$result;
    }

    public static function set(string $key, $value, bool $isJson): void
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            $config = new self();
            $config->key = $key;
        }
        if ($isJson) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $value = (string)$value;
        }
        $config->value = $value;

        $config->save();
    }
}
