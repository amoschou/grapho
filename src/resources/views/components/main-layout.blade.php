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
                <p><a href="{{ route('grapho.path', ['path' => $path, 'pdf' => '']) }}">Download PDF</a></p>
                <hr>
            </nav>
            <section>{{ $slot }}</section>
            <footer>
                <hr>

                <p>
                    @if ($updateTime) Last updated: {{ $updateTime }} @endif &emsp;<a href="{{ $editLink }}" target="_blank">Edit this page</a>
                </p>

                <div>
                    <h4>Comments</h4>

                    <form method="POST" action="{{ is_null($path) ? route('grapho.home.comment.create') : route('grapho.path.comment.create', ['path' => $path]) }}">
                        @csrf
                        <textarea rows="6" cols="80" name="comment"></textarea>
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
    </body>
</html>


