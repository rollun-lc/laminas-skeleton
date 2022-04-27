<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

use Jaeger\Span\Context\SpanContext;
use Jaeger\Tag\StringTag;
use rollun\dic\InsideConstruct;
use rollun\logger\LifeCycleToken;
use rollun\utils\Json\Serializer;
use Laminas\Diactoros\ServerRequestFactory;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Laminas\ServiceManager\ServiceManager;

ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_USER_DEPRECATED);

// Delegate static file requests back to the PHP built-in webserver
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(function () {
    /** @var ServiceManager $container */
    $container = require 'config/container.php';

    InsideConstruct::setContainer($container);

    /**
     * @var \Jaeger\Tracer\Tracer $tracer
     */
    $tracer = $container->get(\Jaeger\Tracer\Tracer::class);

    /**
     * Self-called anonymous function that creates its own scope and keep the top lamda clean.
     */
    /**
     *
     */
    $span = (function () use ($tracer) {
        $tags = [];
        $serverRequest = ServerRequestFactory::fromGlobals();

        $traceContextHeader = $serverRequest->getHeader(\rollun\tracer\ClientWithTracer::TRACER_HEADER_NAME);
        if ($traceContextHeader) {
            $traceContext = json_decode($traceContextHeader);
            $spanContext = new SpanContext(
                $traceContext->traceId,
                $traceContext->spanId,
                $traceContext->parentId,
                $traceContext->flags
            );
        } else {
            $spanContext = null;
        }


        $tags[] = new StringTag(
            'request.attributes.json',
            Serializer::jsonSerialize($serverRequest->getAttributes())
        );

        foreach ($serverRequest->getHeaders() as $headerName => $headerValues) {
            $tags[] = new StringTag("request.header.$headerName", implode(' ,', $headerValues));
        }

        $tags[] = new StringTag('request.method', $serverRequest->getMethod());

        $tags[] = new StringTag('request.protocolVersion', $serverRequest->getProtocolVersion());

        $tags[] = new StringTag('request.target', $serverRequest->getRequestTarget());

        $tags[] = new StringTag('request.uri', $serverRequest->getUri()->__toString());

        $tags[] = new StringTag('request.get.raw', $serverRequest->getUri()->getQuery());
        $tags[] = new StringTag(
            'request.get.json',
            Serializer::jsonSerialize($serverRequest->getQueryParams())
        );


        $tags[] = new StringTag('request.body', $serverRequest->getBody()->__toString());

        foreach ($serverRequest->getHeaders() as $headerName => $headerValues) {
            $tags[] = new StringTag("request.header.$headerName", implode(' ,', $headerValues));
        }

        foreach ($serverRequest->getCookieParams() as $cookieName => $cookieValue) {
            $tags[] = new StringTag(
                "request.cookie.$cookieName",
                (is_array($cookieValue) ? implode(' ,', $cookieValue) : $cookieValue)
            );
        }

        return $tracer->start('index', $tags, $spanContext);
    })();


    /** @var Application $app */
    $app = $container->get(Application::class);
    $factory = $container->get(MiddlewareFactory::class);

    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    $app->run();
    $tracer->finish($span);
    $tracer->flush();
})();
