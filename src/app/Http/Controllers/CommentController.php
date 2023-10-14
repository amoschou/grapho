<?php

namespace AMoschou\Grapho\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController
{
    public function postHome(Request $request)
    {
        $validatedData = $request->validate([
            'comment' => ['required'],
        ]);

        DB::table('grapho_comments')->insert([
            'user_id' => Auth::id(),
            'path' => '',
            'comment' => $validatedData['comment'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('grapho.home');
    }

    public function postPath($path, Request $request)
    {
        $validatedData = $request->validate([
            'comment' => ['required'],
        ]);

        DB::table('grapho_comments')->insert([
            'user_id' => Auth::id(),
            'path' => $path,
            'comment' => $validatedData['comment'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('grapho.path', ['path' => $path]);
    }
}