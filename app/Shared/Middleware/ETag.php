<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ETag
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if ($request->isMethod('GET')
            && $response->isSuccessful()
            && $response->getContent() !== false
            && $response->getContent() !== ''
        ) {
            $etag = '"' . md5($response->getContent()) . '"';

            if ($request->header('If-None-Match') === $etag) {
                return (new Response('', 304))
                    ->setEtag($etag)
                    ->setPublic();
            }

            $response->setEtag($etag);
            $response->setPublic();
        }

        return $response;
    }
}