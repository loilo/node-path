<?php
declare(strict_types=1);

namespace Loilo\NodePath\Test\Helpers;

trait MessageFormatterTrait
{
    protected function formatMessage(array $method, array $args, $expected, $actual): string
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        return sprintf(
            "%s::%s(%s)\n  expect=%s\n  actual=%s",
            $method[0],
            $method[1],
            join(', ', array_map(function ($arg) use ($jsonOptions) {
                return json_encode($arg, $jsonOptions);
            }, $args)),
            json_encode($expected, $jsonOptions),
            json_encode($actual, $jsonOptions)
        );
    }
}
