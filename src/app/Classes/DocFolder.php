<?php

namespace AMoschou\Grapho\App\Classes;

use AMoschou\Grapho\App\Classes\Traits\HasNavigableDocItems;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class DocFolder extends SplFileInfo
{
    use HasNavigableDocItems;

    private $metadataFilename = '_index.yaml';

    private $metadata = null;

    private $defaultOrd = 0;

    private $tree = null;

    public function __construct(string $filename, $relativePath = null)
    {
        parent::__construct($filename);

        $this->constructMetadata();

        $this->constructTree();

        $this->constructNavigation($relativePath);
    }

    private function constructMetadata()
    {
        $default = [
            'ord' => $this->defaultOrd,
            'title' => $this->getFilename(),
        ];

        $metadataPath = $this->getRealPath() . '/' . $this->metadataFilename;

        try {
            $this->metadata = array_merge($default, Yaml::parseFile($metadataPath));
        } catch (\Throwable) {
            $this->metadata = $default;
        }
    }

    private function constructTree()
    {
        $this->tree = $this->buildTreeRecursively();
    }

    private function getMetadata($key)
    {
        return $this->metadata[$key];
    }

    public function getOrd()
    {
        return $this->getMetadata('ord');
    }

    public function getTitle()
    {
        return $this->getMetadata('title');
    }

    public function getTree()
    {
        return $this->tree;
    }

    private function buildTreeRecursively()
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => $this->getRealPath(),
        ]);

        $ignore = ['.git', '.github', '_index.yaml'];

        $folderList = array_filter($disk->directories(), function ($el) use ($ignore) {
            return ! in_array($el, $ignore);
        });

        $fileList = array_filter($disk->files(), function ($el) use ($ignore) {
            return ! in_array($el, $ignore);
        });

        $orderedList = [];

        foreach ($folderList as $filename) {
            $absolutePathToItem = $this->getRealPath() . '/' . $filename;

            $ord = (new DocFolder ($absolutePathToItem))->getOrd();

            $orderedList[$ord][] = $filename;
        }

        foreach ($fileList as $filename) {
            $absolutePathToItem = $this->getRealPath() . '/' . $filename;

            $ord = (new DocFile ($absolutePathToItem))->getOrd();

            $orderedList[$ord][] = $filename;
        }

        ksort($orderedList);

        $orderedList = Arr::flatten($orderedList);

        $tree = [];

        foreach ($orderedList as $item) {
            $absolutePathToItem = $this->getRealPath() . '/' . $item;

            if (is_file($absolutePathToItem)) {
                $tree[$item] = null;
            }

            if (is_dir($absolutePathToItem)) {
                $tree[$item] = (new DocFolder($absolutePathToItem))->tree;
            }
        }

        return $tree;
    }

    public function listContents($maxDepth = null, $currentLevel = 0, $wrap = true, $returnAsString = false)
    {
        $listItems = [];

        if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
            $children = [];

            foreach ($this->getChildren() as $child) {
                $currentLevel++;

                if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
                    $children[] = str_repeat(' ', 4 * $currentLevel) . '<li>' . $child->getTitle() . '</li>';
                }

                if ($child instanceof DocFolder) {
                    $children = array_merge($children, $child->listContents($maxDepth, $currentLevel, true, false));
                }

                $currentLevel--;
            }

            if (count($children) > 0) {
                $listItems = [
                    str_repeat(' ', 4 * $currentLevel) . '<ol class="level-' . $currentLevel . '">',
                    ...$children,
                    str_repeat(' ', 4 * $currentLevel) . '</ol>',
                ];
            }
        }

        return $returnAsString ? implode("\n", $listItems) : $listItems;
    }

    // public function objectTree()
    // {
    //     foreach ($this->getTree() as $key => $val) {
    //         $absolutePathToItem = $this->getRealPath() . '/' . $key;

    //         if (is_null($val)) /* if is file */ {
    //             $this->children[] = (new DocFile ($absolutePathToItem))->withParent($this);
    //         }

    //         if (is_array($val)) /* if is folder */ {
    //             $this->children[] = (new DocFolder($absolutePathToItem))->withParent($this);
    //         }
    //     }
    // }
}
