<?php

namespace AMoschou\Grapho\App\Classes\Traits;

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use Romans\Filter\IntToRoman;
use Illuminate\Support\Str;

trait DocFolderOrFile2
{
    private function realPathToRelativePath($realPath, $sourcePath)
    {
        if (! Str::of($realPath)->startsWith("{$sourcePath}/")) {
            return 'ERROR!';
        }

        return Str::of($realPath)->replaceFirst("{$sourcePath}/", '')->toString();
    }
}