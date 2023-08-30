<details>
    <summary>{{ $folder->getTitle() }}</summary>
    <x-grapho::toc-content-items :node="$folder->getChildren()"></x-grapho::toc-content-items>
</details>