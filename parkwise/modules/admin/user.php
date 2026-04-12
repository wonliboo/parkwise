<?php
/**
 * ParkWise - Kelola User (Admin)
 * Input  : GET (list, search, page) | POST (tambah/edit/hapus)
 * Proses : CRUD tb_user dengan hash password
 * Output : Tabel user + modal form
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Kelola User';
$activePage = 'user';
$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = clean($_GET['q'] ?? '');
$offset     = ($page - 1) * $perPage;

// --- PROSES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $data = [
            ':nama'   => clean($_POST['nama_lengkap'] ?? ''),
            ':user'   => clean($_POST['username'] ?? ''),
            ':pass'   => password_hash($_POST['password'] ?? 'parkwise123', PASSWORD_DEFAULT),
            ':role'   => in_array($_POST['role'], ['admin','petugas','owner']) ? $_POST['role'] : 'petugas',
            ':aktif'  => 1,
        ];
        $pdo->prepare(
            'INSERT INTO tb_user (nama_lengkap,username,password,role,status_aktif)
             VALUES (:nama,:user,:pass,:role,:aktif)'
        )->execute($data);
        logAktivitas('Tambah user: ' . $data[':user']);
        setFlash('success', 'User berhasil ditambahkan.');

    } elseif ($action === 'edit') {
        $id   = (int)($_POST['id_user'] ?? 0);
        $data = [
            ':nama'  => clean($_POST['nama_lengkap'] ?? ''),
            ':role'  => in_array($_POST['role'], ['admin','petugas','owner']) ? $_POST['role'] : 'petugas',
            ':aktif' => (int)($_POST['status_aktif'] ?? 1),
            ':id'    => $id,
        ];
        $sql = 'UPDATE tb_user SET nama_lengkap=:nama, role=:role, status_aktif=:aktif';
        if (!empty($_POST['password'])) {
            $data[':pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql .= ', password=:pass';
        }
        $sql .= ' WHERE id_user=:id';
        $pdo->prepare($sql)->execute($data);
        logAktivitas('Edit user ID: ' . $id);
        setFlash('success', 'User berhasil diperbarui.');

    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id_user'] ?? 0);
        if ($id !== currentUserId()) {
            $pdo->prepare('DELETE FROM tb_user WHERE id_user = :id')->execute([':id' => $id]);
            logAktivitas('Hapus user ID: ' . $id);
            setFlash('success', 'User berhasil dihapus.');
        } else {
            setFlash('error', 'Tidak bisa menghapus akun sendiri.');
        }
    }

    redirect(BASE_URL . '/modules/admin/user.php');
}

// --- QUERY DATA ---
$whereClause = $search ? "WHERE (nama_lengkap LIKE :q OR username LIKE :q OR role LIKE :q)" : '';
$params      = $search ? [':q' => "%$search%"] : [];

// Total untuk pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM tb_user $whereClause");
$totalStmt->execute($params);
$total    = (int)$totalStmt->fetchColumn();
$totalPgs = max(1, (int)ceil($total / $perPage));

// Data user dengan LIMIT
$params[':limit']  = $perPage;
$params[':offset'] = $offset;
$users = $pdo->prepare("SELECT * FROM tb_user $whereClause ORDER BY id_user DESC LIMIT :limit OFFSET :offset");
$users->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$users->bindValue(':offset', $offset,  PDO::PARAM_INT);
if ($search) $users->bindValue(':q', "%$search%");
$users->execute();
$users = $users->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="filter-bar">
  <div class="search-wrap">
    <i data-lucide="search"></i>
    <input type="text" class="search-input" id="searchInput" placeholder="Cari nama, username, role..."
           value="<?= htmlspecialchars($search) ?>"
           onkeyup="liveSearch(this.value)">
  </div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">
    <i data-lucide="user-plus"></i> Tambah User
  </button>
</div>

<div class="panel">
  <div class="panel-header">
    <h2>Daftar User (<?= $total ?>)</h2>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Lengkap</th>
          <th>Username</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        <?php if (empty($users)): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">Tidak ada data.</td></tr>
        <?php else: foreach ($users as $i => $u): ?>
        <tr>
          <td class="text-muted text-small"><?= $offset + $i + 1 ?></td>
          <td style="font-weight:500;"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td>
            <span class="badge <?= $u['status_aktif'] ? 'badge-aktif' : 'badge-nonaktif' ?>">
              <?= $u['status_aktif'] ? 'Aktif' : 'Nonaktif' ?>
            </span>
          </td>
          <td>
            <button class="btn btn-outline btn-sm" onclick='editUser(<?= json_encode($u) ?>)'>
              <i data-lucide="edit-2"></i> Edit
            </button>
            <?php if ($u['id_user'] !== currentUserId()): ?>
            <form method="POST" style="display:inline;" id="delForm<?= $u['id_user'] ?>">
              <input type="hidden" name="action" value="hapus">
              <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="confirmDelete('delForm<?= $u['id_user'] ?>', '<?= htmlspecialchars($u['nama_lengkap'], ENT_QUOTES) ?>')">
                <i data-lucide="trash-2"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPgs > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= $totalPgs; $p++): ?>
    <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i data-lucide="user-plus"></i> Tambah User</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter">
          </div>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" class="form-control">
            <option value="admin">Admin</option>
            <option value="petugas" selected>Petugas</option>
            <option value="owner">Owner</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i data-lucide="edit-2"></i> Edit User</h3>
      <button class="modal-close" onclick="closeModal('modalEdit')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="editForm">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_user" id="editId">
      <div class="modal-body">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" id="editNama" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
          <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Role</label>
            <select name="role" id="editRole" class="form-control">
              <option value="admin">Admin</option>
              <option value="petugas">Petugas</option>
              <option value="owner">Owner</option>
            </select>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status_aktif" id="editStatus" class="form-control">
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editUser(u) {
  document.getElementById('editId').value     = u.id_user;
  document.getElementById('editNama').value   = u.nama_lengkap;
  document.getElementById('editRole').value   = u.role;
  document.getElementById('editStatus').value = u.status_aktif;
  openModal('modalEdit');
  lucide.createIcons();
}

function liveSearch(q) {
  const url = new URL(window.location.href);
  url.searchParams.set('q', q);
  url.searchParams.set('page', 1);
  clearTimeout(window._searchTimer);
  window._searchTimer = setTimeout(() => window.location.href = url.toString(), 500);
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
