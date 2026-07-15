<?php

declare(strict_types=1);

use AiSdk\Generate;
use AiSdk\Mistral;
use AiSdk\Mistral\Tests\Fakes\FakeHttpClient;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;

afterEach(function () {
    Generate::reset();
    Mistral::reset();
});

function configureMistralEmbeddingsWith(FakeHttpClient $client): void
{
    $factory = new Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
}

it('generates Mistral embeddings using its documented endpoint', function () {
    $client = new FakeHttpClient(200, json_encode([
        'object' => 'list',
        'model' => 'mistral-embed',
        'data' => [['object' => 'embedding', 'index' => 0, 'embedding' => [0.1, 0.2]]],
        'usage' => ['prompt_tokens' => 4, 'total_tokens' => 4],
    ]));
    configureMistralEmbeddingsWith($client);
    Mistral::create(['apiKey' => 'mistral-test']);

    $result = Generate::embedding('A document')
        ->model(Mistral::model('mistral-embed'))
        ->providerOptions('mistral', ['user' => 'user-123'])
        ->run();

    expect($result->output->vector)->toBe([0.1, 0.2])
        ->and($result->usage->inputTokens)->toBe(4)
        ->and($client->lastRequest?->getUri()->getPath())->toBe('/v1/embeddings')
        ->and($client->sentBody())->toMatchArray([
            'model' => 'mistral-embed',
            'input' => ['A document'],
            'encoding_format' => 'float',
            'user' => 'user-123',
        ]);
});

it('rejects unsupported portable dimensions before sending a Mistral request', function () {
    $client = new FakeHttpClient(200, '{}');
    configureMistralEmbeddingsWith($client);
    Mistral::create(['apiKey' => 'mistral-test']);

    Generate::embedding('A document')
        ->model(Mistral::model('mistral-embed'))
        ->dimensions(256)
        ->run();
})->throws(\AiSdk\Exceptions\InvalidArgumentException::class);
