<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Converter;

class ConverterBag
{
    private static array $converterMap = [];

    private static array $converterObjects = [];

    public static function get(string $name): ConverterInterface
    {
        self::init();
        if (!isset(self::$converterObjects[$name])) {
            if (!isset(self::$converterMap[$name])) {
                throw new \InvalidArgumentException(sprintf('No converter named "%s" found', $name));
            }

            self::$converterObjects[$name] = new self::$converterMap[$name]();
        }

        return self::$converterObjects[$name];
    }

    public static function set(string $name, string $className): void
    {
        self::init();
        if (isset(self::$converterMap[$name])) {
            throw new \InvalidArgumentException(sprintf('Converter with name "%s" already exist', $name));
        }

        self::$converterMap[$name] = $className;
    }

    public static function has(string $name): bool
    {
        self::init();

        return isset(self::$converterMap[$name]);
    }

    protected static function init(): void
    {
        if (!empty(self::$converterMap)) {
            return;
        }
        self::$converterMap = [
            DateTimeConverter::getName() => DateTimeConverter::class,
            DateTimeImmutableConverter::getName() => DateTimeImmutableConverter::class,
            JsonConverter::getName() => JsonConverter::class,
        ];
    }
}
