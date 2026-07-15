<?php

declare(strict_types=1);

use AiSdk\Generate;
use AiSdk\Mistral;

afterEach(function () {
    Generate::reset();
    Mistral::reset();
});

it('uses adapter capabilities for opaque Mistral model ids', function () {
    Mistral::create(['apiKey' => 'mistral-test']);
    $model = Mistral::model('vendor/private-model');

    expect($model->modelId())->toBe('vendor/private-model');
});
