<?php

namespace AMoschou\Grapho\App\Classes;

// use AMoschou\Grapho\App\Classes\DocFile as LegacyDocFile;
use SplFileInfo;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use Throwable;

class DocFile2
{
    public string $sourcePath;
    public string $relativePath;
    public string $fullPath;
    private $defaultOrd = 0;
    // public LegacyDocFile $legacyDocFile;
    private SplFileInfo $splFileInfo;

    public function __construct($relativePath)
    {
        $this->sourcePath = config('grapho.source_path');

        $this->relativePath = $relativePath;

        $this->fullPath = "{$this->sourcePath}/{$this->relativePath}";

        $this->splFileInfo = new SplFileInfo($this->fullPath);

        // $this->legacyDocFile = new LegacyDocFile($this->fullPath);

        $this->constructMetadata();
    }

    public function getOrd() { return $this->getMetadata('ord'); }
    private function getMetadata($key) { return $this->metadata[$key]; }
    private function constructMetadata()
    {
        $default = [
            'ord' => $this->defaultOrd,
            'title' => $this->splFileInfo->getBaseName('.md'),
        ];

        try {
            $this->metadata = array_merge(
                $default,
                (new FrontMatterParser(new SymfonyYamlFrontMatterParser()))
                    ->parse(file_get_contents($this->splFileInfo->getRealPath()))
                    ->getFrontMatter()
            );
        } catch (Throwable) {
            $this->metadata = $default;
        }
    }
}
