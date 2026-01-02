<?php

// Configuration
$OPENROUTER_API_KEY = ''; // Get free key at https://openrouter.ai/keys

// Free models available on OpenRouter
$available_models = [
    'google/gemini-2.0-flash-exp:free' => 'Google Gemini 2.0 Flash (Free)',
    'xiaomi/mimo-v2-flash:free' => 'Mimo v2',
];

$selected_model = $_POST['model'] ?? 'google/gemini-2.0-flash-exp:free';

// Handle form submission
$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = $_POST['prompt'] ?? '';
    
    if (empty($OPENROUTER_API_KEY) || $OPENROUTER_API_KEY === 'YOUR_API_KEY_HERE') {
        $error = 'Please set your OpenRouter API key in the PHP file. Get a free key at: https://openrouter.ai/keys';
    } elseif (empty($prompt)) {
        $error = 'Please enter a prompt.';
    } else {
        try {
            $messages = [];
            $content_parts = [];
            
            // Process uploaded files first
            if (!empty($_FILES['files']['name'][0])) {
                foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_data = file_get_contents($tmp_name);
                        $file_name = $_FILES['files']['name'][$key];
                        $mime_type = $_FILES['files']['type'][$key];
                        
                        // Check if it's an image
                        if (strpos($mime_type, 'image/') === 0) {
                            $base64_data = base64_encode($file_data);
                            $data_url = "data:{$mime_type};base64,{$base64_data}";
                            
                            $content_parts[] = [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $data_url
                                ]
                            ];
                        } else {
                            // For non-image files, include content as text
                            $content_parts[] = [
                                'type' => 'text',
                                'text' => "File: {$file_name}\nContent:\n" . substr($file_data, 0, 5000)
                            ];
                        }
                    }
                }
            }
            
            // Add prompt text
            $content_parts[] = [
                'type' => 'text',
                'text' => $prompt
            ];
            
            $messages[] = [
                'role' => 'user',
                'content' => $content_parts
            ];
            
            // Prepare API request
            $api_url = "https://openrouter.ai/api/v1/chat/completions";
            
            $request_body = [
                'model' => $selected_model,
                'messages' => $messages
            ];
            
            // Make API call
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $OPENROUTER_API_KEY,
                'HTTP-Referer: http://localhost', // Optional
                'X-Title: AI File Analyzer' // Optional
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                
                if (isset($response_data['choices'][0]['message']['content'])) {
                    $result = $response_data['choices'][0]['message']['content'];
                } else {
                    $error = 'Unexpected response format from OpenRouter API.';
                }
            } else {
                $error_data = json_decode($response, true);
                $error_message = $error_data['error']['message'] ?? 'Unknown error';
                
                if ($http_code === 429) {
                    $error = "⏱️ <strong>Rate Limit Exceeded</strong><br><br>";
                    $error .= "You've hit the rate limit for this model.<br><br>";
                    $error .= "<strong>Solutions:</strong><br>";
                    $error .= "1. Wait a few seconds and try again<br>";
                    $error .= "2. Try a different model from the dropdown<br>";
                    $error .= "3. Check your credits at: <a href='https://openrouter.ai/credits' target='_blank'>OpenRouter Credits</a><br><br>";
                    $error .= "<em>Technical details: " . htmlspecialchars($error_message) . "</em>";
                } elseif ($http_code === 401) {
                    $error = "🔑 <strong>Invalid API Key</strong><br><br>";
                    $error .= "Please check your OpenRouter API key.<br>";
                    $error .= "Get a free key at: <a href='https://openrouter.ai/keys' target='_blank'>https://openrouter.ai/keys</a>";
                } else {
                    $error = 'API Error (' . $http_code . '): ' . $error_message;
                }
            }
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenRouter AI File Analyzer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        textarea {
            resize: vertical;
        }
        
        select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        input[type="file"]:hover {
            border-color: #667eea;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .result-box, .error-box {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .result-box {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
        }
        
        .error-box {
            background: #fef2f2;
            border: 2px solid #ef4444;
            color: #991b1b;
            line-height: 1.6;
        }
        
        .error-box a {
            color: #dc2626;
            text-decoration: underline;
        }
        
        .info-box {
            background: #dcfce7;
            border: 2px solid #22c55e;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .info-box a {
            color: #15803d;
            font-weight: 600;
        }
        
        .supported-formats {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .free-badge {
            display: inline-block;
            background: #22c55e;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 OpenRouter AI File Analyzer <span class="free-badge">FREE</span></h1>
        <p class="subtitle">Upload files and ask questions using free AI models via OpenRouter</p>
        
        <?php if (empty($OPENROUTER_API_KEY) || $OPENROUTER_API_KEY === 'YOUR_API_KEY_HERE'): ?>
        <div class="info-box">
            <strong>✨ Setup Required:</strong> Get your free OpenRouter API key at 
            <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> and add it to the top of this PHP file.
            <br><br>
            <strong>Why OpenRouter?</strong> Access multiple free AI models with a single API key!
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="model">Select AI Model (All Free!)</label>
                <select id="model" name="model">
                    <?php foreach ($available_models as $model_id => $model_name): ?>
                        <option value="<?php echo htmlspecialchars($model_id); ?>" <?php echo ($model_id === $selected_model) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($model_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prompt">Your Prompt</label>
                <textarea 
                    id="prompt" 
                    name="prompt" 
                    rows="4" 
                    placeholder="E.g., Analyze this image and describe what you see..."
                    required
                ><?php echo htmlspecialchars($_POST['prompt'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="files">Upload Files (Optional)</label>
                <input 
                    type="file" 
                    id="files" 
                    name="files[]" 
                    multiple
                    accept="image/*,.pdf,.txt,.doc,.docx"
                >
                <div class="supported-formats">
                    Supported: Images (JPEG, PNG, GIF, WebP), PDF, Text files
                </div>
            </div>
            
            <button type="submit">✨ Generate Response</button>
        </form>
        
        <?php if ($error): ?>
        <div class="error-box">
            <strong>Error:</strong><br>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($result): ?>
        <div class="result-box">
            <strong>AI Response:</strong><br><br>
            <?php echo nl2br(htmlspecialchars($result)); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>