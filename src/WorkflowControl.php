<?php

namespace Illuminatech\EnumSeeder;

/**
 * WorkflowControl defines common methods, which controls the seeding workflow.
 * Its methods designed to be overridden at particular seeder class.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait WorkflowControl
{
    /**
     * Defines the rows, which related database entity should be synchronized with.
     *
     * @return array[] list of rows data.
     */
    abstract protected function rows(): array;

    /**
     * Defines attribute values, which should be applied as defaults to each created row.
     * The particular row definition may override values defined by this method.
     * For example:
     *
     * ```
     * [
     *     'created_at' => now(),
     * ]
     * ```
     *
     * @return array<string, mixed> attributes specification.
     */
    protected function shouldCreateWith(): array
    {
        return [];
    }

    /**
     * Defines whether rows, which exist in the database, but are missing at {@see rows()} specification, should
     * be deleted from database or not.
     *
     * @return bool whether to delete obsolete rows from database.
     */
    protected function shouldDeleteObsolete(): bool
    {
        return false;
    }

    /**
     * Defines the attribute values, which should be applied to the rows, which exist in the database,
     * but are missing at {@see rows()} specification.
     *
     * For example:
     *
     * ```
     * [
     *     'deleted_at' => now(),
     * ]
     * ```
     *
     * > Note: if {@see shouldDeleteObsolete()} returns `true`, this method will be ignored.
     *
     * @return array<string, mixed> attributes specification.
     */
    protected function shouldUpdateObsoleteWith(): array
    {
        return [];
    }

    /**
     * Defines whether rows, which exist in the database, should be always updated with actual {@see rows()} values or not.
     *
     * > Note: if {@see shouldUpdateExistingOnlyWith()} returns not empty value, this method will be ignored.
     *
     * @return bool whether to always update existing rows.
     */
    protected function shouldUpdateExisting(): bool
    {
        return false;
    }

    /**
     * Defines the list of attributes and particular attribute values, which should always be applied to the rows,
     * which already exist in the database.
     *
     * For example:
     *
     * ```
     * [
     *     'slug',
     *     'is_active',
     *     'updated_at' => now(),
     * ]
     * ```
     *
     * > Note: this method takes precedence over {@see shouldUpdateExisting()}.
     *
     * @return array<string|int, mixed> attributes specification.
     */
    protected function shouldUpdateExistingOnlyWith(): array
    {
        return [];
    }
}