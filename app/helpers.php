<?php

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

if (!function_exists('customValidate')) {
    function customValidate($validationData, $rules)
    {
        $validator = Validator::make($validationData, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }
}

if (!function_exists('validateUserId')) {
    function validateUserId($userId)
    {
        customValidate(['userId' => $userId], [
            'userId' => 'required|integer|exists:users,id'
        ]);
    }
}

if (!function_exists('validateCaptcha')) {
    function validateCaptcha($num1, $num2, $operator, $answer)
    {
        $result = 0;

        switch ($operator) {
            case "+":
                $result = $num1 + $num2;
                break;

            case "-":
                $result = $num1 - $num2;
                break;

            case "*":
                $result = $num1 * $num2;
                break;

            case "/":
                $result = $num1 / $num2;
                break;

            default:
                $result = 0;
        }

        if ($answer === $result) {
            return true;
        } else {
            return false;
        }
    }
}
