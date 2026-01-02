<?php
session_start();
require_once '../config/db.php';
require_once '../config/ai_config.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "AI Document Analyzer";
$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = $_POST['prompt'] ?? '';
    
    // Check Config
    if (!defined('AI_API_KEY') || empty(AI_API_KEY)) {
        $error = "API Key missing. Please check config/ai_config.php.";
    } elseif (empty($prompt)) {
        $error = "Please enter a prompt.";
    } else {
        // Construct Gemini Payload
        $parts = [];
        
        // Handle File Uploads (Images/Text)
        if (!empty($_FILES['files']['name'][0])) {
            foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                    $mime_type = $_FILES['files']['type'][$key];
                    $file_data = file_get_contents($tmp_name);
                    
                    if (strpos($mime_type, 'image/') === 0) {
                        // Image for Vision
                        $base64_data = base64_encode($file_data);
                        $parts[] = [
                            'inline_data' => [
                                'mime_type' => $mime_type,
                                'data' => $base64_data
                            ]
                        ];
                    } else {
                        // Text File
                        $text_content = substr($file_data, 0, 10000); // Limit context
                        $parts[] = ['text' => "File Content ({$_FILES['files']['name'][$key]}):\n" . $text_content];
                    }
                }
            }
        }
        
        // Add User Prompt
        $parts[] = ['text' => $prompt];

        $payload = [
            'contents' => [
                ['parts' => $parts]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 4096
            ]
        ];

        // Call Google Gemini API
        $api_url = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL_ID . ":generateContent?key=" . AI_API_KEY;
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            // Parse Gemini Response
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $result = $data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $result = "AI returned no text. Debug: " . print_r($data, true);
            }
        } else {
            $error = "API Error ($http_code). Raw response: " . htmlspecialchars(substr($response, 0, 200));
        }
    }
}

include '../includes/layout/header.php';
include '../includes/layout/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include '../includes/layout/topbar.php'; ?>
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
        
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Analyze Documents & Images (Powered by Gemini)</h2>
            
            <?php if($error): ?>
                <div class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 p-4 rounded-lg mb-6 border border-red-200 dark:border-red-800"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Model Selection Hidden (Fixed to Configured Model) -->
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload File (Image/Text)</label>
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-file-upload text-gray-400 text-3xl mb-2"></i>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-brand-600 dark:text-brand-400 hover:text-brand-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="files[]" type="file" class="sr-only" multiple onchange="updateFileList(this)">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-500">PNG, JPG, PDF, TXT up to 10MB</p>
                            <div id="file-list" class="mt-4 text-sm text-gray-600 dark:text-gray-300 font-medium"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prompt / Question</label>
                    <textarea name="prompt" rows="4" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500" placeholder="Describe what you see in this image..."><?= htmlspecialchars($_POST['prompt'] ?? '') ?></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm">
                        <i class="fas fa-magic mr-2"></i> Analyze with Gemini
                    </button>
                </div>
            </form>

            <?php if($result): ?>
                <div class="mt-8 bg-brand-50 dark:bg-brand-900/20 rounded-xl p-6 border border-brand-100 dark:border-brand-800">
                    <h3 class="text-md font-bold text-brand-900 dark:text-brand-100 mb-2"><i class="fas fa-robot mr-2"></i> Gemini Response</h3>
                    <div class="prose prose-sm text-brand-800 dark:text-brand-200 max-w-none whitespace-pre-wrap"><?= htmlspecialchars($result) ?></div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
function updateFileList(input) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    if (input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
            list.innerHTML += '<div><i class="fas fa-check text-green-500 mr-2"></i>' + input.files[i].name + '</div>';
        }
    }
}
</script>

<?php include '../includes/layout/footer.php'; ?>
