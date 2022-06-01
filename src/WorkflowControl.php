<?php

namespace Illuminatech\EnumSeeder;

trait WorkflowControl
{
    abstract protected function rows(): array;

    protected function shouldCreateWith(): array
    {
        return [];
    }

    protected function shouldDeleteObsolete(): bool
    {
        return false;
    }

    protected function shouldUpdateObsoleteWith(): array
    {
        return [];
    }

    protected function shouldUpdateExisting(): bool
    {
        return false;
    }

    protected function shouldUpdateExistingOnlyWith(): array
    {
        return [];
    }
}