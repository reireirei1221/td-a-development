<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use GuzzleHttp\Client;

class PostController extends Controller
{
    public function index(Post $post)
    {
        return view('posts/index')->with(['posts' => $post->getPaginateByLimit()]);
    }

    public function show(Post $post)
    {
        return view('posts/show')->with(['post' => $post]);
    }

    public function create(Category $category)
    {
        return view('posts/create')->with(['categories' => $category->get()]);
    }

    // public function store(Post $post, Request $request)
    // {
    //     $input = $request['post'];
    //     $post->fill($input)->save();
    //     return redirect('/posts/' . $post->id);
    // }

    // Post method
    public function store(Post $post, Request $request)
    {
        $input = $request['post'];

        // 認証キーが設定されている場合のみ翻訳する
        if (config('services.deepl.auth_key')) {
            $input['meaning'] = $this->translate($input['name']);
        }

         // 更新または追加するデータを指定した条件で取得する
        $existingPost = Post::where('name', $input['name'])->first();

        if ($existingPost) {
            // データが存在する場合はcountを1インクリメントする
            $existingPost->count += 1;
            $existingPost->save();
            return redirect('/posts/' . $existingPost->id);
        } else {
            // データが存在しない場合は保存する  
            $post->fill($input)->save();
            return redirect('/posts/' . $post->id);
        }
    }

    public function edit(Post $post)
    {
        return view('posts/edit')->with(['post' => $post]);
    }

    // Put method
    public function update(Request $request, Post $post)
    {
        $input_post = $request['post'];
        // dd($post['count']);
        $post->fill($input_post)->save();

        return redirect('/posts/' . $post->id);
    }

    // 翻訳結果を出力する
    public function translate(String $text)
    {
        $client = new Client();

        $response = $client->request('POST', 'https://api-free.deepl.com/v2/translate', [
            'form_params' => [
                'auth_key' => config('services.deepl.auth_key'),
                'text' => $text,
                'target_lang' => 'JA',
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        //return view('posts/translate')->with(['response' => $response]);
        return $response['translations'][0]['text'];
    }
}