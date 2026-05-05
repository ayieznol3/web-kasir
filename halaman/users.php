<?php
$page = 'users';

// Ambil semua user
$user_list = mysqli_query($conn, "SELECT * FROM users ORDER BY role, nama");
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-users-cog text-primary mr-2"></i>Manajemen User
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun admin dan kasir</p>
        </div>
        <button onclick="showModalTambah()" 
                class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            <i class="fas fa-plus"></i> Tambah User
        </button>
    </div>

    <!-- Tabel User -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Username</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Terdaftar</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php while($u = mysqli_fetch_assoc($user_list)): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                                <?= $u['role'] == 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' ?>">
                                <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                            </div>
                            <span class="font-medium"><?= htmlspecialchars($u['nama']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm"><?= htmlspecialchars($u['username']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs rounded-full font-medium 
                            <?= $u['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                            <i class="fas fa-<?= $u['role'] == 'admin' ? 'shield-alt' : 'user' ?> mr-1"></i>
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= tgl_jam($u['created_at']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <button onclick="editUser(<?= $u['id'] ?>, '<?= addslashes($u['nama']) ?>', '<?= addslashes($u['username']) ?>', '<?= $u['role'] ?>')" 
                                    class="px-3 py-1.5 text-xs border rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <button onclick="hapusUser(<?= $u['id'] ?>, '<?= addslashes($u['nama']) ?>')" 
                                    class="px-3 py-1.5 text-xs border border-red-200 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                            <?php else: ?>
                            <span class="text-xs text-gray-400 px-3 py-1.5">Anda</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==================== MODAL TAMBAH / EDIT ==================== -->
<div id="modal-user" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-bold" id="modal-title">
                <i class="fas fa-user-plus text-primary mr-2"></i>Tambah User
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        
        <form action="proses/user_simpan.php" method="post" class="p-6 space-y-4">
            <input type="hidden" name="id" id="user-id">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="nama" id="user-nama" required 
                       class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none"
                       placeholder="Nama lengkap">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username *</label>
                <input type="text" name="username" id="user-username" required 
                       class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none"
                       placeholder="Username untuk login">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Password <span id="pass-required" class="text-red-500">*</span>
                </label>
                <input type="password" name="password" id="user-password" 
                       class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none"
                       placeholder="Minimal 4 karakter">
                <p class="text-xs text-gray-400 mt-1" id="pass-hint">Kosongkan jika tidak ingin mengubah password</p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Role *</label>
                <select name="role" id="user-role" required 
                        class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 py-2.5 border rounded-xl font-medium hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL HAPUS ==================== -->
<div id="modal-hapus" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold">Hapus User?</h3>
        <p class="text-sm text-gray-500 mt-1" id="hapus-nama">-</p>
        <p class="text-xs text-red-400 mt-2">⚠️ Tidak bisa dibatalkan</p>
        <div class="flex gap-3 mt-4">
            <button onclick="closeModalHapus()" class="flex-1 py-2 border rounded-xl font-medium">Batal</button>
            <a href="#" id="btn-hapus" class="flex-1 py-2 bg-red-500 text-white rounded-xl font-semibold">Hapus</a>
        </div>
    </div>
</div>

<script>
// ==================== MODAL TAMBAH ====================
function showModalTambah() {
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-plus text-primary mr-2"></i>Tambah User';
    document.getElementById('user-id').value = '';
    document.getElementById('user-nama').value = '';
    document.getElementById('user-username').value = '';
    document.getElementById('user-password').value = '';
    document.getElementById('user-role').value = 'kasir';
    document.getElementById('pass-required').classList.remove('hidden');
    document.getElementById('pass-hint').classList.add('hidden');
    document.getElementById('user-password').required = true;
    document.getElementById('modal-user').classList.remove('hidden');
    document.getElementById('user-nama').focus();
}

// ==================== MODAL EDIT ====================
function editUser(id, nama, username, role) {
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-edit text-primary mr-2"></i>Edit User';
    document.getElementById('user-id').value = id;
    document.getElementById('user-nama').value = nama;
    document.getElementById('user-username').value = username;
    document.getElementById('user-password').value = '';
    document.getElementById('user-role').value = role;
    document.getElementById('pass-required').classList.add('hidden');
    document.getElementById('pass-hint').classList.remove('hidden');
    document.getElementById('user-password').required = false;
    document.getElementById('modal-user').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-user').classList.add('hidden');
}

// ==================== HAPUS ====================
function hapusUser(id, nama) {
    document.getElementById('hapus-nama').textContent = nama;
    document.getElementById('btn-hapus').href = 'proses/user_hapus.php?id=' + id;
    document.getElementById('modal-hapus').classList.remove('hidden');
}

function closeModalHapus() {
    document.getElementById('modal-hapus').classList.add('hidden');
}

// Tutup modal
document.getElementById('modal-user').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('modal-hapus').addEventListener('click', function(e) {
    if (e.target === this) closeModalHapus();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeModal(); closeModalHapus(); }
});
</script>