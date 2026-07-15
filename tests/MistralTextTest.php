<?php

declare(strict_types=1);

use AiSdk\Generate;
use AiSdk\Mistral;
use AiSdk\Mistral\Tests\Fakes\FakeHttpClient;
use AiSdk\Schema;
use AiSdk\Support\Sdk;

afterEach(function () {
    Generate::reset();
    Mistral::reset();
});

function configureMistralWith(FakeHttpClient $client): void
{
    $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    Generate::configure(new Sdk(
        httpClient: $client,
        requestFactory: $factory,
        streamFactory: $factory,
    ));
}

it('generates text end to end through the Mistral vertical', function () {
    $client = new FakeHttpClient(200, json_encode([
        'id' => 'chatcmpl_mistral',
        'object' => 'chat.completion',
        'created' => 1710000000,
        'model' => 'mistral-large-latest',
        'system_fingerprint' => 'fp_mistral',
        'choices' => [['index' => 0, 'message' => ['content' => 'Hello from Mistral'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 7, 'completion_tokens' => 3],
    ]));
    configureMistralWith($client);

    Mistral::create(['apiKey' => 'mistral-test']);

    $result = Generate::text('Hi')->model(Mistral::model('mistral-large-latest'))->run();

    expect($result->text)->toBe('Hello from Mistral')
        ->and($result->usage->inputTokens)->toBe(7)
        ->and($result->providerMetadata['mistral']['id'])->toBe('chatcmpl_mistral')
        ->and($result->providerMetadata['mistral']['model'])->toBe('mistral-large-latest')
        ->and($result->providerMetadata['mistral']['choice_finish_reason'])->toBe('stop');

    $body = $client->sentBody();
    expect($body['model'])->toBe('mistral-large-latest')
        ->and($body['messages'][0]['role'])->toBe('user')
        ->and($body['stream'])->toBeFalse();

    expect($client->lastRequest->getHeaderLine('Authorization'))->toBe('Bearer mistral-test');
});

it('normalizes provider-neutral text usage fields', function () {
    $client = new FakeHttpClient(200, json_encode([
        'choices' => [['index' => 0, 'message' => ['content' => 'Hello from Mistral'], 'finish_reason' => 'stop']],
        'usage' => ['input_tokens' => 13, 'output_tokens' => 6, 'total_tokens' => 19],
    ]));
    configureMistralWith($client);

    Mistral::create(['apiKey' => 'mistral-test']);

    $result = Generate::text('Hi')->model(Mistral::model('mistral-large-latest'))->run();

    expect($result->usage->inputTokens)->toBe(13)
        ->and($result->usage->outputTokens)->toBe(6)
        ->and($result->usage->totalTokens)->toBe(19);
});

it('maps a 429 to a rate limit exception', function () {
    $client = new FakeHttpClient(429, json_encode(['error' => ['message' => 'slow down']]));
    configureMistralWith($client);
    Mistral::create(['apiKey' => 'mistral-test']);

    Generate::text('Hi')->model(Mistral::model('mistral-large-latest'))->run();
})->throws(\AiSdk\Exceptions\RateLimitException::class);

it('falls back to json_object structured output for models without json_schema support', function () {
    $client = new FakeHttpClient(200, json_encode([
        'choices' => [['message' => ['content' => '{"city":"Lahore","country":"Pakistan"}'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 7],
    ]));
    configureMistralWith($client);
    Mistral::create(['apiKey' => 'mistral-test']);

    $result = Generate::text('Extract the city and country from: Lahore, Pakistan.')
        ->model(Mistral::model('mistral-small-latest'))
        ->output(Schema::object(
            name: 'address',
            properties: [
                Schema::string(name: 'city')->required(),
                Schema::string(name: 'country')->required(),
            ],
        ))
        ->run();

    $body = $client->sentBody();

    expect($body['response_format'])->toBe(['type' => 'json_object'])
        ->and($body['messages'][0]['role'])->toBe('system')
        ->and($body['messages'][0]['content'])->toContain('valid JSON object')
        ->and($result->output)->toBe(['city' => 'Lahore', 'country' => 'Pakistan']);
});

it('accepts opaque text model ids without a model inventory', function () {
    Mistral::create(['apiKey' => 'mistral-test']);

    expect(Mistral::model('future-private-model')->modelId())->toBe('future-private-model');
});
