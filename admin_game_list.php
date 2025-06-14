<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'database_galak';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("<h1 class='text-red-600 p-6 font-bold'>Koneksi database gagal: " . htmlspecialchars($conn->connect_error) . "</h1>");
}

include_once 'hitung_game.php';

$kategori_list = [];
$kategori_sql = "SELECT name FROM kategori ORDER BY name ASC";
$kategori_result = $conn->query($kategori_sql);
if ($kategori_result && $kategori_result->num_rows > 0) {
    while($row = $kategori_result->fetch_assoc()) {
        $kategori_list[] = $row;
    }
}

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $stmt_delete = $conn->prepare("DELETE FROM games WHERE id = ?");
    $stmt_delete->bind_param('i', $id_hapus);
    $stmt_delete->execute();
    $stmt_delete->close();
    header("Location: admin_game_list.php?status=sukses_hapus");
    exit;
}

$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$sql = "SELECT id, title, genre, thumbnail, description FROM games";
if (!empty($selected_kategori)) {
    $sql .= " WHERE genre = ?";
}
$sql .= " ORDER BY title ASC";

$stmt = $conn->prepare($sql);
if (!empty($selected_kategori)) {
    $stmt->bind_param("s", $selected_kategori);
}
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Kelola Game</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primaryLight: '#3B82F6', primaryDark: '#60A5FA', secondaryLight: '#8B5CF6', secondaryDark: '#A78BFA', backgroundLight: '#F9FAFB', backgroundDark: '#111827', surfaceLight: 'rgba(255 255 255 / 0.15)', surfaceDark: 'rgba(20 20 30 / 0.65)', textLight: '#111827', textDark: '#E5E7EB', }, fontFamily: { orbitron: ['Orbitron', 'sans-serif'], poppins: ['Poppins', 'sans-serif'], } } } }
  </script>
  <style>
    :root { --primary: #3B82F6; --secondary: #8B5CF6; --background: #F9FAFB; --text: #111827; --surface-bg: rgba(255 255 255 / 0.15); --shadow: rgba(59, 130, 246, 0.4); }
    .dark { --primary: #60A5FA; --secondary: #A78BFA; --background: #111827; --text: #E5E7EB; --surface-bg: rgba(20 20 30 / 0.65); --shadow: rgba(96, 165, 250, 0.6); }
    body { font-family: 'Poppins', sans-serif; }
    h1,h2,h3,.orbitron { font-family: 'Orbitron', sans-serif; color: var(--primary); text-shadow: 0 0 10px var(--secondary); }
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 4px 20px var(--shadow); }
    .btn-primary { background-color: var(--primary); color: white; padding: 0.5rem 1.5rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; border: none; cursor: pointer; }
    .btn-primary:hover { background-color: var(--secondary); box-shadow: 0 0 20px var(--secondary); }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
    .form-input { color: #111827; background-color: rgba(255,255,255,0.9); }
    .dark .form-input { color: #E5E7EB; background-color: rgba(55, 65, 81, 0.9); }
  </style>
</head>
<body class="bg-backgroundLight dark:bg-backgroundDark text-textLight dark:text-textDark transition-colors duration-300 min-h-screen flex flex-col">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
      <nav class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="admin_dashboard.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GAL<span class="text-secondaryLight dark:text-secondaryDark">AK</span></a>
        <div class="hidden md:flex flex-grow justify-center items-center gap-6 text-lg font-semibold text-textLight dark:text-textDark">
          <a href="admin_dashboard.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
          <a href="katalog.php?context=admin" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog</a>
          <a href="admin_game_list.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Kelola Game</a>
          <a href="admin_kategori_list.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Kelola Kategori</a>
          <span class="text-gray-400 dark:text-gray-600">|</span>
          <span class="text-base font-medium">Halo, <?= e($_SESSION['username']) ?>!</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="hidden md:flex items-center">
            <a href="logout.php" onclick="return confirm('Anda yakin ingin keluar?');" class="px-3 py-1 border-2 border-red-500 rounded-lg text-sm text-red-500 hover:bg-red-500 hover:text-white transition">Logout</a>
          </div>
          <div id="toggle" class="toggle-bg" role="switch" aria-checked="false" tabindex="0">
            <div class="toggle-circle"></div>
          </div>
          <button id="menu-btn" aria-label="Toggle menu" aria-expanded="false" class="md:hidden focus:outline-none text-primaryLight dark:text-primaryDark">
            <span class="material-icons text-4xl select-none">menu</span>
          </button>
        </div>
      </nav>
      <nav id="mobile-menu" class="hidden md:hidden bg-backgroundLight dark:bg-backgroundDark border-t border-gray-300 dark:border-gray-700 shadow-lg">
        <a href="admin_dashboard.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Beranda</a>
        <a href="katalog.php?context=admin" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Katalog</a>
        <a href="admin_game_list.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Kelola Game</a>
        <a href="admin_kategori_list.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Kelola Kategori</a>
        <a href="logout.php" class="block px-6 py-3 text-red-500 hover:bg-red-500 hover:text-white transition">Logout (<?= e($_SESSION['username']) ?>)</a>
      </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <h1 class="orbitron text-4xl select-none">Kelola Game</h1>
      <a href="admin_game_form.php" class="btn-primary">Tambah Game</a>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
      <?php foreach ($kategori_list as $kategori):
        $genre = $kategori['name'];
        $jumlah = get_game_count_by_genre($genre);
      ?>
        <span class="inline-block px-4 py-2 rounded-full bg-primaryLight/20 dark:bg-primaryDark/30 text-primaryLight dark:text-primaryDark font-semibold shadow hover:scale-105 transition-all select-none">
          <?= e($genre) ?> <span class="ml-1 text-xs font-bold">(<?= $jumlah ?>)</span>
        </span>
      <?php endforeach; ?>
    </div>

    <div class="mb-8 p-4 glass-card rounded-xl">
        <form method="GET" action="admin_game_list.php" class="flex items-center gap-4">
            <label for="kategori-filter" class="font-semibold">Filter Kategori:</label>
            <select name="kategori" id="kategori-filter" class="form-input p-2 border rounded-md flex-grow">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori_list as $kategori): ?>
                    <option value="<?= e($kategori['name']) ?>" <?= ($selected_kategori == $kategori['name']) ? 'selected' : '' ?>>
                        <?= e($kategori['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary py-2 px-6">Filter</button>
            <a href="admin_game_list.php" class="text-sm hover:underline">Reset</a>
        </form>
    </div>

    <div class="overflow-x-auto glass-card p-4 rounded-2xl">
      <table class="min-w-full table-auto">
        <thead class="border-b-2 border-primaryLight dark:border-primaryDark">
          <tr>
            <th class="px-4 py-3 text-left">ID</th>
            <th class="px-4 py-3 text-left">Thumbnail</th>
            <th class="px-4 py-3 text-left">Judul</th>
            <th class="px-4 py-3 text-left">Kategori/Genre</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-blue-100/50 dark:hover:bg-blue-900/20">
              <td class="px-4 py-2 align-middle"><?= e($row['id']) ?></td>
              <td class="px-4 py-2 align-middle">
                <img src="<?= !empty($row['thumbnail']) ? e($row['thumbnail']) : 'https://via.placeholder.com/100x60' ?>" alt="thumb" class="w-24 h-14 object-cover rounded-md">
              </td>
              <td class="px-4 py-2 align-middle font-semibold"><?= e($row['title']) ?></td>
              <td class="px-4 py-2 align-middle"><?= e($row['genre']) ?></td>
              <td class="px-4 py-2 align-middle text-center">
                <a href="admin_game_form.php?id=<?= e($row['id']) ?>" class="text-blue-500 hover:underline mr-4">Edit</a>
                <a href="admin_game_list.php?hapus=<?= e($row['id']) ?>" class="text-red-500 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus game: <?= addslashes(e($row['title'])) ?>?')">Hapus</a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="5" class="text-center py-8">
                Tidak ada game yang ditemukan<?php if(!empty($selected_kategori)) echo " untuk kategori \"" . e($selected_kategori) . "\""; ?>.
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-16 text-center text-gray-600 dark:text-gray-400 text-sm">
      &copy; <?= date("Y") ?> GALAK. Hak cipta dilindungi undang-undang.
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    
    const toggle = document.getElementById('toggle');
    const htmlElement = document.documentElement;
    function applyTheme(theme) {
      if(theme === 'dark') {
        htmlElement.classList.add('dark');
        toggle.setAttribute('aria-checked', 'true');
      } else {
        htmlElement.classList.remove('dark');
        toggle.setAttribute('aria-checked', 'false');
      }
    }
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);

    toggle.addEventListener('click', () => {
      const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem('theme', newTheme);
      applyTheme(newTheme);
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>