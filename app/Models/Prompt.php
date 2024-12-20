<?php

namespace App\Models;

use App\Enums\PromptCode;
use Dflydev\DotAccessData\Exception\DataException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Prompt extends Model
{
    public $guarded = [];

    protected $casts = [
        'code' => PromptCode::class,
    ];

    public static function for(PromptCode $promptCode): string
    {
        if ($prompt = self::whereCode($promptCode)->first()) {
            Log::warning('prompt', ['prompt' => $prompt->prompt]);

            return $prompt->prompt;
        }

        throw new DataException('No prompt available for given Code');
    }
}
