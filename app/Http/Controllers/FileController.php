<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class FileController extends Controller
{
    //  * LIST
    public function index()
    {
        $files = File::latest('added_timestamp')->get();

        return view('files.index', compact('files'));
    }

    //  * SHOW (VIEW / DOWNLOAD)
    // public function show(File $file)
    // {
    //     // If Google Drive
    //     if ($file->google_drive_id) {
    //         return redirect()->away(
    //             "https://drive.google.com/file/d/{$file->google_drive_id}/view"
    //         );
    //     }

    //     abort(404);
    // }
//  public function show(File $file)
// {
//     $disk = Storage::disk('private');

//     abort_if(
//         !$file->file_name || !$disk->exists($file->file_name),
//         404
//     );

//     return response()->file(
//         $disk->path($file->file_name)
//     );
// }
public function show(File $file)
{
    abort_unless(
        Storage::disk('private')->exists($file->file_name),
        404
    );

    return response()->file(
        Storage::disk('private')->path($file->file_name)
    );
}

    //  * CREATE FORM
    public function create()
    {
        return view('files.create');
    }

    //  * STORE
    public function store(Request $request)
    {
        $request->validate([
            'file'  => 'required|file|max:10240',
            'name'  => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $uploaded = $request->file('file');

        //  Replace this later with Google Drive service
        $path = $uploaded->store('uploads', 'public');

        File::create([
            'google_drive_id'  => null,
            'google_drive_md5' => md5_file($uploaded->getRealPath()),
            'name'             => $request->name,
            'notes'            => $request->notes,
            'file_name'        => $uploaded->getClientOriginalName(),
            'file_type'        => $uploaded->getClientMimeType(),
            'added_timestamp'  => now(),
            'added_by_user'    => auth()->id(),
            'slug'             => Str::random(24),
        ]);

        return redirect()->back()->with('success', 'File uploaded');
    }

    //  * DELETE
    public function destroy(File $file)
    {
        $file->delete();

        return response()->json(['success' => true]);
    }
}
?>