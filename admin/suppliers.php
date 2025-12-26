<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';

// Handle Add Supplier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supplier'])) {
    $name = trim($_POST['supplier_name']);
    $contact = trim($_POST['contact_info']);

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Suppliers (supplier_name, contact_info) VALUES (?, ?)");
            $stmt->execute([$name, $contact]);
            $message = "Supplier added successfully!";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Suppliers
$suppliers = $pdo->query("SELECT * FROM Suppliers ORDER BY supplier_id DESC")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Suppliers</h1>

    <?php if($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <!-- Add Supplier Form -->
    <div class="bg-white shadow sm:rounded-lg mb-6 p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Supplier</h3>
        <form method="POST" action="" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                <input type="text" name="supplier_name" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Contact Info</label>
                <input type="text" name="contact_info" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
            </div>
            <button type="submit" name="add_supplier" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add Supplier</button>
        </form>
    </div>

    <!-- Suppliers List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($suppliers as $supplier): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $supplier['supplier_id'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($supplier['supplier_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($supplier['contact_info']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-900 cursor-pointer">Edit</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
