<?php

namespace Illuminatech\EnumSeeder;

/**
 * ChecksOutdated allows to check whether the existing row data matches the new one.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ChecksOutdated
{
    /**
     * Checks if existing row is outdated in comparison to the new row data.
     *
     * @param object $existingRow existing record.
     * @param array $newRow new record data.
     * @param array $only list of attributes, which should be compared, if empty - all attributes will be compared.
     * @return bool whether existing row is outdated.
     */
    protected function isOutdated(object $existingRow, array $newRow, array $only = []): bool
    {
        foreach ($newRow as $attribute => $value) {
            if (!empty($only) && !in_array($attribute, $only, true)) {
                continue;
            }

            if (!$this->isEqual($existingRow->{$attribute}, $newRow[$attribute])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if given values are equal or not.
     *
     * @param mixed $oldValue existing attribute value.
     * @param mixed $newValue new attribute value.
     * @return bool whether values are considered equal.
     */
    protected function isEqual($oldValue, $newValue): bool
    {
        if ($oldValue === null) {
            return $newValue === null;
        }

        if ($newValue === null) {
            return $oldValue === null;
        }

        if (is_string($oldValue) && is_string($newValue)) {
            return $oldValue === $newValue;
        }

        if (is_int($oldValue) && is_int($newValue)) {
            return $oldValue === $newValue;
        }

        return $oldValue == $newValue;
    }
}