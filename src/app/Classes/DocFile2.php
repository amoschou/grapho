<?php

namespace AMoschou\Grapho\App\Classes;

use AMoschou\Grapho\App\Classes\DocFile as LegacyDocFile;
use SplFileInfo;

class DocFile2
{
    public string $sourcePath;

    public string $relativePath;

    public string $fullPath;

    public LegacyDocFile $legacyDocFile;

    private SplFileInfo $splFileInfo;

    public function __construct($relativePath)
    {
        $this->sourcePath = config('grapho.source_path');

        $this->relativePath = $relativePath;

        $this->fullPath = "{$this->sourcePath}/{$this->relativePath}";

        $this->splFileInfo = new SplFileInfo($this->fullPath);

        $this->legacyDocFile = new LegacyDocFile($this->fullPath);
    }
}
