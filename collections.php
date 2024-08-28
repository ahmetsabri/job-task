<?php
$employees = [
    ['name' => 'John', 'city' => 'Dallas'],
    ['name' => 'Jane', 'city' => 'Austin'],
    ['name' => 'Jake', 'city' => 'Dallas'],
    ['name' => 'Jill', 'city' => 'Dallas'],
];

$offices = [
    ['office' => 'Dallas HQ', 'city' => 'Dallas'],
    ['office' => 'Dallas South', 'city' => 'Dallas'],
    ['office' => 'Austin Branch', 'city' => 'Austin'],
];

$output = collect($offices)->groupBy('city')->transform(function ($key, $value) use ($employees) {
        return $key->keyBy('office')->transform(function () use ($value, $employees) {
            return collect($employees)->where('city', $value)->pluck('name');
        });
    });
