<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @class Task
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property integer $user_id
 * @property integer $status_id
 */
class Task extends Model
{
    use Searchable;
    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'text',
        'user_id',
        'status_id'
    ];


//    #[SearchUsingPrefix(['title','text'])]
//    #[SearchUsingFullText(['title','text'])]
    public function toSearchableArray(): array
    {
        return [
//            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    private function normalizeText($text)
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9а-яА-Я\s]/u', '', $text)));
    }
}
