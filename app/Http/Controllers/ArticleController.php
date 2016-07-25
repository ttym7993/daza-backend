<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\ArticleViewer;

use Auth;

use Illuminate\Http\Request;

use App\Http\Requests;

class ArticleController extends Controller
{

    public function __construct()
    {
        // 执行 auth 认证
        $this->middleware('auth', [
            'except' => [
                'index',
                'show'
            ]
        ]);
    }

    public function index(Request $request)
    {
        $params = $request->all();

        $columns = [
            'id',
            'user_id',
            'topic_id',
            'title',
            'summary',
            'image_url',
            'published_at',
        ];

        $query = Article::select($columns)
            ->with(['user', 'topic'])
            ->orderBy('published_at', 'desc');

        return $this->pagination($query->paginate());
    }

    public function store(Request $request)
    {
        $params = $request->all();

        $this->validate($request, [
            'topic_id'    => 'required|exists:topics,id',
            'title'       => 'required|min:6|max:255',
            'content'     => 'required',
            'author'      => 'min:2',
            'author_link' => 'url',
            'source'      => 'min:2',
            'source_link' => 'url',
        ]);

        $params = array_merge($params, ['user_id' => Auth::id()]);

        $data = Article::create($params);
        if ($data) {
            return $this->success($data);
        }
        return $this->failure();
    }

    public function show(Request $request, $article_id)
    {
        $request->merge(['article' => $article_id]);
        $this->validate($request, ['article' => 'exists:articles,id']);

        $data = Article::find($article_id);
        if (Auth::check()) {
            ArticleViewer::create([
                'user_id'    => Auth::id(),
                'article_id' => $data->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
        return $this->success($data);
    }

    public function update(Request $request)
    {
        return $this->failure();
    }

    public function destroy(Request $request)
    {
        return $this->failure();
    }

}
