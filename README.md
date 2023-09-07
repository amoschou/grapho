# Grapho

Create book as a Laravel app from a folder of Markdown files.

## Installation

Require the package with:
```
composer require amoschou/grapho
```

Then publish the vendor files, if desired:
```
php artisan vendor:publish --tag=grapho-config
php artisan vendor:publish --tag=grapho-views
```

## Configuration

Create a new folder at `resources/src/grapho` (or where you configured the path) and place the Markdown files here.

The route prefix is inserted in the URLs for each page.

The GitHub repo is where the Markdown files are sourced from. This is information is used for 'Edit this page' buttons.

Browse to `example.com/docs` to see the rendered Markdown files.

## Structure of the Markdown folder

By default, the Markdown folder is located at `resources/src/grapho`. Place all Markdown files in folders, in a tree structure. All markdown files must have a `.md` suffix in the filename. The `.md` suffix is not allowed on a folder.

Markdown files can contain YAML front matter, with two key-value pairs `title` and `ord`:

```
---
title: The title of the page
ord: 3
---

# Continue writing markdown in this file.
```

The title is used in the table of contents and in breadcrumb navigation. The ord is a number that is used to order the files and folders. Without this information, the default title is taken to be the filename (without `.md` suffix) and the default ord is taken to be `0`. If more than one folder or file has the same ord, they are listed in no particular order (which is probably alphabetical order).

Folders can also have titles and orders defined with a `_index.yaml` file inside the folder.
