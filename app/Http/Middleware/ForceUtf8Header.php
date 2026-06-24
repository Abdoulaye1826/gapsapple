<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8Header
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $contentType = $response->headers->get('Content-Type');

            if ($contentType === null) {
                if ($response->getContent() !== null) {
                    $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
                }
            } else {
                if (stripos($contentType, 'charset') === false
                    && (stripos($contentType, 'text/') !== false
                    || stripos($contentType, 'application/json') !== false
                    || stripos($contentType, 'application/xml') !== false
                    || stripos($contentType, 'application/xhtml+xml') !== false)) {
                    $baseType = explode(';', $contentType, 2)[0];
                    $response->headers->set('Content-Type', trim($baseType).'; charset=UTF-8');
                }
            }
        }

        return $response;
    }
}
