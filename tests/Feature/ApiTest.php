<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiTest extends TestCase
{
    const ENDPOINT = '/api/1/nyt/best-sellers';
    const NYT_API_KEY = 'asdf123';
    const NYT_ENDPOINT_URL = 'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        config([
            'env.nyt.apiKey' => self::NYT_API_KEY,
            'env.nyt.endpointUrl' => self::NYT_ENDPOINT_URL,
        ]);
    }

    public function testWithoutParameters(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) {
            return $request->url() === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY;
        });
    }

    public function testFilterAuthor(): void
    {
        $authorFilter = 'Sanderson';

        $response = $this->getJson(self::ENDPOINT . '?author=' . $authorFilter);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($authorFilter) {
            return $request->url() === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY . '&author=' . $authorFilter;
        });
    }

    public function testFilterIsbnSingle(): void
    {
        $isbn = '1231231231';

        $response = $this->getJson(self::ENDPOINT . '?isbn[]=' . $isbn);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($isbn) {
            return $request->url() === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY . '&isbn=' . $isbn;
        });
    }

    public function testFilterIsbnMultiple(): void
    {
        $isbn1 = '1231231231';
        $isbn2 = '1231231231231';

        $response = $this->getJson(self::ENDPOINT . '?isbn[]=' . $isbn1 . '&isbn[]=' . $isbn2);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($isbn1, $isbn2) {
            return urldecode($request->url()) === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY . '&isbn=' . $isbn1 . ';' . $isbn2;
        });
    }

    public function testFilterInvalidIsbnLength(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?isbn[]=1234');

        $response->assertStatus(422);
    }

    public function testFilterTitle(): void
    {
        $titleFilter = 'Kings';

        $response = $this->getJson(self::ENDPOINT . '?title=' . $titleFilter);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($titleFilter) {
            return $request->url() === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY . '&title=' . $titleFilter;
        });
    }

    public function testOffset(): void
    {
        $offset = 20;

        $response = $this->getJson(self::ENDPOINT . '?offset=' . $offset);

        $response->assertStatus(200);

        Http::assertSent(function (Request $request) use ($offset) {
            return $request->url() === self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY . '&offset=' . $offset;
        });
    }

    public function testInvalidOffsetValueNotMultiple(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?offset=5');

        $response->assertStatus(422);
    }

    public function testInvalidOffsetValueNegative(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?offset=-20');

        $response->assertStatus(422);
    }
}
