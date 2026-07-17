# aisdk/mistral

<a href="https://github.com/phpaisdk/mistral/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/mistral/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/mistral"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/aisdk/mistral"></a>
<a href="https://packagist.org/packages/aisdk/mistral"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/mistral"></a>
<a href="https://packagist.org/packages/aisdk/mistral"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/mistral"></a>
<a href="https://whyphp.dev"><img src="https://img.shields.io/badge/Why_PHP-in_2026-7A86E8?style=flat-square&labelColor=18181b" alt="Why PHP in 2026"></a>

------

Official Mistral provider for the PHP AI SDK. Uses Mistral's OpenAI-compatible Chat Completions API and embeddings endpoint.

## Installation

```bash
composer require aisdk/mistral
```

## Basic Usage

```php
use AiSdk\Generate;
use AiSdk\Mistral;

$result = Generate::text()
    ->model(Mistral::model('mistral-large-latest'))
    ->instructions('Write short, clear answers.')
    ->prompt('Explain closures in PHP.')
    ->run();

echo $result->text;
```

Default model shorthand:

```php
Generate::model(Mistral::model('mistral-large-latest'));

$result = Generate::text('Explain closures in PHP.')->run();
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|---|---|---|
| `MISTRAL_API_KEY` | API key for authentication | Required |
| `MISTRAL_BASE_URL` | Base URL for API requests | `https://api.mistral.ai/v1` |

### Programmatic Configuration

```php
$provider = Mistral::create([
    'apiKey' => 'mistral-...',
    'baseUrl' => 'https://api.mistral.ai/v1',
    'headers' => ['X-Custom-Header' => 'value'],
]);
```

## Supported Capabilities

| Capability | Support |
|---|---|
| Text generation | Native |
| Streaming | Native |
| Tool calling | Native |
| Structured output | Adapted (`json_object` + instruction); exact native support can be declared at runtime |
| Embeddings | Native |
| Text input | Native |
| Image input | Supported by the adapter; the selected model is validated by Mistral |

## Streaming

```php
use AiSdk\Generate;
use AiSdk\Mistral;

$stream = Generate::text('Tell me a story.')
    ->model(Mistral::model('mistral-large-latest'))
    ->stream();

foreach ($stream->chunks() as $chunk) {
    echo $chunk;
}

$result = $stream->run();
```

## Embeddings

```php
use AiSdk\Generate;
use AiSdk\Mistral;

$result = Generate::embedding(['Search query', 'Document text'])
    ->model(Mistral::model('mistral-embed'))
    ->providerOptions('mistral', ['user' => 'user-123'])
    ->run();

$queryVector = $result->embeddings[0]->vector;
$documentVector = $result->embeddings[1]->vector;
```

Mistral's embedding request schema does not expose a dimensions field, so this adapter rejects the portable `dimensions()` option before sending a request.

## Structured Output

Without an exact runtime capability override, the provider adapter degrades `json_schema` to `json_object` with an injected JSON instruction:

```php
use AiSdk\Generate;
use AiSdk\Mistral;
use AiSdk\Schema;

$result = Generate::text()
    ->model(Mistral::model('mistral-large-latest'))
    ->prompt('Extract the city and country from: Lahore, Pakistan.')
    ->output(Schema::object(
        name: 'address',
        properties: [
            Schema::string(name: 'city')->required(),
            Schema::string(name: 'country')->required(),
        ],
    ))
    ->run();
```

Models with native `json_schema` support (`openai/gpt-oss-20b`, `openai/gpt-oss-120b`, `moonshotai/kimi*`) use the native format directly.

## Tools

```php
use AiSdk\Generate;
use AiSdk\Mistral;
use AiSdk\Schema;
use AiSdk\Tool;

$weather = Tool::make('weather', 'Get current weather')
    ->input(Schema::string(name: 'city')->required())
    ->run(fn (string $city): string => "Sunny in {$city}");

$result = Generate::text()
    ->model(Mistral::model('mistral-large-latest'))
    ->prompt('What is the weather in Lahore?')
    ->tool($weather)
    ->run();
```

## Model IDs and Capabilities

Mistral model IDs pass through unchanged and do not need to be registered. The package does not ship a model inventory; the Mistral API remains the authority on whether a particular model accepts a requested feature.

Capabilities describe what the Mistral adapter can serialize. The Mistral API returns a normalized SDK exception if the selected model or requested feature is rejected.

## Provider-Specific Options

Raw provider options can be passed as an escape hatch:

```php
$result = Generate::text('Hello')
    ->model(Mistral::model('mistral-large-latest'))
    ->providerOptions('mistral', [
        'raw' => ['top_k' => 40],
    ])
    ->run();
```

## Testing

```bash
composer test
```

## Documentation

- [PHP AI SDK documentation](https://phpaisdk.com/docs)
- [Mistral documentation](https://phpaisdk.com/docs/mistral)

## Community

- [Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md)
- [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)
- For private security reports, email [security@phpaisdk.com](mailto:security@phpaisdk.com).
