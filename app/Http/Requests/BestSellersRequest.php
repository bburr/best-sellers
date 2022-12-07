<?php declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\IsbnLength;
use Illuminate\Foundation\Http\FormRequest;

class BestSellersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'author' => 'string',
            'isbn' => 'array', // array of 10 or 13 digit strings
            'isbn.*' => ['string', new IsbnLength],
            'title' => 'string',
            'offset' => ['integer', 'multiple_of:20', 'gte:0'], // multiple of 20
        ];
    }
}
