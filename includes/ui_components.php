<?php
// includes/ui_components.php

/**
 * Renders a Stats Card
 */
function renderStatsCard($title, $value, $icon_class, $color = 'brand') {
    $colors = [
        'brand' => 'bg-brand-50 text-brand-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'orange' => 'bg-orange-50 text-orange-700',
        'red' => 'bg-rose-50 text-rose-700',
    ];
    $icon_bg = $colors[$color] ?? $colors['brand'];
    
    echo "
    <div class='bg-white dark:bg-gray-800 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 rounded-xl border border-gray-200 dark:border-gray-700'>
        <div class='p-5'>
            <div class='flex items-center'>
                <div class='flex-shrink-0'>
                    <div class='rounded-md p-3 {$icon_bg} bg-opacity-20 dark:bg-opacity-10'>
                        <i class='{$icon_class} text-xl'></i>
                    </div>
                </div>
                <div class='ml-5 w-0 flex-1'>
                    <dl>
                        <dt class='text-sm font-medium text-gray-500 dark:text-gray-400 truncate'>{$title}</dt>
                        <dd>
                            <div class='text-2xl font-bold text-gray-900 dark:text-white'>{$value}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    ";
}

/**
 * Renders a Status Badge
 */
function renderStatusBadge($status) {
    $status = strtolower($status);
    $styles = [
        'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
        'completed' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
        'finished' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700',
        'requested' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-700',
        'rejected' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700',
        'assigned' => 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-700',
        'available' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700',
        'draft' => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
    ];
    
    $style = $styles[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
    $label = ucfirst($status);
    
    echo "<span class='px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {$style}'>{$label}</span>";
}

/**
 * Renders a Section Header
 */
function renderSectionHeader($title, $action_text = '', $action_url = '', $action_color = 'brand') {
    $btn = '';
    if ($action_text && $action_url) {
        $colors = [
            'brand' => 'bg-brand-600 hover:bg-brand-700 focus:ring-brand-500',
            'accent' => 'bg-accent-600 hover:bg-accent-700 focus:ring-accent-500',
        ];
        $c = $colors[$action_color] ?? $colors['brand'];
        $btn = "<a href='{$action_url}' class='inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {$c} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-transform hover:-translate-y-0.5'>
            {$action_text}
        </a>";
    }
    
    echo "
    <div class='md:flex md:items-center md:justify-between mb-6'>
        <div class='flex-1 min-w-0'>
            <h2 class='text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate'>{$title}</h2>
        </div>
        <div class='mt-4 flex md:mt-0 md:ml-4'>
            {$btn}
        </div>
    </div>
    ";
}
?>
