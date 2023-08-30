@props([
    'breadcrumbs'
])

<p>
    <a href="">Home</a>&ensp;{{
         '/' 
    }}@foreach ($breadcrumbs as $breadcrumb)&ensp;<a href="{{ route('grapho.path', ['path' => $breadcrumb['partial-path']]) }}">{{ 
        $breadcrumb['title'] 
    }}</a>&ensp;{{ 
        '/'
    }}@endforeach
</p>
