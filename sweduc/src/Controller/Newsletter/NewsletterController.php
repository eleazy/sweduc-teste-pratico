<?php

namespace App\Controller\Newsletter;

use App\Model\Newsletter\Post;
use App\Model\Newsletter\VistoPost;
use App\Controller\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
//use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Core\Usuario;

class NewsletterController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get()->map(function ($post) {
            $post->formatted_date = $post->created_at->format('d/m/Y');
            return $post;
        });

        return $this->platesView('Newsletter/Index', compact('posts'));
    }

    public function novas(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = Usuario::fromSession();
        $userId = $usuario->id;

        $viewedPostIds = VistoPost::where('usuario_id', $userId)
            ->where('visualizou', true)
            ->pluck('post_id')
            ->toArray();

        $countNaoVisualizado = Post::whereNotIn('id', $viewedPostIds)->count();

        return $this->jsonResponse([
            'success' => true,
            'count_nao_visualizado' => $countNaoVisualizado
        ]);
    }

    public function marcarvisto(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = Usuario::fromSession();
        $userId = $usuario->id;

        $postIds = Post::pluck('id')->toArray();

        $alreadyViewed = VistoPost::where('usuario_id', $userId)
            ->pluck('post_id')
            ->toArray();

        $newViews = array_diff($postIds, $alreadyViewed);

        foreach ($newViews as $postId) {
            VistoPost::create([
                'usuario_id' => $userId,
                'post_id' => $postId,
                'visualizou' => true,
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'marcados_como_vistos' => count($newViews),
        ]);
    }
}
