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
    ol {
        counter-reset: section; /* Creates a new instance of the
                                    section counter with each ol
                                    element */
        list-style-type: none;
    }

    li::before {
        counter-increment: section; /* Increments only this instance
                                                    of the section counter */
        content: counters(section, ".") " "; /* Combines the values of all instances
                                                of the section counter, separated
                                                by a period */
    }

    .markdown-body ol {
        list-style-type: upper-roman;
    }

    .markdown-body ol ol,
    .markdown-body ol ol ol,
    .markdown-body ol ol ol ol,
    .markdown-body ol ol ol ol ol
    {
        list-style-type: numeric;
    }
</style>

<h2>Table of contents</h2>

<?php
    $doc = new \AMoschou\Grapho\App\Classes\DocFolder(config('grapho.source_path'));

    echo implode("\n", $doc->listContents());
?>

</x-grapho::main-layout>

