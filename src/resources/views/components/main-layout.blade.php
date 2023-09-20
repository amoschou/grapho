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

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css" integrity="sha512-Ya9H+OPj8NgcQk34nCrbehaA0atbzGdZCI2uCbqVRELgnlrh8vQ2INMnkadVMSniC54HChLIh5htabVuKJww8g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
        </style>
    </head>
    <body>
        <div>
            <header>
                <h2>{{ config('app.name', 'Laravel') }}</h2>
            </header>
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
                        <textarea rows="6" cols="80" name="comment" style="display: block; width: 100%;"></textarea>
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
        </div>
        <script>
            document.querySelectorAll('blockquote > p:first-child').forEach((p) => {
                if (p.innerHTML.startsWith('[!NOTE]<br>')) {
                    p.innerHTML = p.innerHTML.replace('[!NOTE]<br>', '<strong>Note:</strong><br>');
                } else if (p.innerHTML.startsWith('[!IMPORTANT]<br>')) {
                    p.innerHTML = p.innerHTML.replace('[!IMPORTANT]<br>', '<strong>Important:</strong><br>');
                } else if (p.innerHTML.startsWith('[!WARNING]<br>')) {
                    p.innerHTML = p.innerHTML.replace('[!WARNING]<br>', '<strong>Warning:</strong><br>');
                }
            });
        </script>
    </body>
</html>


