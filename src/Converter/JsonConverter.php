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

class JsonConverter implements ConverterInterface
{
    public static function getName(): string
    {
        return 'json';
    }

    public function toDatabaseValue($value, ?array $options)
    {
        return json_encode($value);
    }

    public function toPHPValue(array $values, string $propertyName, ?array $options)
    {
        if (!isset($values[$propertyName]) || null === $values[$propertyName]) {
            return null;
        }

        return json_decode($values[$propertyName], true, 512, JSON_THROW_ON_ERROR);
    }
}
