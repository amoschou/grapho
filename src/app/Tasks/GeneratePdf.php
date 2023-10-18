<?php

namespace AMoschou\Grapho\App\Tasks;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Spatie\Async\Task;

class GeneratePdf extends Task;
{
    public function configure()
    {
        dd('x');

        require __DIR__ .'/../../../../../vendor/autoload.php';

        $app = require_once __DIR__ .'/../../../../../bootstrap/app.php';

        $kernel = $app->make(Kernel::class);

        $kernel->handle(
            $request = Request::capture()
        );
    }

    public function run()
    {
        dd('a');

        $tmpDir = TemporaryDirectory::make();

        $pdfs = [];
        $paths = [];

        foreach ($views as $tag => $view) {
            $path = $tmpDir->path($tag . '.pdf');
            $paths[] = $path;
            file_put_contents($path, WeasyPrint::prepareSource($view)->build()->getData());
        }

        $pdf = new Pdf($paths);

        $pdf->cat()->saveAs($tmpDir->path('out.pdf'));

        $pdf->send('out-async.pdf', false);

        $tmpDir->delete();
    }
}
