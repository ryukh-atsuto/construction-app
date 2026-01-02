<?php
// includes/profile_forms.php

/**
 * Renders role-specific professional information fields
 */
function renderRoleStep2($role, $data)
{
    echo '<div id="step2" class="space-y-6 hidden animate-fadeIn">';
    echo '<h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 2: Professional Insights (' . ucfirst($role) . ')</h3>';
    echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';

    switch ($role) {
        case 'owner':
            renderOwnerFields($data);
            break;
        case 'manager':
            renderManagerFields($data);
            break;
        case 'engineer':
            renderEngineerFields($data);
            break;
        case 'worker':
            renderWorkerFields($data);
            break;
        case 'admin':
            renderAdminFields($data);
            break;
    }

    echo '</div>';
    echo '<div class="flex justify-between mt-8">';
    echo '  <button type="button" onclick="prevStep(1)" class="px-6 py-3 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-white font-bold rounded-xl hover:bg-gray-200 transition-all">Back</button>';
    echo '  <button type="button" onclick="nextStep(3)" class="px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg transition-all">Save & Next</button>';
    echo '</div>';
    echo '</div>';
}

function renderOwnerFields($data)
{
    echo '
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Ownership Type</label>
            <select name="owner_type" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
                <option value="Individual" ' . (($data['owner_type'] ?? '') == 'Individual' ? 'selected' : '') . '>Private Individual</option>
                <option value="Company" ' . (($data['owner_type'] ?? '') == 'Company' ? 'selected' : '') . '>Company Representative</option>
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Company Name</label>
            <input type="text" name="company_name" value="' . htmlspecialchars($data['company_name'] ?? '') . '" placeholder="e.g. Skyline Ventures" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none focus:border-brand-500">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Business Registration #</label>
            <input type="text" name="business_reg_number" value="' . htmlspecialchars($data['business_reg_number'] ?? '') . '" placeholder="REG-2024-XXXX" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Investment Range</label>
            <select name="investment_range" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
                <option value="0-1M" ' . (($data['investment_range'] ?? '') == '0-1M' ? 'selected' : '') . '>$0 - $1 Million</option>
                <option value="1M-10M" ' . (($data['investment_range'] ?? '') == '1M-10M' ? 'selected' : '') . '>$1M - $10 Million</option>
                <option value="10M+" ' . (($data['investment_range'] ?? '') == '10M+' ? 'selected' : '') . '>$10 Million+</option>
            </select>
        </div>
        <div class="md:col-span-2 space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Past Project Experience</label>
            <textarea name="past_experience" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none focus:border-brand-500">' . htmlspecialchars($data['past_experience'] ?? '') . '</textarea>
        </div>
    ';
}

function renderManagerFields($data)
{
    echo '
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Assigned Region</label>
            <input type="text" name="assigned_region" value="' . htmlspecialchars($data['assigned_region'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Management Experience (Years)</label>
            <input type="number" name="experience_years" value="' . htmlspecialchars($data['experience_years'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Shift Preference</label>
            <select name="work_shift" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
                <option value="Day" ' . (($data['work_shift'] ?? '') == 'Day' ? 'selected' : '') . '>Morning / Day</option>
                <option value="Night" ' . (($data['work_shift'] ?? '') == 'Night' ? 'selected' : '') . '>Night Shift</option>
                <option value="Flexible" ' . (($data['work_shift'] ?? '') == 'Flexible' ? 'selected' : '') . '>Flexible</option>
            </select>
        </div>
    ';
}

function renderEngineerFields($data)
{
    echo '
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Specialization</label>
            <input type="text" name="specialization" value="' . htmlspecialchars($data['specialization'] ?? '') . '" placeholder="e.g. Structural, Geotechnical" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">License Number</label>
            <input type="text" name="license_number" value="' . htmlspecialchars($data['license_number'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Years of Practice</label>
            <input type="number" name="years_experience" value="' . htmlspecialchars($data['years_experience'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Consultation Fee ($/hr)</label>
            <input type="number" name="consultation_fee" value="' . htmlspecialchars($data['consultation_fee'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
    ';
}

