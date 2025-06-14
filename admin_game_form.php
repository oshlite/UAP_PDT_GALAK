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

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$kategori_options = [];
$kategori_sql = "SELECT id, name FROM kategori ORDER BY name ASC";
$kategori_result = $conn->query($kategori_sql);
if ($kategori_result && $kategori_result->num_rows > 0) {
    while ($row = $kategori_result->fetch_assoc()) {
        $kategori_options[] = $row;
    }
}


$game_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$title = '';
$genre = '';
$thumbnail = '';
$description = '';
$is_edit = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];
    $existing_thumbnail = $_POST['existing_thumbnail'];
    
    $thumbnail_to_save = $existing_thumbnail;

    if (!empty($_POST['thumbnail_url'])) {
        if (filter_var($_POST['thumbnail_url'], FILTER_VALIDATE_URL)) {
            $thumbnail_to_save = $_POST['thumbnail_url'];
        } else {
            $error_message = "Format URL thumbnail tidak valid.";
        }
    }
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = basename($_FILES["thumbnail_file"]["name"]);
        $unique_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $image_name);
        $target_file = $target_dir . $unique_filename;
        if (move_uploaded_file($_FILES["thumbnail_file"]["tmp_name"], $target_file)) {
            $thumbnail_to_save = $target_file; 
        } else {
            $error_message = "Gagal mengupload file.";
        }
    }

    if (empty($error_message)) {
        if (empty($title) || empty($genre)) {
            $error_message = "Judul dan Kategori (Genre) game harus diisi.";
        } else {
            $conn->begin_transaction();
            try {
                $kategori_stmt = $conn->prepare("SELECT id FROM kategori WHERE name = ? LIMIT 1");
                $kategori_stmt->bind_param('s', $genre);
                $kategori_stmt->execute();
                $kategori_result = $kategori_stmt->get_result();
                if ($kategori_result->num_rows === 0) {
                    $desc_default = 'Kategori otomatis dari form game';
                    $insert_kat = $conn->prepare("INSERT INTO kategori (name, description) VALUES (?, ?)");
                    $insert_kat->bind_param('ss', $genre, $desc_default);
                    $insert_kat->execute();
                }
                if ($posted_id > 0) {
                    $sql = "CALL UpdateGameDescription(?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('is', $posted_id, $description);
                } else {
                    $sql = "CALL AddNewGame(?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssss', $title, $genre, $thumbnail_to_save, $description);
                }
                if ($stmt->execute()) {
                    $conn->commit();
                    header("Location: admin_game_list.php?status=sukses");
                    exit();
                } else {
                    $conn->rollback();
                    $error_message = "Terjadi kesalahan pada database: " . $stmt->error;
                }
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Transaksi gagal: " . $e->getMessage();
            }
        }
    }
}

if ($game_id > 0) {
    $sql_fetch = "SELECT title, genre, thumbnail, description FROM games WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param('i', $game_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($result->num_rows === 1) {
        $game = $result->fetch_assoc();
        $title = $game['title'];
        $genre = $game['genre'];
        $thumbnail = $game['thumbnail'];
        $description = $game['description'];
        $is_edit = true;
    } else {
        die("<h1 class='text-red-600 p-6 font-bold'>Data game tidak ditemukan!</h1>");
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $is_edit ? "Edit Game" : "Tambah Game Baru" ?> - Admin GALAK</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
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
    .btn-submit { background-color: var(--primary); color: white; padding: 0.6rem 1.5rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; }
    .btn-submit:hover { background-color: var(--secondary); box-shadow: 0 0 20px var(--secondary); }
    .form-input { color: #111827; background-color: rgba(255,255,255,0.9); }
    .dark .form-input { color: #E5E7EB; background-color: rgba(55, 65, 81, 0.9); }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
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
            <a href="logout.php" onclick="return confirm('Anda yakin ingin keluar?');" class="px-3 py-1 border-2 border-red-500 rounded-lg text-sm text-red-500 hover:bg-red-500 hover:text-white transition">Logout</a>          </div>
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
    <h1 class="orbitron text-4xl mb-10 text-center select-none"><?= $is_edit ? "Edit Game" : "Tambah Game Baru" ?></h1>
    
    <form method="post" class="glass-card p-8 rounded-2xl max-w-2xl mx-auto space-y-6" novalidate enctype="multipart/form-data">
      <?php if ($error_message): ?>
          <div class="text-red-500 p-3 bg-red-100 dark:bg-red-900/50 border border-red-400 rounded-lg"><?= e($error_message) ?></div>
      <?php endif; ?>
      
      <input type="hidden" name="id" value="<?= $game_id ?>" />
      <input type="hidden" name="existing_thumbnail" value="<?= e($thumbnail) ?>" />

      <div>
        <label for="title" class="block mb-2 font-semibold text-primaryLight dark:text-primaryDark">Judul Game</label>
        <input type="text" id="title" name="title" required value="<?= e($title) ?>" class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 form-input" />
      </div>

      <div>
        <label for="genre" class="block mb-2 font-semibold text-primaryLight dark:text-primaryDark">Kategori (Genre)</label>
        <select id="genre" name="genre" required class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 form-input">
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($kategori_options as $kategori): ?>
                <option value="<?= e($kategori['name']) ?>" <?= ($kategori['name'] == $genre) ? 'selected' : '' ?>>
                    <?= e($kategori['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="text-xs mt-2 dark:text-gray-400 text-gray-600">Jika kategori belum ada, silakan tambah dulu melalui menu "Kelola Kategori".</p>
      </div>

      <div>
        <label class="block mb-2 font-semibold text-primaryLight dark:text-primaryDark">Thumbnail</label>
        <p class="text-xs dark:text-gray-400 text-gray-600 mb-3">Prioritas: Upload File > URL Gambar. Kosongkan keduanya untuk memakai gambar lama.</p>
        <div class="space-y-4">
            <div>
                <label for="thumbnail_file" class="text-sm font-medium">Upload File (Prioritas Utama)</label>
                <input type="file" id="thumbnail_file" name="thumbnail_file" class="w-full mt-1 p-2 rounded-md border border-gray-300 dark:border-gray-600 form-input file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/50 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100" />
            </div>
            <div class="text-center font-semibold">ATAU</div>
            <div>
                <label for="thumbnail_url" class="text-sm font-medium">Masukkan URL Gambar</label>
                <input type="text" id="thumbnail_url" name="thumbnail_url" placeholder="https://example.com/image.jpg" class="w-full mt-1 p-2 rounded-md border border-gray-300 dark:border-gray-600 form-input" />
            </div>
        </div>
        <?php if ($is_edit && !empty($thumbnail)): ?>
            <div class="mt-4">
                <p class="text-sm font-semibold mb-2">Thumbnail Saat Ini:</p>
                <img src="<?= e($thumbnail) ?>" alt="Current Thumbnail" class="w-40 h-auto rounded-md border-2 border-primaryLight">
            </div>
        <?php endif; ?>
      </div>

      <div>
        <label for="description" class="block mb-2 font-semibold text-primaryLight dark:text-primaryDark">Deskripsi</label>
        <textarea id="description" name="description" rows="5" class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 form-input resize-y"><?= e($description) ?></textarea>
      </div>

      <div class="flex justify-end gap-4 pt-4">
        <a href="admin_game_list.php" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">Batal</a>
        <button type="submit" class="btn-submit"><?= $is_edit ? "Update Game" : "Tambah Game" ?></button>
      </div>
    </form>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-16 text-center text-gray-600 dark:text-gray-400 text-sm">
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
  </script>
</body>
</html>
<?php $conn->close(); ?>