<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * @param  array<string, string>  $items  label => url
     * @return array<int, array{label: string, url: string}>
     */
    protected function breadcrumbs(array $items): array
    {
        return collect(['Home' => route('home')] + $items)
            ->map(fn (string $url, string $label) => ['label' => $label, 'url' => $url])
            ->values()
            ->all();
    }
}
