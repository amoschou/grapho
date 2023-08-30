<?php

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use AMoschou\Grapho\App\Http\Controllers\GraphoController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

Route::prefix(config('grapho.route_prefix'))->name('grapho.')->group(function () {
    Route::get('/', function () {
        $tocNode = GraphoController::tableOfContents();

        return view('grapho::page', [
            'htmlContent' => '',
            'tocNode' => $tocNode,
            'editLink' => '#',
            'breadcrumbs' => [],
            'updateTime' => null,
        ]);
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

        return view('grapho::page', [
            'htmlContent' => $htmlContent,
            'editLink' => $editLink,
            'breadcrumbs' => $breadcrumbs,
            'updateTime' => $updateTime,
        ]);
    })->where('path', '.+')->name('path');
});
