<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use rollun\logger\Writer\Stream;

return [
    'log' => [
        LoggerInterface::class => [
            'writers' => [
                [
                    'name' => Stream::class,
                    'options' => [
                        'stream' => 'data/logs/all.log',
                    ]
                ],
            ],
        ],
    ],
];