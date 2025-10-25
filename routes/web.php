<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

// Dashboard principal
Route::get('/', function() {
    try {
        $apiBaseUrl = "https://landing-pbg-ia.vercel.app/api/pbg";
        
        $chartResponse = Http::timeout(10)->get($apiBaseUrl . "/charts");
        $latestResponse = Http::timeout(10)->get($apiBaseUrl . "/latest");
        
        if (!$chartResponse->successful() || !$latestResponse->successful()) {
            return view("dashboard", ["error" => "Error conectando con la API"]);
        }
        
        $chartData = $chartResponse->json()["data"];
        $latestData = $latestResponse->json()["data"];
        
        $totalPBG = $chartData["total_pbg"] ?? 0;
        $totalValueBillions = $totalPBG / 1_000_000_000;
        
        // Obtener años min y max de la evolución
        $evolution = $chartData["evolution"] ?? [];
        $years = array_column($evolution, 'año');
        $minYear = !empty($years) ? min($years) : 2015;
        $maxYear = !empty($years) ? max($years) : date('Y');
        
        // Calcular estadísticas
        $first_value = $evolution[0]['valor'] ?? 0;
        $last_value = end($evolution)['valor'] ?? 0;
        $years_span = count($evolution);
        $cagr = 0;
        $growth_percentage = 0;
        $abs_growth_billions = 0;
        
        if ($first_value > 0 && $years_span > 1) {
            $cagr = (pow($last_value / $first_value, 1 / ($years_span - 1)) - 1) * 100;
            $growth_percentage = (($last_value - $first_value) / $first_value) * 100;
            $abs_growth_billions = ($last_value - $first_value) / 1000000; // Convertir a millones
        }

        // Obtener valores para display
        $latest_pbg_value = $last_value / 1000000; // Convertir a millones
        $latest_pbg_year = $maxYear ?? 2024;
        $variation_yoy = 5.2; // Valor por defecto, se podría calcular

        $statistics = [
            'latest_pbg_value' => $latest_pbg_value,
            'latest_pbg_year' => $latest_pbg_year,
            'variation_yoy' => $variation_yoy,
            'cagr' => round($cagr, 1),
            'growth_percentage' => round($growth_percentage, 1),
            'abs_growth_billions' => round($abs_growth_billions, 1),
            'years_span' => $years_span,
            'min_year' => $minYear,
            'max_year' => $maxYear,
            'sectors_count' => count($chartData['sectors'] ?? []),
            'total_records' => count($latestData['data'] ?? []),
        ];
        
        return view("dashboard", [
            "statistics" => $statistics,
            "chart_data" => $chartData,
            "latest_data" => $latestData,
            "api_base_url" => $apiBaseUrl
        ]);
        
    } catch (\Exception $e) {
        return view("dashboard", ["error" => "Excepción: " . $e->getMessage()]);
    }
});

// Ruta de debug para development
Route::get('/debug', function() {
    try {
        $apiBaseUrl = "https://landing-pbg-ia.vercel.app/api/pbg";
        
        $chartResponse = Http::timeout(10)->get($apiBaseUrl . "/charts");
        $latestResponse = Http::timeout(10)->get($apiBaseUrl . "/latest");
        
        if (!$chartResponse->successful() || !$latestResponse->successful()) {
            return view("debug", ["error" => "Error de API"]);
        }
        
        $chartData = $chartResponse->json()["data"];
        $latestData = $latestResponse->json()["data"];
        
        $totalPBG = $chartData["total_pbg"] ?? 0;
        $totalValueBillions = $totalPBG / 1_000_000_000;
        
        // Obtener años min y max de la evolución
        $evolution = $chartData["evolution"] ?? [];
        $years = array_column($evolution, 'año');
        $minYear = !empty($years) ? min($years) : 2015;
        $maxYear = !empty($years) ? max($years) : date('Y');
        
        // Calcular métricas adicionales
        $firstValue = $evolution[0]['valor'] ?? $totalPBG;
        $lastValue = $totalPBG;
        $growthPercentage = $firstValue > 0 ? (($lastValue - $firstValue) / $firstValue) * 100 : 0;
        $absGrowthBillions = ($lastValue - $firstValue) / 1_000_000_000;
        $yearsSpan = $maxYear - $minYear;
        $cagr = $yearsSpan > 0 && $firstValue > 0 ? (pow($lastValue / $firstValue, 1 / $yearsSpan) - 1) * 100 : 0;
        
        $statistics = [
            "latest_pbg_value" => $totalValueBillions,
            "latest_pbg_year" => $latestData["year"] ?? date("Y"),
            "variation_yoy" => 5.2,
            "cagr" => round($cagr, 1),
            "years_span" => count($evolution),
            "min_year" => $minYear,
            "max_year" => $maxYear,
            "sectors_count" => count($chartData["sectors"] ?? []),
            "total_records" => ($maxYear - $minYear + 1) * count($chartData["sectors"] ?? []),
            "growth_percentage" => round($growthPercentage, 1),
            "abs_growth_billions" => round($absGrowthBillions, 1)
        ];
        
        return view("debug", [
            "statistics" => $statistics,
            "chart_data" => $chartData,
            "latest_data" => $latestData,
            "api_base_url" => $apiBaseUrl
        ]);
        
    } catch (\Exception $e) {
        return view("debug", ["error" => "Excepción: " . $e->getMessage()]);
    }
});

// API JSON debug route
Route::get('/api-debug', function() {
    try {
        $apiBaseUrl = "https://landing-pbg-ia.vercel.app/api/pbg";
        
        $chartData = Http::timeout(10)->get($apiBaseUrl . "/charts");
        $latestData = Http::timeout(10)->get($apiBaseUrl . "/latest");
        
        return response()->json([
            'chart_data' => $chartData->successful() ? $chartData->json() : $chartData->status(),
            'latest_data' => $latestData->successful() ? $latestData->json() : $latestData->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
Route::get('/debug', function() {
    try {
        $apiBaseUrl = "https://landing-pbg-ia.vercel.app/api/pbg";
        
        $chartData = \Illuminate\Support\Facades\Http::timeout(10)->get($apiBaseUrl . "/charts");
        $latestData = \Illuminate\Support\Facades\Http::timeout(10)->get($apiBaseUrl . "/latest");
        
        return response()->json([
            'chart_data' => $chartData->successful() ? $chartData->json() : $chartData->status(),
            'latest_data' => $latestData->successful() ? $latestData->json() : $latestData->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
