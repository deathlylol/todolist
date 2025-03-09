<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Status
 * @property integer $id
 * @property string $title
 */
class Status extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'title'
    ];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }
}
