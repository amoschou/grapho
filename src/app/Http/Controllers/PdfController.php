<?php

namespace AMoschou\Grapho\App\Http\Controllers;

class PdfController
{
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
