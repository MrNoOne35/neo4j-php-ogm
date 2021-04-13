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

use Neo4j\OGM\Exception\ConverterException;

class DateTimeConverter implements ConverterInterface
{
    const DEFAULT_FORMAT = 'timestamp';

    const LONG_TIMESTAMP_FORMAT = 'long_timestamp';

    public static function getName(): string
    {
        return 'datetime';
    }

    public function toDatabaseValue($value, ?array $options)
    {
        if (null === $value) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            $format = isset($options['format']) ? $options['format'] : self::DEFAULT_FORMAT;

            if (self::DEFAULT_FORMAT === $format) {
                return $value->getTimestamp();
            }

            if (self::LONG_TIMESTAMP_FORMAT === $format) {
                return doubleval($value->format('U.u'));
            }

            try {
                return $value->format($format);
            } catch (\Exception $e) {
                throw new ConverterException(sprintf('Error while converting timestamp: %s', $e->getMessage()));
            }
        }

        throw new ConverterException(sprintf('Unable to convert value in converter "%s"', $this->getName()));
    }

    public function toPHPValue(array $values, string $propertyName, ?array $options)
    {
        if (!isset($values[$propertyName]) || null === $values[$propertyName]) {
            return null;
        }

        $tz = isset($options['timezone']) ? new \DateTimeZone($options['timezone']) : new \DateTimeZone(date_default_timezone_get());

        $format = isset($options['format']) ? $options['format'] : self::DEFAULT_FORMAT;
        $v = $values[$propertyName];

        if (self::DEFAULT_FORMAT === $format) {
            return \DateTime::createFromFormat('U', $v, $tz);
        }

        if (self::LONG_TIMESTAMP_FORMAT === $format) {
            return \DateTime::createFromFormat('U.u', doubleval($v), $tz);
        }

        return \DateTime::createFromFormat($format, $v);
    }
}
