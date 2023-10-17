<?php

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use AMoschou\Grapho\App\Classes\DocNodeFile;
use AMoschou\Grapho\App\Http\Controllers\GraphoController;
use AMoschou\Grapho\App\Http\Controllers\CommentController;
use AMoschou\Grapho\App\Http\Controllers\PdfController;
use AMoschou\Grapho\App\Models\GraphoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use WeasyPrint\Facade as WeasyPrint;

// $middleware = match (config('grapho.starter_kit')) {
//     'breeze' => ['auth'],
//     'jetstream' => [
//         'auth:sanctum',
//         config('jetstream.auth_session'),
//         'verified',
//     ],
//     default => [],
// };

$middleware = config('grapho.middleware'); // Apply this explicitly. For future, apply middleware as above according to starter kit?

Route::middleware($middleware)->group(function () {
    Route::get('/pdf', [PdfController::class, 'pdf']);

    Route::get('/pdf/section/{section}', [PdfController::class, 'section']);

    Route::get('/', function () {
        $tocNode = GraphoController::tableOfContents();

        $comments = GraphoComment::where('path', '')->orderBy('created_at')->get();

        $pdf = array_key_exists('pdf', request()->query());

        $renderable = view('grapho::page', [
            'online' => ! $pdf,
            'htmlContent' => '<h1>' . config('app.name', 'Laravel') . '</h1>',
            'tocNode' => $tocNode,
            'editLink' => '#',
            'breadcrumbs' => [],
            'updateTime' => null,
            'comments' => $comments,
            'path' => null,
            'label' => 'AAAA',
            'title' => null,
        ]);

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
        $pdf = Str::endsWith($path, '.pdf');

        $relativePathWithNoSuffix = $pdf ? Str::replaceLast('.pdf', '', $path) : $path;

        $docNodeFile = new DocNodeFile($relativePathWithNoSuffix);

        if (is_dir($docNodeFile->getAbsolutePathWithNoSuffix())) {
            if ($pdf) {
                // CREATE PDF HERE.
            }

            return redirect()->route('grapho.home');
        }

        abort_if(! is_file($docNodeFile->getAbsolutePathWithDotMd()), 404);

        return $pdf
            ? $docNodeFile->openPdf()
            : $docNodeFile->getRenderable();
    })->where('path', '.+')->name('path');

    Route::post('/', [CommentController::class, 'postHome'])->name('home.comment.create');

    Route::post('/{path}', [CommentController::class, 'postPath'])->where('path', '.+')->name('path.comment.create');
});
