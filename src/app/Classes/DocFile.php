<?php

namespace AMoschou\Grapho\App\Classes;

use AMoschou\Grapho\App\Classes\Traits\HasNavigableDocItems;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use SplFileInfo;

class DocFile extends SplFileInfo
{
    use HasNavigableDocItems;

    private $metadata = null;

    private $defaultOrd = 0;

    public function __construct(string $filename, $relativePath = null)
    {
        parent::__construct($filename);

        $this->constructMetadata();

        $this->constructNavigation($relativePath);
    }

    private function constructMetadata()
    {
        $default = [
            'ord' => $this->defaultOrd,
            'title' => $this->getBaseName('.md'),
        ];

        try {
            $this->metadata = array_merge(
                $default,
                (new FrontMatterParser(new SymfonyYamlFrontMatterParser()))
                    ->parse(file_get_contents(self::getRealPath()))
                    ->getFrontMatter()
            );
        } catch (\Throwable) {
            $this->metadata = $default;
        }
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
        return [];
    }
}