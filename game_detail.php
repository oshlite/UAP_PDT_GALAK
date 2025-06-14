<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'database_galak';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("<h1 class='text-red-600 p-6 font-bold'>Koneksi database gagal: " . htmlspecialchars($conn->connect_error) . "</h1>");
}

$game_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($game_id <= 0) {
    die("<h1 class='text-red-600 p-6 font-bold'>ID game tidak valid.</h1>");
}

$sql = "SELECT id, title, genre, thumbnail, description FROM games WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h1 class='text-red-600 p-6 font-bold'>Game tidak ditemukan.</h1>");
}

$game = $result->fetch_assoc();
function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
$title = e($game['title']);
$genre = e($game['genre']);
$desc = e($game['description']);
$thumb = !empty($game['thumbnail']) ? e($game['thumbnail']) : 'https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/87842f2e-9589-4a1d-9ec6-89b3da6ce670.png';

$stmt_kat = $conn->prepare("SELECT GetKategoriNameByGameId(?) AS kategori");
$stmt_kat->bind_param('i', $game_id);
$stmt_kat->execute();
$result_kat = $stmt_kat->get_result();
$kategori_func = $genre;
if ($result_kat && $result_kat->num_rows > 0) {
    $row_kat = $result_kat->fetch_assoc();
    if (!empty($row_kat['kategori'])) {
        $kategori_func = e($row_kat['kategori']);
    }
}
$stmt_kat->close();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $title ?> - Detail Game GALAK</title>

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
    body { font-family: 'Poppins', sans-serif; }
    h1,h2,h3,.orbitron { font-family: 'Orbitron', sans-serif; }
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 0 10px var(--scrollbar-thumb), 0 0 40px var(--scrollbar-thumb-shadow); }
    .genre-badge { display: inline-block; margin-bottom: 1rem; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; color: #F9FAFB; background: linear-gradient(45deg, var(--scrollbar-thumb), var(--scrollbar-thumb-shadow)); padding: 0.2rem 0.6rem; border-radius: 9999px; box-shadow: 0 0 5px var(--scrollbar-thumb), 0 0 10px var(--scrollbar-thumb-shadow); user-select: none; }
    :root { --scrollbar-thumb: #3B82F6; --scrollbar-thumb-shadow: #8B5CF6; --surface-bg: rgba(255 255 255 / 0.15); }
    .dark { --scrollbar-thumb: #60A5FA; --scrollbar-thumb-shadow: #A78BFA; --surface-bg: rgba(20 20 30 / 0.65); background-color: #111827; color: #E5E7EB; }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
  </style>
</head>

<body class="bg-backgroundLight dark:bg-backgroundDark text-textLight dark:text-textDark transition-colors duration-300 min-h-screen flex flex-col">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
      <nav class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="index.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GALAK</a>
        <div class="hidden md:flex flex-grow justify-center items-center gap-8 text-lg font-semibold text-textLight dark:text-textDark">
          <a href="index.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
          <a href="katalog.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog Game</a>
          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <span class="text-gray-400 dark:text-gray-600">|</span>
              <span class="text-base font-medium">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
              <?php if ($_SESSION['role'] === 'admin'): ?>
                  <a href="admin_dashboard.php" class="text-base font-semibold hover:text-primaryLight dark:hover:text-primaryDark transition" title="Dashboard Admin">Dashboard</a>
              <?php endif; ?>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-4">
          <div class="hidden md:flex items-center">
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href="logout.php" onclick="return confirm('Anda yakin ingin keluar?');" class="px-3 py-1 border-2 border-red-500 rounded-lg text-sm text-red-500 hover:bg-red-500 hover:text-white transition">Logout</a>
              <?php else: ?>
                  <a href="login.php" class="px-4 py-2 border-2 border-primaryLight dark:border-primaryDark rounded-xl text-base font-semibold text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition">Login</a>
              <?php endif; ?>
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
        <a href="index.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Beranda</a>
        <a href="katalog.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Katalog Game</a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b hover:bg-primaryLight hover:text-white transition">Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="block px-6 py-3 text-red-500 hover:bg-red-500 hover:text-white transition">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition">Login</a>
        <?php endif; ?>
      </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow">
    <article class="glass-card p-6 sm:p-8 rounded-3xl max-w-4xl mx-auto">
      <h1 class="orbitron text-4xl mb-6 text-primaryLight dark:text-primaryDark select-none"><?= $title ?></h1>
      <img src="<?= $thumb ?>" alt="Thumbnail <?= $title ?>" class="w-full rounded-2xl mb-8 shadow-lg" />
      <span class="genre-badge">Kategori: <?= $kategori_func ?></span>
      <p class="text-textLight dark:text-textDark mb-8 leading-relaxed select-text text-base sm:text-lg"><?= nl2br($desc) ?></p>
      <a href="katalog.php" class="inline-block px-6 py-2 border-2 border-primaryLight dark:border-primaryDark rounded-xl text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition" aria-label="Kembali ke katalog">Kembali ke Katalog</a>
    </article>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-16 select-none text-center text-gray-600 dark:text-gray-400 text-sm">
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
<?php
$stmt->close();
$conn->close();
?>