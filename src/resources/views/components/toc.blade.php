@php
    $tocNode = AMoschou\Grapho\App\Http\Controllers\GraphoController::tableOfContents2();
@endphp

<x-grapho::toc-content-items :node="$tocNode" />