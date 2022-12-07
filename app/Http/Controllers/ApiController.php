<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BestSellersRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function bestSellers(BestSellersRequest $request): JsonResponse
    {
        $filters = $request->validated();

        // format ISBN field
        if (isset($filters['isbn'])) {
            $filters['isbn'] = implode(';', $filters['isbn']);
        }

        $response = Http::acceptJson()
            ->get(config('env.nyt.endpointUrl'), [
                'api-key' => config('env.nyt.apiKey'),
                ...$filters
            ]);

        return response()->json($response->json());
    }
}
