<?php

namespace AMoschou\Grapho\App\Classes;

// use AMoschou\Grapho\App\Classes\Traits\HasNavigableDocItems;
// use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
// use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;

class PdfFile extends DocFile
{
    public function __construct($filename, $relativePath = null)
    {
        parent::__construct($filename, $relativePath);

        $pathWithDotPdf = "{$path}.pdf";

        $disk = self::getPdfPath();

        $disk->makeDirectory('');

        if (! $disk->exists($pathWithDotPdf)) {
            $this->generatePdf();

            $regeneratePdf = true;
        }

        parent::__construct($pdfAbsolutePath);
    }

    public static function getPdfPath()
    {
        return Storage::build([
            'driver' => 'local',
            'root' => config('grapho.pdf_path'),
        ]);
    }

    private function generatePdf()
    {

    }
}
