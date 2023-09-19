<?php

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use AMoschou\Grapho\App\Http\Controllers\GraphoController;
use AMoschou\Grapho\App\Models\GraphoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use WeasyPrint\Facade as WeasyPrint;

$middleware = match (config('grapho.starter_kit')) {
    'breeze' => ['auth'],
    'jetstream' => [
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ],
    default => [],
};

$middleware = config('grapho.middleware'); // Apply this explicitly. For future, apply middleware as above according to starter kit?

Route::middleware($middleware)->group(function () {
    Route::get('/', function () {
        $tocNode = GraphoController::tableOfContents();

        $comments = GraphoComment::where('path', '')->orderBy('created_at')->get();

        $renderable = view('grapho::page', [
            'htmlContent' => '',
            'tocNode' => $tocNode,
            'editLink' => '#',
            'breadcrumbs' => [],
            'updateTime' => null,
            'comments' => $comments,
            'path' => null,
        ]);

        $pdf = array_key_exists('pdf', request()->query());

        if ($pdf) {
            $method = request()->query('pdf', 'inline');

            $output = WeasyPrint::prepareSource($renderable)->build();

            $pdfFilename = 'home.pdf';

            return match ($method) {
                'download' => $output->download($pdfFilename),
                default => $output->inline($pdfFilename),
            };
        }

        return $renderable;
    })->name('home');

    Route::get('/{path}', function (string $path) {
        $absolutePath = config('grapho.source_path') . '/' . $path;

        if (is_dir($absolutePath)) {
            return redirect()->route('grapho.home');
        }

        $absolutePath .= '.md';

        if (! is_file($absolutePath)) {
            abort(404);
        }

        $pathArray = explode('/', $path);

        $breadcrumbs = [];

        for ($i = 1; $i < count($pathArray); $i++) {
            $partialPath = implode('/', array_slice($pathArray, 0, $i));

            $filename =  config('grapho.source_path') . '/' . $partialPath;

            $breadcrumbs[] = [
                'partial-path' => $partialPath,
                'title' => is_file("{$filename}.md") ? (new DocFile("{$filename}.md"))->getTitle() : (new DocFolder($filename))->getTitle()
            ];
        }

        $result = (new FrontMatterExtension())->getFrontMatterParser()->parse(file_get_contents($absolutePath));

        $htmlContent = (new GithubFlavoredMarkdownConverter())->convert($result->getContent());

        $editLink = 'https://github.com/' . config('grapho.github_repo') . "/edit/main/{$path}.md";

        $updateTime = Carbon::createFromTimestamp((new DocFile($absolutePath))->getMTime())->setTimezone('Australia/Adelaide')->format('g:i A, j F Y');

        $tocNode = GraphoController::tableOfContents();

        $comments = GraphoComment::where('path', $path)->orderBy('created_at')->get();

        $renderable = view('grapho::page', [
            'htmlContent' => $htmlContent,
            'editLink' => $editLink,
            'breadcrumbs' => $breadcrumbs,
            'updateTime' => $updateTime,
            'comments' => $comments,
            'path' => $path,
        ]);

        $pdf = array_key_exists('pdf', request()->query());

        if ($pdf) {
            $method = request()->query('pdf', 'inline');

            $output = WeasyPrint::prepareSource($renderable)->build();

            $pdfFilename = $pathArray[count($pathArray) - 1] . '.pdf';

            return match ($method) {
                'download' => $output->download($pdfFilename),
                default => $output->inline($pdfFilename),
            };
        }

        return $renderable;
    })->where('path', '.+')->name('path');

    Route::post('/', function (Request $request) {
        $validatedData = $request->validate([
            'comment' => ['required'],
        ]);

        DB::table('grapho_comments')->insert([
            'user_id' => Auth::id(),
            'path' => '',
            'comment' => $validatedData['comment'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('grapho.home');
    })->name('home.comment.create');

    Route::post('/{path}', function ($path, Request $request) {
        $validatedData = $request->validate([
            'comment' => ['required'],
        ]);

        DB::table('grapho_comments')->insert([
            'user_id' => Auth::id(),
            'path' => $path,
            'comment' => $validatedData['comment'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('grapho.path', ['path' => $path]);
    })->where('path', '.+')->name('path.comment.create');
});
