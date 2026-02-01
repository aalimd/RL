<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Mews\Purifier\Facades\Purifier;

class EmailTemplateController extends Controller
{
    // ... (index and edit methods unchanged)

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
