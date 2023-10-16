<?php

namespace AMoschou\Grapho\App\Classes;

use SplFileInfo;

use AMoschou\CommonMark\Alert\AlertExtension;
use AMoschou\Grapho\App\Models\GraphoComment;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
// use League\CommonMark\Parser\MarkdownParser;
// use League\CommonMark\Renderer\HtmlRenderer;
use Illuminate\Support\Carbon;
use WeasyPrint\Facade as WeasyPrint;

class DocNodeFile
{
    private string $relativePathWithNoSuffix;
    private string $sourcePath;
    private string $pdfPath;
    private string $absolutePathWithNoSuffix;
    private string $absolutePathWithDotMd;
    private string $absolutePathWithDotPdf;
    private SplFileInfo $pdfFile;
    private SplFileInfo $mdFile;
    private ?array $breadcrumbs = null;
    private ?string $mdContent = null;
    private ?string $htmlContent = null;
    private ?string $renderablePdf = null;
    private ?string $renderableOnline = null;
    private array $pathArray;

    public function __construct($relativePathWithNoSuffix)
    {
        $this->relativePathWithNoSuffix = $relativePathWithNoSuffix;
        $this->sourcePath = $this->getSourcePath();
        $this->pdfPath = $this->getPdfPath();
        $this->absolutePathWithNoSuffix = "{$this->sourcePath}/{$this->relativePathWithNoSuffix}";
        $this->absolutePathWithDotMd = "{$this->sourcePath}/{$this->relativePathWithNoSuffix}.md";
        $this->absolutePathWithDotPdf = "{$this->pdfPath}/{$this->relativePathWithNoSuffix}.pdf";
        $this->mdFile = new SplFileInfo($this->absolutePathWithDotMd);
        $this->pdfFile = new SplFileInfo($this->absolutePathWithDotPdf);
        $this->pathArray = explode('/', $this->relativePathWithNoSuffix);
    }

    public function getAbsolutePathWithNoSuffix()
    {
        return $this->absolutePathWithNoSuffix;
    }

    public function getAbsolutePathWithDotMd()
    {
        return $this->absolutePathWithDotMd;
    }

    private function pdfFileIsStale()
    {
        if (! $this->getPdfFile()->isFile()) {
            return true;
        }

        $mdTime = $this->getMdFile()->getMTime();

        $pdfTime = $this->getPdfFile()->getMTime();

        if ($pdfTime < $mdTime) {
            return true;
        }

        return false;
    }

    public function refreshPdfFile()
    {
        if ($this->pdfFileIsStale()) {
            $this->savePdfFile();
        }
    }

    public static function getSourcePath()
    {
        return config('grapho.source_path');
    }

    public static function getPdfPath()
    {
        return config('grapho.pdf_path');
    }

    private function setBreadcrumbs()
    {
        $pathArray = $this->pathArray;

        $breadcrumbs = [];

        for ($i = 1; $i < count($pathArray); $i++) {
            $partialPath = implode('/', array_slice($pathArray, 0, $i));

            $filename =  self::getSourcePath() . '/' . $partialPath;

            $breadcrumbs[] = [
                'partial-path' => $partialPath,
                'title' => is_file("{$filename}.md") ? (new DocFile("{$filename}.md"))->getTitle() : (new DocFolder($filename))->getTitle()
            ];
        }

        $this->breadcrumbs = $breadcrumbs;
    }

    public function getBreadcrumbs()
    {
        if (is_null($this->breadcrumbs)) {
            $this->setBreadcrumbs();
        }

        return $this->breadcrumbs;
    }

    public function getMdFile()
    {
        return $this->mdFile;
    }

    public function getPdfFile()
    {
        return $this->pdfFile;
    }

    private function setMdContent()
    {
        $this->mdContent = (new FrontMatterExtension())
            ->getFrontMatterParser()
            ->parse(file_get_contents($this->absolutePathWithDotMd))
            ->getContent();
    }

    public function getMdContent()
    {
        if (is_null($this->mdContent)) {
            $this->setMdContent();
        }

        return $this->mdContent;
    }

    public function savePdfFile()
    {
        $output = WeasyPrint::prepareSource($this->getRenderable('pdf'))->build();

        file_put_contents($this->absolutePathWithDotPdf, $output->getData());
    }

    public function getHtmlContent()
    {
        if (is_null($this->htmlContent)) {
            $this->setHtmlContent();
        }

        return $this->htmlContent;
    }

    public function setHtmlContent($option = 'gfm')
    {
        // THERE ARE THREE OPTIONS TO CONVERT MD TO HTML
        // INDICATED BY 'gfm', 'ext' and 'api'.

        $md = $this->getMdContent();

        $htmlContent = match ($option) {
            'gfm' => (new GithubFlavoredMarkdownConverter())->convert($md),
            'ext' => (
                    new MarkdownConverter(
                        (new Environment([]))
                            ->addExtension(new CommonMarkCoreExtension())
                            ->addExtension(new GithubFlavoredMarkdownExtension())
                            ->addExtension(new AlertExtension())
                    )
                )->convert($md),
            'api' => Http::accept('application/vnd.github+json')
                ->withToken(config('grapho.github_api_token'))
                ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
                ->post('https://api.github.com/markdown', ['text' => $md, 'mode' => 'gfm'])
                ->body(),
        };

        $this->htmlContent = $htmlContent;
    }

    public function getRenderable($option = 'online')
    {
        if ($option === 'online') {
            if (is_null($this->renderableOnline)) {
                $this->setRenderable(['online' => true]);
            }

            return $this->renderableOnline;
        }

        if ($option === 'pdf') {
            if (is_null($this->renderablePdf)) {
                $this->setRenderable(['pdf' => true]);
            }

            return $this->renderablePdf;
        }
    }

    public function setRenderable($options = [])
    {
        $defaultOptions = ['pdf' => false, 'online' => true];

        $options = array_merge($defaultOptions, $options);

        $htmlContent = $this->getHtmlContent();
        $editLink = 'https://github.com/' . config('grapho.github_repo') . "/edit/main/{$this->relativePathWithNoSuffix}.md";
        $breadcrumbs = $this->getBreadcrumbs();
        $updateTime = Carbon::createFromTimestamp($this->getMdFile()->getMTime())->setTimezone('Australia/Adelaide')->format('g:i A, j F Y');
        $comments = GraphoComment::where('path', $this->relativePathWithNoSuffix)->orderBy('created_at')->get();
        $path = $this->relativePathWithNoSuffix;

        if ($options['pdf']) {
            $this->renderablePdf = view('grapho::page', [
                'online' => false,
                'htmlContent' => $htmlContent,
                'editLink' => $editLink,
                'breadcrumbs' => $breadcrumbs,
                'updateTime' => $updateTime,
                'comments' => $comments,
                'path' => $path,
            ]);
        }

        if ($options['online']) {
            $this->renderableOnline = view('grapho::page', [
                'online' => true,
                'htmlContent' => $htmlContent,
                'editLink' => $editLink,
                'breadcrumbs' => $breadcrumbs,
                'updateTime' => $updateTime,
                'comments' => $comments,
                'path' => $path,
            ]);
        }
    }

    public function openPdf()
    {
        return Storage::build([
            'driver' => 'local',
            'root' => config('grapho.pdf_path'),
        ])::download($this->relativePathWithNoSuffix);
    }
}
