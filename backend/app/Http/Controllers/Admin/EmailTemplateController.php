<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Mews\Purifier\Facades\Purifier;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = \App\Models\EmailTemplate::all();
        return view('admin.email_templates.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);
        return view('admin.email_templates.edit', compact('template'));
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
