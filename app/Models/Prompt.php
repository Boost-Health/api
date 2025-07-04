<?php

namespace App\Models;

use App\Enums\PromptCode;
use Dflydev\DotAccessData\Exception\DataException;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    public $guarded = [];

    protected $casts = [
        'code' => PromptCode::class,
    ];

    public static function for(PromptCode $promptCode, ?array $contexts = []): string
    {
        if ($prompt = self::whereCode($promptCode)->first()) {
            $promptText = $prompt->prompt;

            if (filled($contexts)) {
                $formattedContexts = [];
                foreach ($contexts as $key => $value) {
                    $formattedContexts[] = sprintf("\n\n<%s>\n%s\n</%s>", $key, $value, $key);
                }

                $promptText .= implode("\n", $formattedContexts);
            }

            return $promptText;
        }

        throw new DataException('No prompt available for given Code');
    }
}
