<?php

declare(strict_types=1);

namespace AiSdk\Mistral\Models;

use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\EmbeddingModelInterface;
use AiSdk\Exceptions\InvalidArgumentException;
use AiSdk\Mistral\MistralOptions;
use AiSdk\OpenAICompatible\EmbeddingRequestBuilder;
use AiSdk\OpenAICompatible\EmbeddingResponseParser;
use AiSdk\Requests\EmbeddingRequest;
use AiSdk\Responses\EmbeddingResponse;
use AiSdk\Utils\Support\Url;

final class MistralEmbeddingModel extends BaseModel implements EmbeddingModelInterface
{
    public function __construct(
        private readonly string $modelId,
        private readonly MistralOptions $options,
    ) {}

    public function provider(): string
    {
        return MistralOptions::PROVIDER_NAME;
    }

    public function modelId(): string
    {
        return $this->modelId;
    }

    public function generate(EmbeddingRequest $request): EmbeddingResponse
    {
        if ($request->dimensions !== null) {
            throw new InvalidArgumentException('Mistral embeddings do not support the portable dimensions() option.');
        }

        $body = EmbeddingRequestBuilder::build($this->modelId, $this->provider(), $request, [
            'dimensionsParameter' => null,
        ]);
        $payload = $this->runner($this->options->sdk)->postJson(
            Url::joinPath($this->options->baseUrl, '/embeddings'),
            $body,
            $this->options->authHeaders(),
            $this->provider(),
        );

        return EmbeddingResponseParser::parse($payload, $this->provider(), count($request->inputs));
    }
}
