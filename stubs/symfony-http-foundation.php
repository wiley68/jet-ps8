<?php

/**
 * IDE / статичен анализ: Symfony HttpFoundation (Request, InputBag).
 * В магазина класовете идват от vendor; при вече заредени класове този файл не дефинира нищо.
 * Не се включва в composer autoload — само за Intelephense / редактор.
 */

declare(strict_types=1);

namespace Symfony\Component\HttpFoundation {
    if (!class_exists(InputBag::class, false)) {
        class InputBag
        {
            /**
             * @param mixed $default
             */
            public function get(string $key, $default = null): mixed
            {
                return $default;
            }

            public function has(string $key): bool
            {
                return false;
            }

            /**
             * @return array<string, mixed>
             */
            public function all(): array
            {
                return [];
            }
        }
    }

    if (!class_exists(Request::class, false)) {
        class Request
        {
            public const METHOD_GET = 'GET';

            public const METHOD_POST = 'POST';

            public const METHOD_PUT = 'PUT';

            public const METHOD_PATCH = 'PATCH';

            public const METHOD_DELETE = 'DELETE';

            public InputBag $query;

            public InputBag $request;

            public function __construct()
            {
                $this->query = new InputBag();
                $this->request = new InputBag();
            }

            public function getMethod(): string
            {
                return self::METHOD_GET;
            }

            public function isMethod(string $method): bool
            {
                return false;
            }

            /**
             * @param mixed $default
             */
            public function get(string $key, $default = null): mixed
            {
                return $default;
            }
        }
    }
}
