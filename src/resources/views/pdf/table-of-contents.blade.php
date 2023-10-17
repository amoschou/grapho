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
    .contents-list > .contents-list-item > span {
        width: 48px;
        display: inline-block;
        text-align: right;
        margin-right: 16px;
    }

    .contents-list .contents-list {
        padding-left: 40px;
    }
</style>

<h2>Table of contents</h2>

<?php
    $doc = new \AMoschou\Grapho\App\Classes\DocFolder(config('grapho.source_path'));

    echo implode("\n", $doc->listContents());
?>

</x-grapho::main-layout>

