<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * '*' fait confiance au proxy immédiat (reverse proxy / load balancer de
     * l'hébergeur) pour les en-têtes X-Forwarded-*. Sans ça, en production
     * derrière un proxy, Laravel voit la connexion interne (souvent http)
     * au lieu du schéma réel côté client (https), ce qui fait générer des
     * liens signés (URL::signedRoute, ex: partage WhatsApp) avec le mauvais
     * schéma — la signature ne correspond alors plus à l'URL visitée par
     * le client et Laravel renvoie 403 "Invalid signature".
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
