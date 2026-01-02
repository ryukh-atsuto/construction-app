<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Financial Overview";

// Fetch Payments
$stmt = $pdo->query("SELECT py.*, p.project_name, u.name as payer_name FROM Payments py JOIN Projects p ON py.project_id = p.project_id JOIN Users u ON py.paid_by = u.user_id ORDER BY py.payment_date DESC");
$payments = $stmt->fetchAll();

// Calculate Totals
$total_revenue = array_sum(array_column($payments, 'total_amount'));
$admin_fees = array_sum(array_column($payments, 'admin_commission'));

include '../includes/layout/header.php';
include '../includes/layout/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include '../includes/layout/topbar.php'; ?>
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Financial Reports</h1>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-brand-700 to-brand-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-brand-100">Total Transaction Volume</p>
                    <i class="fas fa-chart-line text-brand-200 text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold">$<?= number_format($total_revenue, 2) ?></h2>
                <p class="text-xs text-brand-200 mt-2">+12% from last month</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                     <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Revenue (Commissions)</p>
                     <i class="fas fa-coins text-green-500 text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($admin_fees, 2) ?></h2>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                 <div class="flex items-center justify-between mb-4">
                     <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Invoices</p>
                     <i class="fas fa-file-invoice-dollar text-orange-500 text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">3</h2>
                <p class="text-xs text-gray-400 mt-2">Value: $15,200.00</p>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Recent Transactions</h3>
                <button class="text-sm text-brand-600 dark:text-brand-400 font-medium hover:text-brand-800">Export CSV</button>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaction ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fee (5%)</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $pay): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                #TXN-<?= str_pad($pay['payment_id'], 5, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                <?= htmlspecialchars($pay['project_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= htmlspecialchars($pay['payer_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= date('M d, Y', strtotime($pay['payment_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                                $<?= number_format($pay['total_amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 dark:text-green-400 font-medium">
                                +$<?= number_format($pay['admin_commission'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No transactions recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<?php include '../includes/layout/footer.php'; ?>
