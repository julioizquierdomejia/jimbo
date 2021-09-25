<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleFavorite extends Model
{
    use HasFactory;

    protected $table = 'raffle_favorites';
    protected $fillable = [
        'user_id',
        'raffle_id'
    ];

    /*
    |------------------------------------------------------------------------------------
    | Validations
    |------------------------------------------------------------------------------------
    */
    public static function rules($update = false, $id=null)
    {
        return [
            'user_id' => 'required',
            'raffle_id' => 'required',
        ];
    }

    /*
    |------------------------------------------------------------------------------------
    | Relations
    |------------------------------------------------------------------------------------
    */

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    /*
    |------------------------------------------------------------------------------------
    | Scopes
    |------------------------------------------------------------------------------------
    */

    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
}