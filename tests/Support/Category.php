<?php

namespace Illuminatech\EnumSeeder\Test\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $type_id
 * @property string $name
 * @property string $slug
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Category extends Model
{
    /**
     * {@inheritdoc}
     */
    public $incrementing = true;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'type_id',
        'name',
        'slug',
    ];
}