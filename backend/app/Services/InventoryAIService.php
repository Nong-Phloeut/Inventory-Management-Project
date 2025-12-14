<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Collection;
use OpenAI\Exceptions\RateLimitException;

class InventoryAIService
{
    private function callAI(string $prompt): string
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = OpenAI::responses()->create([
                    'model' => 'gpt-4o',
                    'input' => $prompt,
                ]);

                return $response['output'][0]['content'][0]['text']
                    ?? 'No AI response.';
            } catch (RateLimitException $e) {
                $attempt++;
                sleep(2); // wait before retry
            }
        }

        return '⚠️ AI service is busy (rate limit exceeded). Please try again later.';
    }
    // Analyze Low Stock products
    public function analyzeLowStock(Collection $products): string
    {
        if ($products->isEmpty()) {
            return 'All products are sufficiently stocked.';
        }

        // Rule-based fallback (always available)
        $fallback = $products->map(function ($p) {
            $needed = max(0, $p->low_stock_threshold - $p->quantity);
            return "- {$p->name}: current {$p->quantity}, reorder at least {$needed} units.";
        })->implode("\n");

        try {
            $data = $products->map(fn($p) => [
                'name' => $p->name,
                'quantity' => $p->quantity,
                'reorder_level' => $p->low_stock_threshold,
            ])->toArray();

            $response = OpenAI::responses()->create([
                'model' => 'gpt-4o',
                'max_output_tokens' => 300,
                'input' => "Analyze the following low stock products and prioritize restocking:\n"
                    . json_encode($data)
            ]);

            return $response['output'][0]['content'][0]['text'] ?? $fallback;
        } catch (\Throwable $e) {
            // AI failed → fallback still works
            return "⚠️ AI unavailable. System recommendation:\n" . $fallback;
        }
    }


    // Analyze Purchase report
    public function analyzePurchases(Collection $purchases): string
    {
        if ($purchases->isEmpty()) {
            return 'No purchase data available.';
        }

        $data = $purchases->map(fn($p) => [
            'product' => $p->product->name ?? 'Unknown',
            'quantity' => $p->quantity,
            'cost' => $p->cost,
            'date' => optional($p->created_at)->toDateString(),
        ])->toArray();

        $prompt = "
            You are an inventory analyst.

            Analyze this purchase report:
            " . json_encode($data) . "

            Provide:
            - Cost optimization insights
            - High purchase items
            - Unusual anomalies
            ";

        return $this->callAI($prompt);
    }

    // Analyze Stock Movement
    public function analyzeStockMovement(Collection $movements): string
    {
        if ($movements->isEmpty()) {
            return 'No stock movement data available.';
        }

        $data = $movements->map(fn($m) => [
            'product' => $m->product->name ?? 'Unknown',
            'quantity_in' => $m->quantity_in ?? 0,
            'quantity_out' => $m->quantity_out ?? 0,
            'date' => optional($m->created_at)->toDateString(),
        ])->toArray();

        $prompt = "
            You are an inventory AI analyst.

            Analyze the following stock movement data:
            " . json_encode($data) . "

            Provide:
            - Movement trends
            - Fast-moving products
            - Risk of shortages
            ";

        return $this->callAI($prompt);
    }
}
