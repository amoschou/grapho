<?php
    $editLink = false;
    $breadcrumbs = false;
    $updateTime = false;
    $path = null;
    $comments = [];
    $onlin = false;
?>

<x-grapho::main-layout
    :editLink="$editLink"
    :breadcrumbs="$breadcrumbs"
    :updateTime="$updateTime"
    :path="$path"
    :comments="$comments"
    :online="$online"
>

<h2>Table of contents</h2>

<?php
    $doc = new \AMoschou\Grapho\App\Classes\DocFolder(config('grapho.source_path'));

    echo $doc->listContents();
?>

</x-grapho::main-layout>

