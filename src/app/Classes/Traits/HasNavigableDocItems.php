<?php

namespace AMoschou\Grapho\App\Classes\Traits;

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;

trait HasNavigableDocItems
{
    private $firstDocItem = null;

    private $previousDocItem = null;

    private $nextDocItem = null;

    private $lastDocItem = null;

    private ?DocFolder $parent = null;

    private $siblings = [];

    private $children = [];

    private $relativePath = null;

    public function withParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    public function withSiblings($siblings = [])
    {
        $this->siblings = $siblings;

        return $this;
    }

    private function constructNavigation($relativePath = null)
    {
        if (is_null($relativePath)) {
            $this->relativePath = '';
        } else {
            $this->relativePath = $relativePath . '/' . $this->getBasename('.md');
        }

        $this->children = $this->buildChildren();
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getSiblings()
    {
        return $this->siblings;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    private function buildChildren()
    {
        $tree_kv = [];
        $i = 0;
        foreach ($this->getTree() as $key => $val) {
            $absolutePathToItem = $this->getRealPath() . '/' . $key;
            $tree_kv[$i] = [
                'key' => $key,
                'val' => $val,
                'obj' => is_null($val)
                    ? (new DocFile ($absolutePathToItem, $this->getRelativePath()))->withParent($this)
                    : (
                        is_array($val)
                        ? (new DocFolder ($absolutePathToItem, $this->getRelativePath()))->withParent($this)
                        : null
                    ),
            ];
            $i++;
        }
        $firstDocIndex = 0;
        $lastDocIndex = $i - 1;

        $children = [];

        for ($i = 0; $i <= $lastDocIndex; $i++) {
            $absolutePathToItem = $this->getRealPath() . '/' . $tree_kv[$i]['key'];

            $currentDocIndex = $i;
            $previousDocIndex = max(0, $i - 1);
            $nextDocIndex = min($i + 1, $lastDocIndex);

            $siblings = [
                'first' => ($currentDocIndex === $firstDocIndex) || ($previousDocIndex === $firstDocIndex) ? null : $this->getRealPath() . '/' . $tree_kv[$firstDocIndex]['key'],
                'previous' => $currentDocIndex === $firstDocIndex ? null : $this->getRealPath() . '/' . $tree_kv[$previousDocIndex]['key'],
                'self' => $this->getRealPath() . '/' . $tree_kv[$currentDocIndex]['key'],
                'next' => $currentDocIndex === $lastDocIndex ? null : $this->getRealPath() . '/' . $tree_kv[$nextDocIndex]['key'],
                'last' => ($currentDocIndex === $lastDocIndex) || ($nextDocIndex === $lastDocIndex) ? null : $this->getRealPath() . '/' . $tree_kv[$lastDocIndex]['key'],
            ];

            $children[] = $tree_kv[$i]['obj']->withSiblings($siblings);
        }

        return $children;
    }

    public function getLabel($format = 'numeric')
    {
        if (is_null($this->getParent())) {
            return null;
        }

        $this->getRealPath(); // REAL PATH OF THIS ITEM

        $i = 0;
        $foundThis = false;
        foreach ($this->getParent()->getChildren() as $item) {
            if (! $foundThis) {
                $i++;
            }

            if (! $foundThis && ($item->getRealPath() === $this->getRealPath())) {
                $foundThis = true;
            }
        }

        if (! $foundItem) {
            return 'NOTFOUND';
        }

        $thisLabel = match ($format) {
            'numeric' => $i,
        };

        $parentLabel = $this->getParent()->getLabel();

        return is_null($parentLabel) ? $thisLabel : "{$parentLabel}.{$thisLabel}";
    }
}