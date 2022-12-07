<?php declare(strict_types=1);

namespace Tests\Unit\Rules;
use App\Rules\IsbnLength;
use Tests\TestCase;

class IsbnLengthTest extends TestCase
{
    public function data(): array
    {
        return [
            ['123', true],
            ['123123123', true],
            ['1231231231', false],
            ['12312312312', true],
            ['123123123123', true],
            ['1231231231231', false],
            ['12312312312312', true],
        ];
    }

    /**
     * @dataProvider data
     * @param string $value
     * @param bool $shouldFail
     * @return void
     */
    public function testRule(string $value, bool $shouldFail): void
    {
        $fail = function () {
            throw new \Exception();
        };

        try {
            (new IsbnLength())->__invoke('', $value, $fail);

            $this->assertFalse($shouldFail);
        }
        catch (\Exception $e) {
            $this->assertTrue($shouldFail);
        }
    }
}
