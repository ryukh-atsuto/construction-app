<?php
session_start();
require_once '../config/db.php';
require_once '../config/gemini_config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Collect form data
$projectData = [
    'project_type' => $_POST['project_type'] ?? '',
    'location' => $_POST['location'] ?? '',
    'size' => $_POST['size'] ?? '',
    'floors' => $_POST['floors'] ?? '',
    'material_quality' => $_POST['material_quality'] ?? '',
    'timeline' => $_POST['timeline'] ?? '',
    'special_features' => $_POST['special_features'] ?? ''
];

// Handle image upload
$imageBase64 = null;
$imageProcessed = false;

if (isset($_FILES['site_image']) && $_FILES['site_image']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['site_image']['tmp_name'];
    $imageData = file_get_contents($tmpName);

    // Validate image type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (in_array($mimeType, $allowedTypes)) {
        $imageBase64 = base64_encode($imageData);
        $imageProcessed = true;
    }
}

// Build AI prompt
$prompt = "You are a professional construction cost estimator. Based on the following project details" .
    ($imageProcessed ? " and the provided image" : "") .
    ", provide a detailed cost estimation in JSON format.\n\n";

$prompt .= "Project Details:\n";
$prompt .= "- Type: " . $projectData['project_type'] . "\n";
$prompt .= "- Location: " . $projectData['location'] . "\n";
$prompt .= "- Size: " . $projectData['size'] . " sq ft\n";
$prompt .= "- Number of Floors: " . $projectData['floors'] . "\n";
$prompt .= "- Material Quality: " . $projectData['material_quality'] . "\n";
$prompt .= "- Expected Timeline: " . $projectData['timeline'] . " months\n";
$prompt .= "- Special Features: " . ($projectData['special_features'] ?: 'None') . "\n\n";

$prompt .= "Please provide a cost estimation in the following JSON format:\n";
$prompt .= "{\n";
$prompt .= "  \"total_cost\": <number>,\n";
$prompt .= "  \"breakdown\": {\n";
$prompt .= "    \"materials\": <number>,\n";
$prompt .= "    \"labor\": <number>,\n";
$prompt .= "    \"equipment\": <number>,\n";
$prompt .= "    \"permits\": <number>,\n";
$prompt .= "    \"contingency\": <number>,\n";
$prompt .= "    \"special_features\": <number>\n";
$prompt .= "  },\n";
$prompt .= "  \"cost_per_sqft\": <number>,\n";
$prompt .= "  \"timeline_estimate\": \"<X-Y months>\",\n";
$prompt .= "  \"recommendations\": [\"<recommendation 1>\", \"<recommendation 2>\", ...]\n";
$prompt .= "}\n\n";
$prompt .= "Respond ONLY with valid JSON, no additional text.";

// Try Gemini API first
$useAI = true;
$estimationResult = null;

if ($useAI) {
    $apiResponse = callGeminiAPI($prompt, $imageBase64);

    if ($apiResponse['success']) {
        // Try to parse JSON from AI response
        $responseText = $apiResponse['text'];

        // Extract JSON from response (sometimes AI adds markdown or extra text)
        if (preg_match('/\{[\s\S]*\}/', $responseText, $matches)) {
            $jsonText = $matches[0];
            $estimationResult = json_decode($jsonText, true);

            if (!$estimationResult) {
                // JSON parsing failed, use mock
                $useAI = false;
            }
        } else {
            // No JSON found in response, use mock
            $useAI = false;
        }
    } else {
        // API call failed, use mock
        $useAI = false;
    }
}

// Fallback to mock estimation
if (!$useAI || !$estimationResult) {
    $estimationResult = generateMockEstimation($projectData);
}

// Add metadata
$estimationResult['source'] = $useAI ? 'ai' : 'mock';
$estimationResult['generated_at'] = date('Y-m-d H:i:s');
$estimationResult['project_data'] = $projectData;

// Optionally save to database
try {
    $owner_id = $_SESSION['user_id'];
    $total_cost = $estimationResult['total_cost'];

    // Check if we should create a project record or just an estimate
    // For now, just save to Project_Estimates without creating a full project

    $stmt = $pdo->prepare("INSERT INTO Project_Estimates (created_by, estimate_date, total_estimated_cost, status) VALUES (?, NOW(), ?, 'draft')");
    $stmt->execute([$owner_id, $total_cost]);
    $estimate_id = $pdo->lastInsertId();

    $estimationResult['estimate_id'] = $estimate_id;

} catch (Exception $e) {
    // Database save failed, but continue with estimation result
    $estimationResult['db_error'] = $e->getMessage();
}

// Return result
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'estimation' => $estimationResult
]);
?>