<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->isMethod('post')) {
            $types    = ['stocks', 'incomes', 'sales', 'orders'];
            $dateFrom = $dateTo = '';
            try {
                $validated = $request->validate([
                    'dateFrom' => 'required|date',
                    'dateTo' => 'required|date|after_or_equal:dateFrom',
                ]);

                foreach ($types as $type) {
                    $dateTo = $validated['dateTo'];
                    $dateFrom = $validated['dateFrom'];

                    if ($type === 'stocks') {
                        if ($dateFrom > $dateTo)
                            $dateTo = Carbon::now()->format("Y-m-d");
                        $dateFrom = Carbon::now()->format("Y-m-d");
                    }

                    $this->fetchAndStore($dateFrom, $dateTo, $type);
                }
            } catch (ValidationException|Exception $e) {
                http_response_code((int)$e->getCode());
                return view('form', [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'error' => $e->getMessage(),
                ]);
            }
            return view('form', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'success' => true,
            ]);
        }
        return view('form');
    }

    /**
     * @throws Exception
     */
    protected function fetchAndStore($dateFrom, $dateTo, $type): void
    {
        $page = 1;
        do {
            $params   = "dateFrom=$dateFrom&dateTo=$dateTo&page=$page&key=" . config('services.api.key');
            $json     = Http::get(config('services.api.base_url') . "$type?$params");
            $response = $json->json();

            if (empty($response['meta'])) {
                if(is_array($response))
                    $errorMessage = implode(' ', $this->flattenKeysAndValues($response));
                else
                    $errorMessage = $response;
                throw new Exception($errorMessage, 400);
            }

            $lastPage = $response['meta']['last_page'];
            $page++;
            if(!empty($response['data']))
                DB::connection('external')->table($type)->insert($response['data']);
        } while ($page <= $lastPage);
    }

    protected function flattenKeysAndValues(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "$prefix.$key" : $key; // Для вложенных ключей (user.name)
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenKeysAndValues($value, $newKey));
            } else {
                $result[$newKey] = "$newKey: $value";
            }
        }
        return $result;
    }
}
