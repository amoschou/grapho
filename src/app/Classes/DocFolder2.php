<?php

namespace AMoschou\Grapho\App\Classes;

// use AMoschou\Grapho\App\Classes\DocFolder as LegacyDocFolder;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class DocFolder2
{
    use DocFolderOrFile2;

    private string $sourcePath;
    private string $relativePath;
    private string $fullPath;
    // private LegacyDocFolder $legacyDocFolder;
    private SplFileInfo $splFileInfo;
    private array $childrenTree;
    private $metadata;
    private $defaultOrd = 0;
    private $metadataFilename = '_index.yaml';

    public function __construct($relativePath)
    {
        $this->sourcePath = config('grapho.source_path');
        $this->relativePath = $relativePath;
        $this->fullPath = "{$this->sourcePath}/{$this->relativePath}";
        $this->splFileInfo = new SplFileInfo($this->fullPath);
        // $this->legacyDocFolder = new LegacyDocFolder($this->fullPath);
        $this->childrenTree = $this->constructChildrenTree();
        $this->constructMetadata(); // after new splFileInfo
    }

    public function getChildrenTree() { return $this->childrenTree; }
    public function getRelativePath() { return $this->relativePath; }
    private function getMetadata($key) { return $this->metadata[$key]; }
    public function getOrd() { return $this->getMetadata('ord'); }
    public function getSourcePath() { return $this->sourcePath; }
    private function constructMetadata()
    {
        $default = [
            'ord' => $this->defaultOrd,
            'title' => $this->splFileInfo->getFilename(),
        ];

        $metadataPath = $this->splFileInfo->getRealPath() . '/' . $this->metadataFilename;

        try {
            $this->metadata = array_merge($default, Yaml::parseFile($metadataPath));
        } catch (Throwable) {
            $this->metadata = $default;
        }
    }

    private function constructChildrenTree()
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => $this->splFileInfo->getRealPath(),
        ]);

        $ignore = ['.git', '.github', '_index.yaml', 'README.md'];

        $folderList = array_filter($disk->directories(), function ($el) use ($ignore) {
            return ! in_array($el, $ignore);
        });

        $fileList = array_filter($disk->files(), function ($el) use ($ignore) {
            return ! in_array($el, $ignore);
        });

        $orderedList = [];

        foreach ($folderList as $filename) {
            $relativePathToItem = $this->getRelativePath() . '/' . $filename;

            $ord = (new DocFolder2($relativePathToItem))->getOrd();

            $orderedList[$ord][] = $filename;
        }

        foreach ($fileList as $filename) {
            $relativePathToItem = $this->getRelativePath() . '/' . $filename;

            $ord = (new DocFile2($relativePathToItem))->getOrd();

            $orderedList[$ord][] = $filename;
        }

        ksort($orderedList);

        $orderedList = Arr::flatten($orderedList);

        $tree = [];

        foreach ($orderedList as $item) {
            $realPathToItem = $this->getRealPath() . '/' . $item;

            if (is_file($realPathToItem)) {
                $tree[$item] = null;
            }

            if (is_dir($realPathToItem)) {
                $relativePathToItem = $this->realPathToRelativePath($realPathToItem, $this->getSourcePath());

                $tree[$item] = (new DocFolder2($relativePathToItem))->getChildrenTree();
            }
        }

        return $tree;
    }
}
