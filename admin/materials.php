<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';

// Handle Add Material
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_material'])) {
    $name = trim($_POST['material_name']);
    $unit = trim($_POST['unit']);
    $price = $_POST['unit_price'];
    $supplier_id = $_POST['supplier_id'];

    if (!empty($name) && !empty($price)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Materials (material_name, unit, unit_price, supplier_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $unit, $price, $supplier_id]);
            $message = "Material added successfully!";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Materials with Supplier Name
$materials = $pdo->query("SELECT m.*, s.supplier_name FROM Materials m LEFT JOIN Suppliers s ON m.supplier_id = s.supplier_id ORDER BY m.material_id DESC")->fetchAll();

// Fetch Suppliers for Dropdown
$suppliers = $pdo->query("SELECT * FROM Suppliers")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Materials</h1>

    <?php if($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <!-- Add Material Form -->
    <div class="bg-white shadow sm:rounded-lg mb-6 p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Material</h3>
        <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Material Name</label>
                <input type="text" name="material_name" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit (e.g. kg, bags)</label>
                <input type="text" name="unit" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price ($)</label>
                <input type="number" step="0.01" name="unit_price" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Supplier</label>
                <select name="supplier_id" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2 bg-white">
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= $supplier['supplier_id'] ?>"><?= htmlspecialchars($supplier['supplier_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_material" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add Material</button>
        </form>
    </div>

    <!-- Materials List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($materials as $material): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($material['material_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($material['unit']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?= number_format($material['unit_price'], 2) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($material['supplier_name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
