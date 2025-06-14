<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        header('Location: index.php');
    } else {
        header('Location: login.php');
    }
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

$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
if ($user_id === null) {
    header('Location: login.php');
    exit;
}
$stmt = $conn->prepare("SELECT IsUserAdmin(?) AS is_admin");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row || !$row['is_admin']) {
    header("Location: login.php");
    exit;
}

function getTotalGames($conn) {
    $sql = "SELECT COUNT(*) as total FROM games";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}
function getTotalKategori($conn) {
    $sql = "SELECT COUNT(*) as total FROM kategori";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - GALAK</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primaryLight: '#3B82F6', primaryDark: '#60A5FA',
            secondaryLight: '#8B5CF6', secondaryDark: '#A78BFA',
            backgroundLight: '#F9FAFB', backgroundDark: '#111827',
            surfaceLight: 'rgba(255 255 255 / 0.15)', surfaceDark: 'rgba(20 20 30 / 0.65)',
            textLight: '#111827', textDark: '#E5E7EB',
          },
          fontFamily: {
            orbitron: ['Orbitron', 'sans-serif'], poppins: ['Poppins', 'sans-serif'],
          }
        }
      }
    }
  </script>
  
  <style>
    :root { --primary: #3B82F6; --secondary: #8B5CF6; --background: #F9FAFB; --text: #111827; --surface-bg: rgba(255 255 255 / 0.15); --shadow: rgba(59, 130, 246, 0.4); }
    .dark { --primary: #60A5FA; --secondary: #A78BFA; --background: #111827; --text: #E5E7EB; --surface-bg: rgba(20 20 30 / 0.65); --shadow: rgba(96, 165, 250, 0.6); }
    body { font-family: 'Poppins', sans-serif; background-color: var(--background); color: var(--text); }
    .orbitron, h1, h2, h3 { font-family: 'Orbitron', sans-serif; color: var(--primary); text-shadow: 0 0 10px var(--secondary); }
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 4px 20px var(--shadow); padding: 2rem; transition: box-shadow 0.3s ease; user-select: none; }
    .glass-card:hover { box-shadow: 0 8px 40px var(--shadow); }
    .card { background: var(--surface-bg); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 15px var(--shadow); transition: all 0.3s ease; text-decoration: none; display: block; }
    .card:hover { box-shadow: 0 8px 30px var(--shadow); transform: translateY(-5px); }
    .card h2 { color: var(--primary); }
    .card p { color: var(--text); font-family: 'Poppins', sans-serif; }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
  </style>
</head>
<body class="bg-backgroundLight dark:bg-backgroundDark text-textLight dark:text-textDark transition-colors duration-300 flex flex-col min-h-screen">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <a href="admin_dashboard.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GAL<span class="text-secondaryLight dark:text-secondaryDark">AK</span></a>
      
      <div class="hidden md:flex flex-grow justify-center items-center gap-6 text-lg font-semibold text-textLight dark:text-textDark">
        <a href="admin_dashboard.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
        <a href="katalog.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog</a>
        <a href="admin_game_list.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Kelola Game</a>
        <a href="admin_kategori_list.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Kelola Kategori</a>
        <span class="text-gray-400 dark:text-gray-600">|</span>
        <span class="text-base font-medium">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
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
      <a href="katalog.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Katalog</a>
      <a href="admin_game_list.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Kelola Game</a>
      <a href="admin_kategori_list.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Kelola Kategori</a>
      <a href="logout.php" class="block px-6 py-3 text-red-500 hover:bg-red-500 hover:text-white transition">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
    </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow">
    <h1 class="orbitron text-5xl mb-8 select-none">Dashboard Admin</h1>
    <p class="text-lg mb-12 text-textLight dark:text-textDark max-w-2xl">Selamat datang di panel admin GALAK. Gunakan menu navigasi untuk mengelola konten website.</p>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
      <div class="glass-card text-center">
        <h2 class="text-xl font-bold mb-2">Total Game</h2>
        <div class="text-xl font-extrabold text-primaryLight dark:text-primaryDark"><?= getTotalGames($conn) ?></div>
      </div>
      <div class="glass-card text-center">
        <h2 class="text-xl font-bold mb-2">Total Kategori</h2>
        <div class="text-xl font-extrabold text-secondaryLight dark:text-secondaryDark"><?= getTotalKategori($conn) ?></div>
      </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <a href="admin_game_list.php" class="card">
        <h2 class="text-2xl mb-4">Kelola Game</h2>
        <p>Tambah, edit, atau hapus data game dari katalog.</p>
      </a>
      <a href="admin_kategori_list.php" class="card">
        <h2 class="text-2xl mb-4">Kelola Kategori</h2>
        <p>Atur kategori atau genre untuk setiap game.</p>
      </a>
      <a href="katalog.php" class="card" target="_blank">
        <h2 class="text-2xl mb-4">Lihat Katalog Publik</h2>
        <p>Tampilkan katalog game seperti yang dilihat oleh pengunjung.</p>
      </a>
    </section>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-auto select-none text-center text-gray-600 dark:text-gray-400 text-sm">
    &copy; <?= date("Y") ?> GALAK. Hak cipta dilindungi undang-undang.
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', !expanded);
      menu.classList.toggle('hidden');
    });

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
    function getPreferredTheme() {
      if(localStorage.getItem('theme')) { return localStorage.getItem('theme'); }
      if(window.matchMedia('(prefers-color-scheme: dark)').matches) { return 'dark'; }
      return 'light';
    }
    applyTheme(getPreferredTheme());
    toggle.addEventListener('click', () => {
      if(htmlElement.classList.contains('dark')) {
        applyTheme('light');
        localStorage.setItem('theme', 'light');
      } else {
        applyTheme('dark');
        localStorage.setItem('theme', 'dark');
      }
    });
    toggle.addEventListener('keydown', e => {
      if(e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle.click();
      }
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>