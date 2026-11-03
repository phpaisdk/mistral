# PHP AI SDK — Mistral AI

Text generation, streaming, tools, structured output, and embeddings with Mistral AI. Part of the framework-agnostic [PHP AI SDK](https://phpaisdk.com).

## Installation

```bash
composer require aisdk/mistral
```

Set `MISTRAL_API_KEY` in your environment.

## Usage

```php
use AiSdk\Generate;
use AiSdk\Mistral;

$result = Generate::text('Explain closures in PHP in one sentence.')
    ->model(Mistral::model('mistral-large-latest'))
    ->run();

echo $result->text;
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/mistral) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
