<?php

declare(strict_types=1);

namespace PHPPress\Tests\Support;

use PHPPress\Tests\Http\Emitter\Stub\HTTPFunctions;
use PHPUnit\Event\Test\{PreparationStarted, PreparationStartedSubscriber};
use PHPUnit\Event\TestSuite\{Started, StartedSubscriber};
use PHPUnit\Runner\Extension\{Extension, Facade, ParameterCollection};
use PHPUnit\TextUI\Configuration\Configuration;
use Xepozz\InternalMocker\{Mocker, MockerState};

/**
 * Custom configuration extension for mocking internal functions.
 */
final class MockerExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class implements StartedSubscriber {
                public function notify(Started $event): void
                {
                    MockerExtension::load();
                }
            },
            new class implements PreparationStartedSubscriber {
                public function notify(PreparationStarted $event): void
                {
                    MockerState::resetState();
                }
            },
        );
    }

    public static function load(): void
    {
        $mocks = [
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'http_response_code',
                'function' => fn(?int $response_code = null) => HTTPFunctions::http_response_code($response_code),
            ],
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'header',
                'function' => fn(string $string, bool $replace = true, ?int $http_response_code = null) => HTTPFunctions::header($string, $replace, $http_response_code),
            ],
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'headers_sent',
                'function' => fn(&$file = null, &$line = null) => HTTPFunctions::headers_sent($file, $line),
            ],
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'header_remove',
                'function' => fn() => HTTPFunctions::header_remove(),
            ],
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'header_list',
                'function' => fn() => HTTPFunctions::headers_list(),
            ],
            [
                'namespace' => 'PHPPress\Http\Emitter',
                'name' => 'flush',
                'function' => fn() => HTTPFunctions::flush(),
            ],
        ];

        $mocker = new Mocker();
        $mocker->load($mocks);

        MockerState::saveState();
    }
}
