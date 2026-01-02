<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/header.php';
?>

<style>
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .step-indicator {
        transition: all 0.3s ease;
    }

    .step-indicator.active {
        background: #667eea;
        color: white;
    }

    .step-indicator.completed {
        background: #10b981;
        color: white;
    }

    .result-card {
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 mb-4">
                <i class="fas fa-robot text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-2">
                AI Cost Estimator
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">
                Get instant AI-powered construction cost estimates
            </p>
        </div>

        <!-- Estimation Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
            <form id="estimationForm" enctype="multipart/form-data">

                <!-- Image Upload Section -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <i class="fas fa-image mr-2 text-purple-600"></i>Upload Site Image or Blueprint
                    </label>
                    <div class="relative">
                        <input type="file" id="site_image" name="site_image" accept="image/*" class="hidden"
                            onchange="previewImage(event)">
                        <label for="site_image"
                            class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-purple-500 dark:hover:border-purple-400 transition-colors bg-gray-50 dark:bg-gray-700">
                            <div id="imagePreview" class="hidden w-full h-full p-2">
                                <img id="previewImg" src="" alt="Preview"
                                    class="w-full h-full object-contain rounded-lg">
                            </div>
                            <div id="uploadPlaceholder" class="text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-semibold text-purple-600 dark:text-purple-400">Click to
                                        upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Project Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                    <!-- Project Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Project Type <span class="text-red-500">*</span>
                        </label>
                        <select name="project_type" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            <option value="">Select type</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location" required placeholder="e.g., Dhaka, Bangladesh"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Size (sq ft) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="size" required min="100" placeholder="e.g., 2500"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Floors -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Number of Floors <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="floors" required min="1" max="50" placeholder="e.g., 2"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Material Quality -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Material Quality <span class="text-red-500">*</span>
                        </label>
                        <select name="material_quality" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            <option value="">Select quality</option>
                            <option value="basic">Basic</option>
                            <option value="standard">Standard</option>
                            <option value="premium">Premium</option>
                            <option value="luxury">Luxury</option>
                        </select>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Expected Timeline (months) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="timeline" required min="1" placeholder="e.g., 12"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Special Features -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Special Features (Optional)
                    </label>
                    <textarea name="special_features" rows="3"
                        placeholder="e.g., Swimming pool, underground parking, smart home systems..."
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-magic mr-2"></i>
                    Generate AI Estimate
                </button>
            </form>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSection" class="hidden text-center py-12">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400 font-medium">
                <i class="fas fa-brain mr-2"></i>AI is analyzing your project...
            </p>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 result-card">

                <!-- Source Badge -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <span id="sourceBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"></span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400" id="generatedAt"></div>
                </div>

                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-6">
                    <i class="fas fa-calculator mr-2 text-purple-600"></i>
                    Cost Estimation Results
                </h2>

                <!-- Total Cost -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 mb-6 text-white">
                    <p class="text-sm font-medium opacity-90 mb-1">Total Estimated Cost</p>
                    <p class="text-4xl font-black" id="totalCost">$0</p>
                    <p class="text-sm opacity-75 mt-2">
                        <span id="costPerSqft"></span> per sq ft
                    </p>
                </div>

                <!-- Cost Breakdown -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Cost Breakdown</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="breakdownGrid">
                        <!-- Will be populated by JS -->
                    </div>
                </div>

                <!-- Timeline -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-300">
                        <i class="fas fa-clock mr-2"></i>
                        Estimated Timeline: <span id="timelineEst" class="font-bold"></span>
                    </p>
                </div>

                <!-- Recommendations (if AI) -->
                <div id="recommendationsSection" class="hidden mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        AI Recommendations
                    </h3>
                    <ul id="recommendationsList" class="space-y-2">
                        <!-- Will be populated by JS -->
                    </ul>
                </div>

                <!-- Note -->
                <div id="noteSection" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-300" id="noteText"></p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <button onclick="window.print()"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                        <i class="fas fa-print mr-2"></i>Print Estimate
                    </button>
                    <button onclick="location.reload()"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                        <i class="fas fa-redo mr-2"></i>New Estimate
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').classList.remove('hidden');
                document.getElementById('uploadPlaceholder').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    document.getElementById('estimationForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        // Show loading
        document.getElementById('loadingSection').classList.remove('hidden');
        this.classList.add('hidden');
        document.getElementById('resultsSection').classList.add('hidden');

        // Prepare form data
        const formData = new FormData(this);

        try {
            // Submit to backend
            const response = await fetch('process_ai_estimate.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                displayResults(result.estimation);
            } else {
                alert('Error: ' + (result.error || 'Unknown error occurred'));
                document.getElementById('loadingSection').classList.add('hidden');
                this.classList.remove('hidden');
            }
        } catch (error) {
            alert('Error: ' + error.message);
            document.getElementById('loadingSection').classList.add('hidden');
            this.classList.remove('hidden');
        }
    });

    function displayResults(estimation) {
        // Hide loading, show results
        document.getElementById('loadingSection').classList.add('hidden');
        document.getElementById('resultsSection').classList.remove('hidden');

        // Source badge
        const sourceBadge = document.getElementById('sourceBadge');
        if (estimation.source === 'ai') {
            sourceBadge.textContent = '🤖 AI-Powered';
            sourceBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        } else {
            sourceBadge.textContent = '🎲 Mock Estimation';
            sourceBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
        }

        // Generated at
        document.getElementById('generatedAt').textContent = 'Generated: ' + estimation.generated_at;

        // Total cost
        document.getElementById('totalCost').textContent = '$' + estimation.total_cost.toLocaleString();
        document.getElementById('costPerSqft').textContent = '$' + estimation.cost_per_sqft.toLocaleString();

        // Breakdown
        const breakdownGrid = document.getElementById('breakdownGrid');
        breakdownGrid.innerHTML = '';

        const breakdown = estimation.breakdown;
        const items = [
            { label: 'Materials', value: breakdown.materials, icon: 'fa-hard-hat' },
            { label: 'Labor', value: breakdown.labor, icon: 'fa-users' },
            { label: 'Equipment', value: breakdown.equipment, icon: 'fa-tools' },
            { label: 'Permits', value: breakdown.permits, icon: 'fa-file-contract' },
            { label: 'Contingency', value: breakdown.contingency, icon: 'fa-shield-alt' },
            { label: 'Special Features', value: breakdown.special_features, icon: 'fa-star' }
        ];

        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'p-4 bg-gray-50 dark:bg-gray-700 rounded-lg';
            div.innerHTML = `
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                <i class="fas ${item.icon} mr-1"></i>${item.label}
            </p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">$${item.value.toLocaleString()}</p>
        `;
            breakdownGrid.appendChild(div);
        });

        // Timeline
        document.getElementById('timelineEst').textContent = estimation.timeline_estimate;

        // Recommendations (AI only)
        if (estimation.recommendations && estimation.recommendations.length > 0) {
            document.getElementById('recommendationsSection').classList.remove('hidden');
            const recList = document.getElementById('recommendationsList');
            recList.innerHTML = '';
            estimation.recommendations.forEach(rec => {
                const li = document.createElement('li');
                li.className = 'flex items-start text-gray-700 dark:text-gray-300';
                li.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i><span>${rec}</span>`;
                recList.appendChild(li);
            });
        }

        // Note
        if (estimation.note) {
            document.getElementById('noteText').textContent = '⚠️ ' + estimation.note;
        } else {
            document.getElementById('noteSection').classList.add('hidden');
        }

        // Scroll to results
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }
</script>

<?php include '../includes/footer.php'; ?>