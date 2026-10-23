<?php

use Laravel\Ai\Responses\Data\RerankingUsage;

test('reranking usage to array appends the search units to the base usage counts', function (): void {
    expect((new RerankingUsage(320, 2.5, cost: 0.25))->toArray())->toBe([
        'input_tokens' => 320,
        'output_tokens' => 0,
        'cost' => 0.25,
        'search_units' => 2.5,
    ]);
});
