<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Support\Facades\Storage;

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

    public function viewPdf($id)
    {
        $file = UserFile::findOrFail($id);

        // Anonymous users can only view approved public research.
        abort_unless(
            $file->status === 'approved' &&
            $file->visibility === 'public',
            404
        );

        return Storage::disk(
            config('research.storage_disk', 'local')
        )->response($file->file_path);
    }
}
