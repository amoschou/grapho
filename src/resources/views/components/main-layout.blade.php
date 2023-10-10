@props([
    'editLink',
    'breadcrumbs',
    'updateTime',
    'path',
    'comments',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css" integrity="sha512-Ya9H+OPj8NgcQk34nCrbehaA0atbzGdZCI2uCbqVRELgnlrh8vQ2INMnkadVMSniC54HChLIh5htabVuKJww8g==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}

        {{-- <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" /> --}}

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">

        {{--
        <style>
            .markdown-body {
                box-sizing: border-box;
                min-width: 200px;
                max-width: 980px;
                margin: 0 auto;
                padding: 45px;
            }

            @media (max-width: 767px) {
                .markdown-body {
                    padding: 15px;
                }
            }

            .markdown-body div.md-alert {
                margin-top: 0;
                margin-bottom: 16px;
                margin-left: 0;
                margin-right: 0;
                padding: 0 1em;
                color: var(--color-fg-muted);
                border-left: 0.25em solid var(--color-border-default);
            }

            .markdown-body div.md-alert > span { font-weight: 500; }
            .markdown-body div.md-alert > span > svg { margin-right: 8px; }

            .markdown-body div.md-alert.md-alert-note { border-left-color: #0969DA; }
            .markdown-body div.md-alert.md-alert-important { border-left-color: #8250df; }
            .markdown-body div.md-alert.md-alert-warning { border-left-color: #9A6700; }

            .markdown-body .color-note { color: #0969DA; }
            .markdown-body .color-important { color: #8250df; }
            .markdown-body .color-warning { color: #9A6700; }

            .material-symbols-sharp { vertical-align: -6px; font-variation-settings: 'FILL' 1, 'wght' 600, 'GRAD' 0, 'opsz' 24; }

            body {
                font-family: 'IBM Plex Sans', sans-serif;
            }
        </style>
        --}}
        <style>
            @page {
                size: A4;
                margin: 14mm;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>{{ config('app.name', 'Laravel') }}</h1>

            <nav>
                <x-grapho::auth-links></x-grapho::auth-links>
                <x-grapho::toc></x-grapho::toc>
                <x-grapho::breadcrumbs :breadcrumbs="$breadcrumbs"></x-grahpo::breadcrumbs>
                <div>
                    @if (is_null($path))
                        <a href="{{ route('grapho.home', ['pdf' => 'inline']) }}">Download PDF</a>
                    @else
                        <a href="{{ route('grapho.path', ['path' => $path, 'pdf' => 'inline']) }}">Download PDF</a>
                    @endif
                </div>
                <hr>
            </nav>
        </header>
        <section class="markdown-body">{{ $slot }}</section>
        <footer>
            <hr>

            <p>
                @if ($updateTime) Last updated: {{ $updateTime }} @endif &emsp;<a href="{{ $editLink }}" target="_blank">Edit this page</a>
            </p>

            <div>
                <h4>Comments</h4>

                <form method="POST" action="{{ is_null($path) ? route('grapho.home.comment.create') : route('grapho.path.comment.create', ['path' => $path]) }}">
                    @csrf
                    <textarea rows="6" name="comment" style="display: block; width: calc(100% - 6px);"></textarea>
                    <button type="submit">Save</button>
                </form>

                @foreach ($comments as $comment)
                    <div>
                        <p><strong>{{ $comment->user->name }} ({{ $comment->created_at }}):</strong></p>
                        <p>{{ $comment->comment }}</p>
                    </div>
                @endforeach
            </div>
        </footer>
        {{--
        <script>
            document.querySelectorAll('blockquote > p:first-child').forEach((p) => {
                bq = p.parentElement;
                if (p.innerHTML.startsWith('[!NOTE]<br>')) {
                    bq.classList.add('note');
                    p.innerHTML = p.innerHTML.replace('[!NOTE]<br>', '<span class="color-note"><span class="material-symbols-sharp">info</span> <strong>Note:</strong></span><br>');
                } else if (p.innerHTML.startsWith('[!IMPORTANT]<br>')) {
                    bq.classList.add('important');
                    p.innerHTML = p.innerHTML.replace('[!IMPORTANT]<br>', '<span class="color-important"><span class="material-symbols-sharp">feedback</span> <strong>Important:</strong></span><br>');
                } else if (p.innerHTML.startsWith('[!WARNING]<br>')) {
                    bq.classList.add('warning');
                    p.innerHTML = p.innerHTML.replace('[!WARNING]<br>', '<span class="color-warning"><span class="material-symbols-sharp">warning</span> <strong>Warning:</strong></span><br>');
                }
            });
        </script>
        --}}
    </body>
</html>


