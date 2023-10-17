<?php
    $editLink = false;
    $breadcrumbs = false;
    $updateTime = false;
    $path = null;
    $comments = [];
    $online = false;
?>

<x-grapho::main-layout
    :editLink="$editLink"
    :breadcrumbs="$breadcrumbs"
    :updateTime="$updateTime"
    :path="$path"
    :comments="$comments"
    :online="$online"
>

<style>
    contents-list { padding-left: 15px; }
</style>

<h2>Table of contents</h2>

<?php
    $doc = new \AMoschou\Grapho\App\Classes\DocFolder(config('grapho.source_path'));

    echo implode("\n", $doc->listContents(wrap: ['div', 'div']));
?>

</x-grapho::main-layout>

