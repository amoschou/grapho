<?php

namespace AMoschou\Grapho\App\Http\Controllers;

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;

class GraphoController extends Controller
{
    public static function tableOfContents () {
        $toc = (new DocFolder (config('grapho.source_path')))->getChildren();

        return $toc;
    }
}
