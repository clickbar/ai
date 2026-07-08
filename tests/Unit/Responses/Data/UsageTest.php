<?php

use Laravel\Ai\Responses\Data\Usage;

test('usage defaults all tokens to zero', function (): void {
    $usage = new Usage;

    expect($usage->promptTokens)->toBe(0)
        ->and($usage->completionTokens)->toBe(0)
        ->and($usage->cacheWriteInputTokens)->toBe(0)
        ->and($usage->cacheReadInputTokens)->toBe(0)
        ->and($usage->reasoningTokens)->toBe(0);
});

test('usage accepts token values in constructor', function (): void {
    $usage = new Usage(100, 50, 25, 10, 5);

    expect($usage->promptTokens)->toBe(100)
        ->and($usage->completionTokens)->toBe(50)
        ->and($usage->cacheWriteInputTokens)->toBe(25)
        ->and($usage->cacheReadInputTokens)->toBe(10)
        ->and($usage->reasoningTokens)->toBe(5);
});

test('usage add returns new instance with summed tokens', function (): void {
    $usage1 = new Usage(100, 50, 25, 10, 5);
    $usage2 = new Usage(50, 25, 10, 5, 0);

    $combined = $usage1->add($usage2);

    expect($combined->promptTokens)->toBe(150)
        ->and($combined->completionTokens)->toBe(75)
        ->and($combined->cacheWriteInputTokens)->toBe(35)
        ->and($combined->cacheReadInputTokens)->toBe(15)
        ->and($combined->reasoningTokens)->toBe(5);
});

test('usage add does not mutate original', function (): void {
    $usage1 = new Usage(100, 50, 25, 10, 5);
    $usage2 = new Usage(50, 25, 10, 5, 0);

    $usage1->add($usage2);

    expect($usage1->promptTokens)->toBe(100)
        ->and($usage1->completionTokens)->toBe(50);
});

test('usage to array includes all token fields', function (): void {
    $usage = new Usage(100, 50, 25, 10, 5);

    $array = $usage->toArray();

    expect($array['prompt_tokens'])->toBe(100)
        ->and($array['completion_tokens'])->toBe(50)
        ->and($array['cache_write_input_tokens'])->toBe(25)
        ->and($array['cache_read_input_tokens'])->toBe(10)
        ->and($array['reasoning_tokens'])->toBe(5);
});

test('usage json serialize returns to array', function (): void {
    $usage = new Usage(200, 100, 50, 20, 10);

    $json = $usage->jsonSerialize();

    expect($json['prompt_tokens'])->toBe(200)
        ->and($json['completion_tokens'])->toBe(100);
});

test('usage adds costs without mutating the original values', function (?float $first, ?float $second, ?float $expected): void {
    $usage = new Usage(cost: $first);
    $other = new Usage(cost: $second);

    $combined = $usage->add($other);

    expect($combined->cost)->toBe($expected)
        ->and($usage->cost)->toBe($first)
        ->and($other->cost)->toBe($second);
})->with([
    'unknown costs' => [null, null, null],
    'initial unknown cost' => [null, 0.25, 0.25],
    'subsequent unknown cost' => [0.25, null, 0.25],
    'known costs' => [0.25, 0.5, 0.75],
    'zero cost' => [0.0, 0.0, 0.0],
]);

test('usage serializes cost alongside token counts', function (?float $cost): void {
    $usage = new Usage(100, 50, 25, 10, 5, cost: $cost);

    expect($usage->toArray())->toBe([
        'prompt_tokens' => 100,
        'completion_tokens' => 50,
        'cache_write_input_tokens' => 25,
        'cache_read_input_tokens' => 10,
        'reasoning_tokens' => 5,
        'cost' => $cost,
    ])->and(json_decode(json_encode($usage), true)['cost'])->toEqual($cost);
})->with([null, 0.0, 0.25]);
