<?php
/**
 * Gemini API Configuration
 */

define('GEMINI_API_KEY', 'AIzaSyBqQAF-sc_tGnjcHaG0GJwgzU2paUJ6JbM');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');

/**
 * Send request to Gemini API with image and text
 */
function callGeminiAPI($prompt, $imageBase64 = null)
{
    $apiKey = GEMINI_API_KEY;
    $url = GEMINI_API_ENDPOINT . '?key=' . $apiKey;

    // Build request body
    $parts = [];

    // Add text prompt
    $parts[] = ['text' => $prompt];

    // Add image if provided
    if ($imageBase64) {
        $parts[] = [
            'inline_data' => [
                'mime_type' => 'image/jpeg',
                'data' => $imageBase64
            ]
        ];
    }

    $requestBody = [
        'contents' => [
            [
                'parts' => $parts
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
        ]
    ];

    // Make API call
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $httpCode !== 200) {
        return [
            'success' => false,
            'error' => $error ?: 'API returned status code: ' . $httpCode,
            'response' => $response
        ];
    }

    $data = json_decode($response, true);

    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => true,
            'text' => $data['candidates'][0]['content']['parts'][0]['text']
        ];
    }

    return [
        'success' => false,
        'error' => 'Invalid API response format',
        'response' => $response
    ];
}

/**
 * Generate mock estimation (fallback)
 */
function generateMockEstimation($projectData)
{
    // Base cost per square foot based on material quality
    $costPerSqFt = [
        'basic' => rand(80, 120),
        'standard' => rand(120, 180),
        'premium' => rand(180, 250),
        'luxury' => rand(250, 400)
    ];

    $quality = $projectData['material_quality'] ?? 'standard';
    $baseCost = $costPerSqFt[$quality];
    $sqft = (int) ($projectData['size'] ?? 1000);

    // Calculate base construction cost
    $constructionCost = $baseCost * $sqft;

    // Adjust for floors (multi-story adds complexity)
    $floors = (int) ($projectData['floors'] ?? 1);
    if ($floors > 1) {
        $constructionCost *= (1 + ($floors - 1) * 0.15);
    }

    // Project type multiplier
    $typeMultiplier = [
        'residential' => 1.0,
        'commercial' => 1.3,
        'industrial' => 1.5
    ];
    $type = strtolower($projectData['project_type'] ?? 'residential');
    $constructionCost *= ($typeMultiplier[$type] ?? 1.0);

    // Additional costs
    $materialsCost = $constructionCost * 0.35;
    $laborCost = $constructionCost * 0.40;
    $equipmentCost = $constructionCost * 0.10;
    $permitsCost = $constructionCost * 0.05;
    $contingency = $constructionCost * 0.10;

    // Special features
    $specialFeatures = 0;
    $features = $projectData['special_features'] ?? '';
    if (stripos($features, 'pool') !== false)
        $specialFeatures += rand(30000, 60000);
    if (stripos($features, 'parking') !== false)
        $specialFeatures += rand(15000, 35000);
    if (stripos($features, 'basement') !== false)
        $specialFeatures += rand(25000, 50000);

    $totalCost = $constructionCost + $specialFeatures;

    return [
        'total_cost' => round($totalCost, 2),
        'breakdown' => [
            'materials' => round($materialsCost, 2),
            'labor' => round($laborCost, 2),
            'equipment' => round($equipmentCost, 2),
            'permits' => round($permitsCost, 2),
            'contingency' => round($contingency, 2),
            'special_features' => round($specialFeatures, 2)
        ],
        'cost_per_sqft' => round($totalCost / $sqft, 2),
        'timeline_estimate' => ceil($sqft / 100) . '-' . ceil($sqft / 80) . ' months',
        'note' => 'This is a mock estimation generated for demonstration purposes.'
    ];
}
?>