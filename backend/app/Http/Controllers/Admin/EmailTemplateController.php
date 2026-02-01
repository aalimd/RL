<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;

use Mews\Purifier\Facades\Purifier;

class EmailTemplateController extends Controller
{
    private function getSettings()
    {
        return Settings::all()->pluck('value', 'key')->toArray();
    }

    public function index()
    {
        $settings = $this->getSettings();
        $templates = \App\Models\EmailTemplate::all();
        return view('admin.email-templates.index', compact('templates', 'settings'));
    }

    public function edit($id)
    {
        $settings = $this->getSettings();
        $template = \App\Models\EmailTemplate::findOrFail($id);
        return view('admin.email-templates.edit', compact('template', 'settings'));
    }

    public function update(Request $request, $id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $cleanBody = Purifier::clean($request->body);

        $template->update([
            'subject' => $request->subject,
            'body' => $cleanBody,
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }
}
