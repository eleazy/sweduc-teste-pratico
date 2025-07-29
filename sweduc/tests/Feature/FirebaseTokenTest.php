<?php

use App\Academico\Model\FirebaseToken;
use App\Framework\Middleware\ValidateOAuthMiddleware;
use App\Model\Core\Usuario;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function App\Framework\app;
use function Pest\Faker\faker;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    loggedAs(Usuario::first());

    app()->set(ValidateOAuthMiddleware::class, new class extends ValidateOAuthMiddleware {
        public function __construct()
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $usuario = Usuario::first();

            return $handler->handle(
                $request->withAttribute(Usuario::class, $usuario)
            );
        }
    });
});

afterEach(function () {
    FirebaseToken::truncate();
});

test('registra token no banco', function () {
    $token = faker()->uuid;
    assertEquals(0, FirebaseToken::count());

    $request = putJson("/api/v2/firebase-token/$token");
    assertEquals(201, $request->getStatusCode());
    assertEquals(1, FirebaseToken::count());
});

test('token não duplica no banco', function () {
    $token = faker()->uuid;
    assertEquals(0, FirebaseToken::count());

    putJson("/api/v2/firebase-token/$token");
    assertEquals(1, FirebaseToken::count());

    putJson("/api/v2/firebase-token/$token");
    assertEquals(1, FirebaseToken::count());
});


test('exclui tokens com mais de um mes de qualquer usuário', function () {
    $token = faker()->uuid;
    $tokensCount = 5;
    $now = Carbon::now();

    for ($i = 0; $i < $tokensCount; $i++) {
        FirebaseToken::create([
            'token' => faker()->uuid,
            'usuario_id' => faker()->randomElement(Usuario::all()->pluck('id')->toArray()), // qualquer usuario
            'created_at' => $now->subMonth()->subHour() // um mes e uma hora atras
        ]);
    }

    assertEquals($tokensCount, FirebaseToken::count());

    putJson("/api/v2/firebase-token/$token");
    assertEquals(1, FirebaseToken::count());

    putJson("/api/v2/firebase-token/$token");
    assertEquals(1, FirebaseToken::count());
});
