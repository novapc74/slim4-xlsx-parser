<?php

use App\Routes\GetTestInfo;
use App\Routes\GetXlsxFile;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function(App $app):void
{
    # CORS Pre-Flight OPTIONS Request Handler
    $app->options('/api/{routes:.+}', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response;
    });

    $app->group('/api', function (RouteCollectorProxyInterface $group) {
        $group->get('/test',GetTestInfo::class);
        $group->get('/xlsx-file', GetXlsxFile::class);
    });

    $app->addBodyParsingMiddleware();
};
