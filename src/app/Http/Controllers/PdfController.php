<?php

namespace AMoschou\Grapho\App\Http\Controllers;

use AMoschou\Grapho\App\Classes\DocFile;
use AMoschou\Grapho\App\Classes\DocFolder;
use AMoschou\Grapho\App\Tasks\GeneratePdf;
use mikehaertl\pdftk\Pdf;
use Spatie\Async\Pool;
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

        $pdf = new Pdf(array_keys($files));
        
        $pdf->cat()->saveAs($tmpDir->path('out.pdf'));

        $pdf->send('out.pdf', false);

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

    public function buildPdfs()
    {
        $doc = new DocFolder(config('grapho.source_path'));

        $arr = $doc->arrayContents();

        foreach ($arr as $page) {
            if ($page['child'] instanceof DocFolder) {
                // quietly ignore this for now
            }

            if ($page['child'] instanceof DocFile) {
                $docNodeFile = new DocNodeFile($page['relpath']);

                $output = WeasyPrint::prepareSource($docNodeFile->getRenderable('pdf'))->build();

                Storage::build([
                    'driver' => 'local',
                    'root' => config('grapho.pdf_path'),
                ])->put("build/{$page['relpath']}.pdf", $output->getData());
            }
        }

        dd('Done');
    }
}
