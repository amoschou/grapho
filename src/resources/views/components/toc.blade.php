@php
    $tocNode = AMoschou\Grapho\App\Http\Controllers\GraphoController::tableOfContents();
@endphp

<x-grapho::toc-content-items :node="$tocNode" />