<?php
$page = 'log';
$currentPage = getCurrentPage();
$perPage = 30;

$search = $_GET['search'] ?? '';
$user_filter = $_GET['user'] ?? '';

$where = "WHERE 1=1";
if ($search) {
    $search_esc = esc($search);
    $where .= " AND (l.aktivitas LIKE '%$search_esc%' OR l.detail LIKE '%$search_esc%')";
}
if ($user_filter) {
    $where .= " AND l.user_id = '$user_filter'";
}

// Total
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas l $where");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = getTotalPages($totalRows, $perPage);

// Data
$limit = getLimit($currentPage, $perPage);
$log_list = mysqli_query($conn, "
    SELECT l.*, u.nama as user_nama, u.role
    FROM log_aktivitas l
    JOIN users u ON l.user_id = u.id
    $where
    ORDER BY l.created_at DESC
    $limit
");

// List user untuk filter
$user_list = mysqli_query($conn, "SELECT * FROM users ORDER BY nama");
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold"><i class="fas fa-history text-primary mr-2"></i>Log Aktivitas</h1>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="log">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs text-gray-500 block mb-1">Cari</label>
                <input type="text" name="search" value="<?= $search ?>" 
                       placeholder="Aktivitas atau detail..."
                       class="w-full px-4 py-2 border rounded-xl text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">User</label>
                <select name="user" class="px-4 py-2 border rounded-xl text-sm">
                    <option value="">Semua</option>
                    <?php while($u = mysqli_fetch_assoc($user_list)): ?>
                    <option value="<?= $u['id'] ?>" <?= $user_filter == $u['id'] ? 'selected' : '' ?>>
                        <?= $u['nama'] ?> (<?= $u['role'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm">Filter</button>
        </form>
    </div>

    <!-- Info -->
    <p class="text-sm text-gray-400">Total <?= $totalRows ?> aktivitas</p>

    <!-- Tabel -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs uppercase">Aktivitas</th>
                    <th class="px-4 py-3 text-left text-xs uppercase">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php while($l = mysqli_fetch_assoc($log_list)): ?>
                <tr class="hover:bg-gray-50 text-sm">
                    <td class="px-4 py-3 whitespace-nowrap"><?= tgl_jam($l['created_at']) ?></td>
                    <td class="px-4 py-3">
                        <span class="font-medium"><?= $l['user_nama'] ?></span>
                        <br><span class="text-xs text-gray-400"><?= $l['role'] ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                            <?= $l['aktivitas'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?= $l['detail'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?= pagination($currentPage, $totalPages, '?', ['page' => 'log', 'search' => $search, 'user' => $user_filter]) ?>
</div>