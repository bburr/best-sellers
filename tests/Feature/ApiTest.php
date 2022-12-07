<?php

namespace Tests\Feature;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiTest extends TestCase
{
    const ENDPOINT = '/api/1/nyt/best-sellers';
    const NYT_API_KEY = 'asdf123';
    const NYT_ENDPOINT_URL = 'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json';

    const AUTHOR_FILTER = 'Sanderson';
    const TITLE_FILTER = 'Kings';
    const ISBN_FILTER_1 = '0765399830';
    const ISBN_FILTER_2 = '9780765399830';
    const OFFSET = 20;

    protected function setUp(): void
    {
        parent::setUp();

        $url = self::NYT_ENDPOINT_URL . '?api-key=' . self::NYT_API_KEY;

        Http::fake([
            $url => $this->response('no-arguments'),
            $url . '&author=' . self::AUTHOR_FILTER => $this->response('author-filter'),
            $url . '&author=' . self::AUTHOR_FILTER . '&offset=' . self::OFFSET =>
                $this->response('author-offset'),
            $url . '&author=' . self::AUTHOR_FILTER . '&title=' . self::TITLE_FILTER =>
                $this->response('author-title-filter'),
            $url . '&isbn=' . self::ISBN_FILTER_1 => $this->response('isbn-filter'),
            $url . '&isbn=' . self::ISBN_FILTER_1 . '%3B' . self::ISBN_FILTER_2 =>
                $this->response('actual-multi-isbn-response'),
            $url . '&offset=' . self::OFFSET => $this->response('offset'),
            $url . '&title=' . self::TITLE_FILTER => $this->response('title-filter'),
            $url . '&title=' . self::TITLE_FILTER . '&offset=' . self::OFFSET =>
                $this->response('title-offset'),
        ]);

        Http::preventStrayRequests();

        config([
            'env.nyt.apiKey' => self::NYT_API_KEY,
            'env.nyt.endpointUrl' => self::NYT_ENDPOINT_URL,
        ]);
    }

    public function testWithoutParameters(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response
            ->assertStatus(200)
            ->assertJsonPath('num_results', 34933)
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('results.14.title', "'TIS THE SEASON");
    }

    public function testFilterAuthor(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?author=' . self::AUTHOR_FILTER);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 25)
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('results.0.title', 'A MEMORY OF LIGHT');
    }

    public function testFilterAuthorOffset(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?author=' . self::AUTHOR_FILTER . '&offset=' . self::OFFSET);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 25)
            ->assertJsonCount(5, 'results')
            ->assertJsonPath('results.0.title', 'THE WAY OF KINGS');
    }

    public function testFilterIsbnSingle(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?isbn[]=' . self::ISBN_FILTER_1);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 1)
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.title', 'OATHBRINGER');
    }

    public function testFilterIsbnMultiple(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?isbn[]=' . self::ISBN_FILTER_1 . '&isbn[]=' . self::ISBN_FILTER_2);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 0)
            ->assertJsonCount(0, 'results');
    }

    public function testFilterInvalidIsbnLength(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?isbn[]=1234');

        $response->assertStatus(422);
    }

    public function testFilterTitle(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?title=' . self::TITLE_FILTER);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 24)
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('results.0.title', 'A CLASH OF KINGS');
    }

    public function testFilterTitleOffset(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?title=' . self::TITLE_FILTER . '&offset=' . self::OFFSET);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 24)
            ->assertJsonCount(4, 'results')
            ->assertJsonPath('results.3.title', 'THE WAY OF KINGS');
    }

    public function testFilterAuthorTitle(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?author=' . self::AUTHOR_FILTER . '&title=' . self::TITLE_FILTER);

        $response->assertStatus(200)
            ->assertJsonPath('num_results', 1)
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.title', 'THE WAY OF KINGS');
    }

    public function testOffset(): void
    {
        $offset = 20;

        $response = $this->getJson(self::ENDPOINT . '?offset=' . $offset);

        $response
            ->assertStatus(200)
            ->assertJsonPath('num_results', 34933)
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('results.0.title', '1,000 PLACES TO SEE BEFORE YOU DIE');
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

    private function response(string $filename): PromiseInterface
    {
        return Http::response(
            json_decode(file_get_contents(base_path('tests/responses/' . $filename . '.json')), true)
        );
    }
}
