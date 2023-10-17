<?php
    $editLink = false;
    $breadcrumbs = false;
    $updateTime = false;
    $path = null;
    $comments = [];
    $online = false;
    $label = null;
    $title = 'Table of contents';
?>

<x-grapho::main-layout
    :editLink="$editLink"
    :breadcrumbs="$breadcrumbs"
    :updateTime="$updateTime"
    :path="$path"
    :comments="$comments"
    :online="$online"
    :label="$label"
    :title="$title"
>

<style>
    .contents-list > .contents-list-item > span {
        width: 64px;
        display: inline-block;
        text-align: right;
        margin-right: 16px;
        font-size: small;
        color: gray;
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

