<?php

namespace App\Http\Controllers;

use App\Support\HelpCenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(Request $request, HelpCenter $help): View
    {
        $search = $request->string('buscar')->trim()->toString();

        return view('help.index', [
            'topics' => $help->allowedTopics($request->user(), $search),
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $tema, HelpCenter $help): View
    {
        return view('help.show', [
            'topic' => $help->topicFor($request->user(), $tema),
        ]);
    }
}
