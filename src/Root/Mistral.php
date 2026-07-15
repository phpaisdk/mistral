<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\Mistral\MistralOptions;
use AiSdk\Mistral\MistralProvider;

/**
 * Friendly facade for the Mistral provider.
 *
 *   $model = Mistral::model('mistral-large-latest');
 */
final class Mistral
{
    private static ?MistralProvider $default = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public static function create(array $config = []): MistralProvider
    {
        return self::$default = new MistralProvider(MistralOptions::fromArray($config));
    }

    public static function default(): MistralProvider
    {
        return self::$default ??= self::create();
    }

    public static function reset(): void
    {
        self::$default = null;
    }

    public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
