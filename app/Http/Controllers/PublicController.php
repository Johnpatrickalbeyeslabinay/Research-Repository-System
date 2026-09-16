<?php

namespace App\Http\Controllers;

/**
 * The public-facing /public listing is the exact same query and view
 * logic as /repository, restricted to visitors who aren't even logged
 * in — see RepositoryController::listing() for the shared implementation.
 * Both controllers used to duplicate the same search/year-filter code
 * (and the same bugs) independently; this just picks the "anonymous"
 * branch of that one implementation.
 */
class PublicController extends Controller
{
    public function index()
    {
        return app(RepositoryController::class)->listing(publicOnly: true, view: 'public');
    }
}
