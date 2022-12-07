<?php declare(strict_types=1);

namespace Tests\Unit\Rules;
use App\Rules\IsbnLength;
use Tests\TestCase;

class IsbnLengthTest extends TestCase
{
    public function data(): array
    {
        return [
            ['123'],
            ['123123123'],
            ['12312312312'],
            ['123123123123'],
            ['12312312312312'],
        ];
    }

    /**
     * @dataProvider data
     * @param string $value
     * @return void
     */
    public function testRule(string $value): void
    {
        $fail = function () {
            throw new \Exception();
        };

        $this->expectException(\Exception::class);

        (new IsbnLength())->__invoke('', $value, $fail);
    }
}
