<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaDownloadController extends Controller
{
    public function __invoke(int $id): BinaryFileResponse
    {
        $media = Media::findOrFail($id);
        $slugName = Str::slug($media->name);
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
        $filename = "$slugName.$extension";

        return response()->file($media->getPath(), [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
