<?php

declare(strict_types=1);

namespace AiSdk\Mistral;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\EmbeddingModelInterface;
use AiSdk\Contracts\EmbeddingProviderInterface;
use AiSdk\Contracts\TextModelInterface;
use AiSdk\Contracts\TextProviderInterface;
use AiSdk\Mistral\Models\MistralEmbeddingModel;
use AiSdk\Mistral\Models\MistralTextModel;

final class MistralProvider extends BaseProvider implements EmbeddingProviderInterface, TextProviderInterface
{
    public function __construct(public readonly MistralOptions $options) {}

    public function name(): string
    {
        return MistralOptions::PROVIDER_NAME;
    }

    protected function textModel(string $modelId): TextModelInterface
    {
        return new MistralTextModel($modelId, $this->options);
    }

    protected function embeddingModel(string $modelId): EmbeddingModelInterface
    {
        return new MistralEmbeddingModel($modelId, $this->options);
    }
}
