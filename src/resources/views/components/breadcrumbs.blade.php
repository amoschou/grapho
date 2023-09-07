@props([
    'breadcrumbs'
])

<p>
    <a href="{{ route('grapho.home') }}">Home</a>
    /
    @foreach ($breadcrumbs as $breadcrumb)
        <a href="{{ route('grapho.path', ['path' => $breadcrumb['partial-path']]) }}">{{ $breadcrumb['title'] }}</a>
        /
    @endforeach
</p>
