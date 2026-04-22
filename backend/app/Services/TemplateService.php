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
        $attributes = Cache::remember('active_template_v2', 3600, function () {
            return Template::where('is_active', true)->first()?->getAttributes();
        });

        if (!is_array($attributes)) {
            return null;
        }

        return Template::newFromBuilder($attributes);
    }

    /**
     * Get all templates (Cached)
     */
    public function getAllTemplates()
    {
        $templates = Cache::remember('all_templates_v2', 1800, function () {
            return Template::orderBy('name')
                ->get()
                ->map(static fn (Template $template) => $template->getAttributes())
                ->all();
        });

        return Template::hydrate(is_array($templates) ? $templates : []);
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
}