function renderWorkerFields($data)
{
    echo '
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Primary Skill</label>
            <select name="skillset" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
                <option value="Masonry" ' . (($data['skillset'] ?? '') == 'Masonry' ? 'selected' : '') . '>Masonry</option>
                <option value="Plumbing" ' . (($data['skillset'] ?? '') == 'Plumbing' ? 'selected' : '') . '>Plumbing</option>
                <option value="Electrical" ' . (($data['skillset'] ?? '') == 'Electrical' ? 'selected' : '') . '>Electrical</option>
                <option value="Carpentry" ' . (($data['skillset'] ?? '') == 'Carpentry' ? 'selected' : '') . '>Carpentry</option>
                <option value="Painting" ' . (($data['skillset'] ?? '') == 'Painting' ? 'selected' : '') . '>Painting</option>
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Secondary Skills (Optional)</label>
            <input type="text" name="secondary_skills" value="' . htmlspecialchars($data['secondary_skills'] ?? '') . '" placeholder="e.g. Welding, Tiling" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Hourly Rate ($/hr)</label>
            <input type="number" name="hourly_rate" value="' . htmlspecialchars($data['hourly_rate'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
        <div class="space-y-2">
            <div class="flex items-center space-x-3 mt-8">
                <input type="checkbox" name="fitness_declaration" ' . (($data['fitness_declaration'] ?? false) ? 'checked' : '') . ' class="w-5 h-5 text-brand-600 rounded">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">I am physically fit for site work</label>
            </div>
        </div>
    ';
}

function renderAdminFields($data)
{
    echo '
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Permission Scope</label>
            <select name="permission_scope" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
                <option value="All Regions" ' . (($data['permission_scope'] ?? '') == 'All Regions' ? 'selected' : '') . '>All Regions</option>
                <option value="North" ' . (($data['permission_scope'] ?? '') == 'North' ? 'selected' : '') . '>North Zone</option>
                <option value="South" ' . (($data['permission_scope'] ?? '') == 'South' ? 'selected' : '') . '>South Zone</option>
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Audit Responsibility</label>
            <input type="text" name="audit_responsibility" value="' . htmlspecialchars($data['audit_responsibility'] ?? '') . '" class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white outline-none">
        </div>
    ';
}

/**
 * Renders document upload fields (Common for all roles)
 */
function renderRoleStep3($role, $data)
{
    echo '<div id="step3" class="space-y-6 hidden animate-fadeIn">';
    echo '<h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 3: Verification Documents</h3>';
    echo '<div class="space-y-4">';

    // Role-specific document requirements
    $doc_label = "Supporting Document";
    switch ($role) {
        case 'owner':
            $doc_label = "Business Registration / Investment Proof";
            break;
        case 'manager':
            $doc_label = "Management Certification";
            break;
        case 'engineer':
            $doc_label = "Professional License / Degree Certificate";
            break;
        case 'worker':
            $doc_label = "Safety Training Certificate";
            break;
        case 'admin':
            $doc_label = "Authorized ID Card Scan";
            break;
    }

    echo '
        <div class="p-6 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl bg-gray-50 dark:bg-slate-900/50 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 mb-4">
                <i class="fas fa-file-upload text-2xl"></i>
            </div>
            <p class="text-sm font-bold text-gray-900 dark:text-white">' . $doc_label . '</p>
            <p class="text-xs text-gray-500 mb-4">Supported formats: PDF, JPG, PNG (Max 5MB)</p>
            <input type="file" name="doc_upload" class="hidden" id="doc_upload">
            <button type="button" onclick="document.getElementById(\'doc_upload\').click()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-bold shadow-sm">Choose File</button>
        </div>
    ';

    echo '</div>';
    echo '<div class="flex justify-between mt-8">';
    echo '  <button type="button" onclick="prevStep(2)" class="px-6 py-3 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-white font-bold rounded-xl hover:bg-gray-200 transition-all">Back</button>';
    echo '  <button type="submit" class="px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-500/20 transform hover:-translate-y-0.5 transition-all">Complete Profile <i class="fas fa-check-circle ml-2"></i></button>';
    echo '</div>';
    echo '</div>';
}
?>