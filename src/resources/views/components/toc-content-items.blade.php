@foreach ($node as $contentItem)
    @if ($loop->first) <ul> @endif

    <li>
        @if ($contentItem instanceof AMoschou\Grapho\App\Classes\DocFolder)
            <x-grapho::toc-folder :folder="$contentItem"></x-grapho::toc-folder>
        @endif

        @if ($contentItem instanceof AMoschou\Grapho\App\Classes\DocFile)
            <x-grapho::toc-file :file="$contentItem"></x-grapho::toc-file>
        @endif
    </li>

    @if ($loop->last) </ul> @endif
@endforeach
