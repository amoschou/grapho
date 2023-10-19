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

        $ignore = ['.git', '.github', '_index.yaml', 'README.md'];

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

    public function listContents($currentLevel = 0, $maxDepth = null, $indent = '')
    {
        $bigIndent = "{$indent}    ";

        $listItems = [];

        if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
            $currentLevel++;

            foreach ($this->getChildren() as $child) {
                if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
                    $childLabel = $child->getLabel();
                    $childTitle = $child->getTitle();

                    $listItems[] = $bigIndent. "<div class=\"contents-list-item\"><span>{$childLabel}</span>{$childTitle}</div>";
                }

                if ($child instanceof DocFolder) {
                    $listItems = array_merge($listItems, $child->listContents($currentLevel, $maxDepth, $bigIndent));
                }
            }

            $currentLevel--;
        }

        return count($listItems) === 0 ? [] : [
            $indent . "<div class=\"contents-list\">",
            ...$listItems,
            $indent . '</div>',
        ];
    }

    public function arrayContents($currentLevel = 0, $maxDepth = null)
    {
        $listItems = [];

        if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
            $currentLevel++;

            foreach ($this->getChildren() as $child) {
                if (is_null($maxDepth) || ($currentLevel <= $maxDepth)) {
                    $childLabel = $child->getLabel();
                    $childTitle = $child->getTitle();

                    $listItems[] = [
                        'child' => $child,
                        'label' => $childLabel,
                        'title' => $childTitle,
                    ];
                }

                if ($child instanceof DocFolder) {
                    $listItems = array_merge($listItems, $child->arrayContents($currentLevel, $maxDepth));
                }
            }

            $currentLevel--;
        }

        return $listItems;
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
