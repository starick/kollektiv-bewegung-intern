<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicProtocolResource;
use App\Markdown\ProtocolMarkdown;
use App\Models\Protocol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProtocolShareController extends Controller
{
    /**
     * Share the protocol publicly. Sharing again replaces the link, so the old one stops working.
     */
    public function store(Protocol $protocol)
    {
        Gate::authorize('update', $protocol);

        $protocol->share();

        // No flash message, the dialog reports it together with copying the link
        return back();
    }

    /**
     * Stop sharing the protocol, the link stops working.
     */
    public function destroy(Protocol $protocol)
    {
        Gate::authorize('update', $protocol);

        $protocol->unshare();

        return back()->with('success', __('Protocol is no longer shared.'));
    }

    /**
     * Show a shared protocol to anyone with the link, without logging in.
     */
    public function show(Request $request, string $token)
    {
        $protocol = Protocol::where('share_token', $token)->firstOrFail();

        return Inertia::render('Protocols/Public', [
            'protocol' => (new PublicProtocolResource($protocol))->resolve(),
            'html' => ProtocolMarkdown::toHtml($protocol->text),
            // Only team members who are logged in learn where the internal page is
            'internalUrl' => $request->user()?->can('view', $protocol) ? route('protocols.show', $protocol) : null,
            // The login continues on the internal page, see FortifyServiceProvider
            'loginUrl' => $request->user() ? null : route('login', ['shared' => $token]),
        ])->toResponse($request)->withHeaders([
            // Shared links are for the people they were sent to, not for search engines
            'X-Robots-Tag' => 'noindex, nofollow',
            // Links in the protocol must not pass the share token on to other sites
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
