<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcrDiagnosticsController extends Controller
{
    public function ping(Request $request)
    {
        $pythonServiceUrl = (string) config('services.ocr_python.health_url', 'http://127.0.0.1:8010/health');

        $res = null;
        $error = null;

        try {
            $res = Http::timeout(5)->connectTimeout(2)->get($pythonServiceUrl);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return response()->json([
            'url' => $pythonServiceUrl,
            'ok' => $res ? $res->successful() : false,
            'status' => $res ? $res->status() : null,
            'body' => $res ? $res->body() : null,
            'error' => $error,
        ]);
    }
}

