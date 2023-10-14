<?php

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use AMoschou\Grapho\App\Http\Controllers\GraphoController;
use AMoschou\Grapho\App\Http\Controllers\CommentController;
use AMoschou\Grapho\App\Http\Controllers\PdfController;
use AMoschou\Grapho\App\Models\GraphoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use WeasyPrint\Facade as WeasyPrint;

use AMoschou\CommonMark\Alert\AlertExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
// use League\CommonMark\Parser\MarkdownParser;
// use League\CommonMark\Renderer\HtmlRenderer;

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
    Route::get('/pdf/section/{section}', [PdfController::class, 'section']);

    Route::get('/', function () {
        $tocNode = GraphoController::tableOfContents();

        $comments = GraphoComment::where('path', '')->orderBy('created_at')->get();

        $pdf = array_key_exists('pdf', request()->query());

        $renderable = view('grapho::page', [
            'online' => ! $pdf,
            'htmlContent' => '',
            'tocNode' => $tocNode,
            'editLink' => '#',
            'breadcrumbs' => [],
            'updateTime' => null,
            'comments' => $comments,
            'path' => null,
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

        if ($pdf) {
            $path = Str::replaceLast('.pdf', '', $path);
        }

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

        $md = $result->getContent();

        // THERE ARE THREE OPTIONS TO CONVERT MD TO HTML

        $option = 1;

        if ($option === 1) {
            $htmlContent = (new GithubFlavoredMarkdownConverter())->convert($md);
        }

        if ($option === 2) {
            $htmlContent = (
                new MarkdownConverter(
                    (new Environment([]))
                        ->addExtension(new CommonMarkCoreExtension())
                        ->addExtension(new GithubFlavoredMarkdownExtension())
                        ->addExtension(new AlertExtension())
                )
            )->convert($md);
        }

        if ($option === 3) {
            // Todo: Somehow include cURL "-L" option here.
            $htmlContent = Http::accept('application/vnd.github+json')
            ->withToken(config('grapho.github_api_token'))
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->post('https://api.github.com/markdown', ['text' => $md, 'mode' => 'gfm'])
            ->body();
        }

        $editLink = 'https://github.com/' . config('grapho.github_repo') . "/edit/main/{$path}.md";

        $updateTime = Carbon::createFromTimestamp((new DocFile($absolutePath))->getMTime())->setTimezone('Australia/Adelaide')->format('g:i A, j F Y');

        // $tocNode = GraphoController::tableOfContents();

        $comments = GraphoComment::where('path', $path)->orderBy('created_at')->get();

        $renderable = view('grapho::page', [
            'online' => ! $pdf,
            'htmlContent' => $htmlContent,
            'editLink' => $editLink,
            'breadcrumbs' => $breadcrumbs,
            'updateTime' => $updateTime,
            'comments' => $comments,
            'path' => $path,
        ]);

        if ($pdf) {
            $output = WeasyPrint::prepareSource($renderable)->build();

            $pdfFilename = $pathArray[count($pathArray) - 1] . '.pdf';

            return $output->inline($pdfFilename);
        }

        return $renderable;
    })->where('path', '.+')->name('path');

    Route::post('/', [CommentController::class, 'postHome'])->name('home.comment.create');

    Route::post('/{path}', [CommentController::class, 'postPath'])->where('path', '.+')->name('path.comment.create');
});
