<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class RejectExecutableExtensions implements ValidationRule
{
    private const BLOCKED_EXTENSIONS = ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $extension = strtolower(trim($value->getClientOriginalExtension()));

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            $fail('File dengan ekstensi .'.$extension.' tidak diizinkan demi keamanan.');
        }
    }
}
