<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'database_galak';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

include_once 'hitung_game.php';

$kategori_list = [];
$kategori_sql = "SELECT id, name FROM kategori ORDER BY name ASC";
$kategori_result = $conn->query($kategori_sql);
if ($kategori_result && $kategori_result->num_rows > 0) {
    while($row = $kategori_result->fetch_assoc()) {
        $kategori_list[] = $row;
    }
}

$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

if (!empty($selected_kategori)) {
    $stmt = $conn->prepare("SELECT id, title, genre, thumbnail, description FROM games WHERE genre = ? ORDER BY title ASC");
    $stmt->bind_param("s", $selected_kategori);
} else {
    $stmt = $conn->prepare("SELECT id, title, genre, thumbnail, description FROM games ORDER BY title ASC");
}
$stmt->execute();
$result = $stmt->get_result();

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$selected_genre = 'Action';
$stmt1 = $conn->prepare("CALL GetGameCountByGenre(?, @game_count)");
$stmt1->bind_param("s", $selected_genre);
$stmt1->execute();
$stmt1->close();

$result1 = $conn->query("SELECT @game_count AS game_count");
$row1 = $result1->fetch_assoc();
$jumlah_game_genre = $row1['game_count'];
$result1->free_result();


$kategori_id = 2;
$stmt2 = $conn->prepare("SELECT GetGameCountByKategoriId(?) AS jumlah");
$stmt2->bind_param("i", $kategori_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$jumlah_game_kategori = $row2['jumlah'];
$stmt2->close();

?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GALAK - Katalog Game</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
  </script>
  <style>
    body { font-family: 'Poppins', sans-serif; }
    h1,h2,h3,.orbitron { font-family: 'Orbitron', sans-serif; color: var(--primary); text-shadow: 0 0 10px var(--secondary);}
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 0 10px var(--scrollbar-thumb), 0 0 40px var(--scrollbar-thumb-shadow); transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease; }
    .glass-card:hover { transform: translateY(-8px); box-shadow: 0 0 20px var(--scrollbar-thumb), 0 0 50px var(--scrollbar-thumb-shadow); border-color: rgba(255 255 255 / 0.5); }
    a.neon-btn { display: inline-flex; align-items: center; gap: 0.5rem; background-color: transparent; border: 2px solid var(--scrollbar-thumb); color: var(--scrollbar-thumb); padding: 0.55rem 1.3rem; font-weight: 600; border-radius: 1rem; box-shadow: 0 0 10px var(--scrollbar-thumb); text-transform: uppercase; font-size: 0.9rem; transition: all 0.3s ease; text-decoration: none; cursor: pointer; user-select: none; }
    a.neon-btn:hover, a.neon-btn:focus { background: var(--scrollbar-thumb); color: white; box-shadow: 0 0 15px var(--scrollbar-thumb), 0 0 40px var(--scrollbar-thumb-shadow); transform: scale(1.05); outline: none; }
    .genre-badge { font-weight: 700; font-size: 0.7rem; text-transform: uppercase; color: #F9FAFB; background: linear-gradient(45deg, var(--scrollbar-thumb), var(--scrollbar-thumb-shadow)); padding: 0.2rem 0.6rem; border-radius: 9999px; box-shadow: 0 0 5px var(--scrollbar-thumb), 0 0 10px var(--scrollbar-thumb-shadow); user-select: none; }
    :root { --primary: #3B82F6; --scrollbar-thumb: #3B82F6; --scrollbar-thumb-shadow: #8B5CF6; --surface-bg: rgba(255 255 255 / 0.15); }
    .dark { --primary: #60A5FA; --scrollbar-thumb: #60A5FA; --scrollbar-thumb-shadow: #A78BFA; --surface-bg: rgba(20 20 30 / 0.65); background-color: #111827; color: #E5E7EB; }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
    .filter-btn { padding: 0.5rem 1.25rem; border-radius: 9999px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease; border: 2px solid var(--primary); color: var(--primary); text-decoration: none; }
    .filter-btn:hover { background-color: var(--primary); color: white; transform: translateY(-2px); }
    .filter-btn.active { background-color: var(--primary); color: white; box-shadow: 0 0 15px var(--primary); }
  </style>
</head>
<body class="bg-backgroundLight dark:bg-backgroundDark text-textLight dark:text-textDark transition-colors duration-300 min-h-screen flex flex-col">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <a href="index.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GALAK</a>
      <div class="hidden md:flex flex-grow justify-center items-center gap-6 text-lg font-semibold text-textLight dark:text-textDark">
        <a href="index.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
        <a href="katalog.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog Game</a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <span class="text-gray-400 dark:text-gray-600">|</span>
            <span class="text-base font-medium">Halo, <?= e($_SESSION['username']) ?>!</span>
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
          <a href="logout.php" class="block px-6 py-3 text-red-500 hover:bg-red-500 hover:text-white transition">Logout (<?= e($_SESSION['username']) ?>)</a>
      <?php else: ?>
          <a href="login.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow">
    <h1 class="orbitron text-4xl mb-6 text-center select-none">Katalog Game GALAK</h1>

    <?php if (!empty($kategori_list)): ?>
    <nav class="flex justify-center flex-wrap gap-x-4 gap-y-3 mb-12">
        <a href="katalog.php" class="<?= empty($selected_kategori) ? 'filter-btn active' : 'filter-btn' ?>">Semua</a>
        <?php foreach ($kategori_list as $kategori): ?>
            <?php
                $kategori_nama = e($kategori['name']);
                $is_active = ($selected_kategori === $kategori['name']);
                $class = $is_active ? 'filter-btn active' : 'filter-btn';
                $href = "katalog.php?kategori=" . urlencode($kategori['name']);
            ?>
            <a href="<?= $href ?>" class="<?= $class ?>"><?= $kategori_nama ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php
include_once 'hitung_game.php';
echo "<div class='flex flex-row flex-nowrap overflow-x-auto justify-center items-center gap-2 mb-8 pb-2 scrollbar-thin scrollbar-thumb-primaryLight scrollbar-track-transparent' style='white-space:nowrap;'>";
$colors = [
  'bg-indigo-400 text-white',
  'bg-blue-400 text-white',
  'bg-purple-400 text-white',
  'bg-indigo-400 text-white',
  'bg-blue-400 text-white',
  'bg-purple-400 text-white',
  'bg-indigo-400 text-white',
];
$i = 0;
foreach ($kategori_list as $kategori) {
    $genre = $kategori['name'];
    $jumlah = get_game_count_by_genre($genre);
    $color = $colors[$i % count($colors)];
    echo "<span class='inline-block px-3 py-1 rounded-full font-semibold shadow text-xs mx-1 $color'>" . e($genre) . ": $jumlah</span>";
    $i++;
}
echo "</div>";
?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-12">
      <?php if($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
          <article class="glass-card">
            <div class="relative overflow-hidden rounded-xl">
              <img src="<?= e(!empty($row['thumbnail']) ? $row['thumbnail'] : 'https://via.placeholder.com/300x200') ?>" alt="Thumbnail game <?= e($row['title']) ?>" loading="lazy" class="w-full object-cover h-48 sm:h-56 rounded-t-xl" />
              <span class="genre-badge absolute top-3 left-3"><?= e($row['genre']) ?></span>
            </div>
            <div class="p-6">
              <h3 class="text-2xl font-semibold mb-3"><?= e($row['title']) ?></h3>
              <p class="text-textLight dark:text-textDark mb-5 leading-relaxed select-text text-sm h-20 overflow-hidden"><?= strlen(e($row['description'])) > 120 ? substr(e($row['description']), 0, 120) . '...' : e($row['description']) ?></p>
              <a href="game_detail.php?id=<?= (int)$row['id'] ?>" class="neon-btn" aria-label="Lihat detail <?= e($row['title']) ?>">Detail</a>
            </div>
          </article>
      <?php endwhile; else: ?>
          <p class="text-center text-lg text-gray-500 col-span-full">
            Tidak ada game yang ditemukan<?php if(!empty($selected_kategori)) echo " untuk kategori \"" . e($selected_kategori) . "\""; ?>.
          </p>
      <?php endif; ?>
    </div>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-16 select-none text-center text-gray-600 dark:text-gray-400 text-sm">
    &copy; <?= date("Y") ?> GALAK. Hak cipta dilindungi undang-undang.
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => { menu.classList.toggle('hidden'); });
    
    const toggle = document.getElementById('toggle');
    const htmlElement = document.documentElement;
    function applyTheme(theme) {
      if(theme === 'dark') { htmlElement.classList.add('dark'); } 
      else { htmlElement.classList.remove('dark'); }
    }
    toggle.addEventListener('click', () => {
      const theme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem('theme', theme);
      applyTheme(theme);
    });
    applyTheme(localStorage.getItem('theme') || 'light');
  </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>