<?php

declare(strict_types=1);

namespace App\Modules\Students;

use InvalidArgumentException;

class StudentsValidator
{

    public function validateUpdate(array $data): void
    {
        $validator = validator($data, [
            "name" => "required|string",
            'email' => 'required|string|email|unique:students,email',
            'avatar' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(json_encode($validator->errors()->all()));
        }
    }

}
