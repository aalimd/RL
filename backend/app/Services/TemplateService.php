<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Facades\Cache;

class TemplateService
{
    /**
     * Get the active template (Cached)
     */
    public function getActiveTemplate(): ?Template
    {
        return Cache::remember('active_template', 3600, function () {
            return Template::where('is_active', true)->first();
        });
    }

    /**
     * Get all templates (Cached)
     */
    public function getAllTemplates()
    {
        return Cache::remember('all_templates', 1800, function () {
            return Template::orderBy('name')->get();
        });
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        Cache::forget('active_template');
        Cache::forget('all_templates');
    }
}
