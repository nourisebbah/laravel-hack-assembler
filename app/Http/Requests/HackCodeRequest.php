<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HackCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hackCode' => 'required|string|max:10000'
        ];
    }
    //extra custom validation logic 
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = explode("\n", $this->input('hackCode'));
    
            foreach ($lines as $index => $line) {
                $line = trim($line);
    
                //to skip lines with comments and empty lines 
                if ($line === '' || str_starts_with($line, '//')) {
                    continue;
                }
    
                // Remove comments within the code 
                if (str_contains($line, '//')) {
                    $line = explode('//', $line, 2)[0];
                    $line = trim($line); // Remove whitespace with the comment
                }
    
                // if the line is empty after removing the comment, we skip it
                if ($line === '') {
                    continue;
                }
    
             
                if (!$this->isValidHackCommand($line)) {
                    $validator->errors()->add(
                        'hackCode',
                        'Invalid syntax at line ' . ($index + 1) . ': "' . trim($line) . '"'
                    );
                }
            }
        });
    }
    
    private function isValidHackCommand(string $line): bool
    {
        if (preg_match('/^@[a-zA-Z0-9_.$:]+$/', $line)) {
            return true; // A-instruction
        }

        if (preg_match('/^(A?M?D?=)?[AMD01+\-!&|]+(;J(GT|EQ|GE|LT|NE|LE|MP))?$/', $line)) {
            return true; // C-instruction
        }

        if (preg_match('/^\([a-zA-Z_.$:][a-zA-Z0-9_.$:]*\)$/', $line)) {
            return true; // Label (pseudo-command)
        }

        return false;
    }
}
