<?php

namespace AMoschou\Grapho\App\Http\Controllers;

use mikehaertl\pdftk\Pdf;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use WeasyPrint\Facade as WeasyPrint;

class PdfController
{
    public function pdf()
    {
        $tmpDir = TemporaryDirectory::make();

        $pdfFrontCover = WeasyPrint::prepareSource(view('grapho::pdf.front-cover'))->build();
        $pdfTableOfContents = WeasyPrint::prepareSource(view('grapho::pdf.table-of-contents'))->build();

        $files = [
            $tmpDir->path('front-cover.pdf') => $pdfFrontCover->getData(),
            $tmpDir->path('table-of-contents.pdf') => $pdfTableOfContents->getData(),
        ];

        foreach ($files as $file => $data) {
            file_put_contents($file, $data);
        }

        $pdf = (new Pdf(array_keys($files)))->cat()->saveAs($tmpDir->path('out.pdf'));

        $pdf->send('out.pdf', true);

        $tmpDir->delete();
    }

    public function section($section)
    {
        if ($section === 'front-cover') {
            return $this->frontCover();
        }

        if ($section === 'table-of-contents') {
            return $this->tableOfContents();
        }
    }

    private function frontCover()
    {
        return view('grapho::pdf.front-cover');
    }

    private function tableOfContents()
    {
        return view('grapho::pdf.table-of-contents');
    }
}
