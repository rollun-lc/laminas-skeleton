<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

return [
    Jaeger\Tracer\Tracer::class => [
        'serviceName' => getenv('SERVICE_NAME') ?? 'application'
    ]
];
