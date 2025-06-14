-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 13, 2025 at 09:22 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database_galak`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddNewGame` (IN `p_title` VARCHAR(100), IN `p_genre` VARCHAR(50), IN `p_thumbnail` VARCHAR(255), IN `p_description` TEXT)   BEGIN
    INSERT INTO games (title, genre, thumbnail, description)
    VALUES (p_title, p_genre, p_thumbnail, p_description);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetGameCountByGenre` (IN `genre_param` VARCHAR(100), OUT `game_count` INT)   BEGIN
    SELECT COUNT(*) INTO game_count
    FROM games
    WHERE genre = genre_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateGameDescription` (IN `p_game_id` INT, IN `p_new_description` TEXT)   BEGIN
    UPDATE games
    SET description = p_new_description
    WHERE id = p_game_id;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetGameCountByKategoriId` (`kategori_id` INT) RETURNS INT DETERMINISTIC BEGIN
    DECLARE jumlah INT;
    SELECT COUNT(*)
    INTO jumlah
    FROM games
    WHERE genre = (SELECT name FROM kategori WHERE id = kategori_id LIMIT 1);
    RETURN jumlah;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetGameTitleById` (`game_id` INT) RETURNS VARCHAR(100) CHARSET utf8mb4 DETERMINISTIC READS SQL DATA BEGIN
  DECLARE title_result VARCHAR(100);

  SELECT title INTO title_result
  FROM games
  WHERE id = game_id;

  RETURN title_result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetKategoriNameByGameId` (`game_id` INT) RETURNS VARCHAR(100) CHARSET utf8mb4 DETERMINISTIC BEGIN
    DECLARE kategori_name VARCHAR(100);
    SELECT genre
    INTO kategori_name
    FROM games
    WHERE id = game_id
    LIMIT 1;
    RETURN kategori_name;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `IsUserAdmin` (`user_id` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE is_admin BOOLEAN DEFAULT FALSE;
    DECLARE user_role VARCHAR(50);

    SELECT role INTO user_role
    FROM users
    WHERE id = user_id
    LIMIT 1;

    SET is_admin = (user_role = 'admin');
    RETURN is_admin;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_log`
--

CREATE TABLE `admin_log` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `log_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_log`
--

INSERT INTO `admin_log` (`id`, `admin_id`, `activity`, `log_time`) VALUES
(1, 1, 'Mengedit game: Brain Battle', '2025-06-11 04:22:13'),
(2, 1, 'Mengedit game: SpeedXX', '2025-06-11 04:57:50'),
(3, 1, 'Menambahkan game baru: adudu', '2025-06-12 06:11:30'),
(5, 11, 'Mengedit game: adudu', '2025-06-12 11:08:41'),
(7, 11, 'Mengedit game: adudu', '2025-06-12 11:35:58'),
(13, 1, 'Menambahkan kategori: Multiplayer', '2025-06-13 13:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `title`, `genre`, `thumbnail`, `description`) VALUES
(1, 'Galaxy Runner', 'Action', 'https://cf.geekdo-images.com/sRypSAob197aOAvH1UW6eQ__itemrep/img/cUjuWtznzbI2mlzpN2b2SAykiQ4=/fit-in/246x300/filters:strip_icc()/pic4429692.jpg', 'Game aksi luar angkasa dengan misi menaklukkan asteroid dan alien.'),
(2, 'Brain Battle', 'Pendidikan', 'uploads/684abeffe8c56_game.png', 'Tantangan logika dan strategi untuk menguji kecerdasanmuuuuuu'),
(3, 'Cat Quest', 'Petualangan', 'https://static.promediateknologi.id/crop/0x0:0x0/0x0/webp/photo/p2/250/2024/10/09/9be10909a155d6e9f6fb23f60bf716642a02805f56feeafb394f970612435720-copy-2418601855.jpg', 'Cat Quest adalah game aksi-RPG lucu dan penuh warna yang mengajak pemain menjelajahi dunia fantasi Felingard sebagai seekor kucing pemberani. Dalam misi menyelamatkan saudara yang diculik oleh naga jahat, pemain akan menjelajahi peta dunia terbuka, menyelesaikan berbagai quest, melawan monster, dan menemukan rahasia yang tersembunyi di balik dunia para kucing ini.\r\n\r\nPertempuran dalam game ini bersifat real-time, dengan sistem sihir, senjata, dan perlengkapan yang bisa di-upgrade. Meskipun tampilannya imut dan cerah, Cat Quest menyajikan tantangan strategis yang cukup menarik untuk pemain dari segala usia. Gaya visual 2D dan animasi kartun menambah daya tariknya sebagai game yang santai namun menantang.\r\n\r\nGame ini juga dikenal karena humor ringan, referensi game dan film populer, serta pengalaman bermain yang cocok untuk gamer pemula maupun veteran RPG.\r\n\r\nFitur Utama:\r\n- Dunia open world penuh petualangan.\r\n- Pertempuran real-time yang cepat dan seru.\r\n- Sistem leveling, equipment, dan skill magic.\r\n- Cerita ringan dengan humor dan referensi pop culture.\r\n- Dukungan berbagai platform dengan kontrol intuitif.'),
(4, 'Sonic & SEGA All-Stars Racing', 'Balapan', 'https://i0.wp.com/gamenisasi.com/wp-content/uploads/2024/03/ezgif-6-6e9cec1b0c.webp?resize=840%2C473&ssl=1', 'Sonic & SEGA All-Stars Racing adalah game balap kart yang menampilkan karakter ikonik dari dunia SEGA, termasuk Sonic the Hedgehog, Tails, Dr. Eggman, dan karakter dari game SEGA lainnya seperti AiAi (Super Monkey Ball) dan Amigo (Samba de Amigo). Pemain berlomba di lintasan penuh warna dengan kendaraan unik, menggunakan item spesial dan jurus andalan masing-masing karakter untuk meraih kemenangan.\r\n\r\nDengan mode multiplayer, berbagai pilihan lintasan, dan kemampuan spesial bernama \"All-Star Move\", game ini menyuguhkan pengalaman balapan yang cepat, kompetitif, dan seru ala arcade.'),
(5, 'Rise of Kingdoms', 'Strategi', 'https://www.harapanrakyat.com/wp-content/uploads/2022/07/Game-Rise-of-Kingdoms-Game-Strategi-dengan-Banyak-Peradaban-Keren.jpg', 'Rise of Kingdoms adalah game strategi real-time di mana pemain membangun dan mengembangkan sebuah peradaban dari awal, memilih satu dari berbagai bangsa terkenal seperti Romawi, Cina, atau Viking. Pemain harus mengelola sumber daya, membentuk pasukan, menjelajahi peta dunia, dan berperang melawan musuh untuk memperluas wilayah kekuasaan mereka.\r\n\r\nGame ini menawarkan perpaduan antara strategi pembangunan kota, diplomasi antar pemain, dan pertempuran skala besar yang dinamis, baik dalam mode PvE maupun PvP. Setiap keputusan pemain akan memengaruhi nasib peradaban mereka di sepanjang waktu.'),
(6, 'Adudu Attacks!', 'Aksi', 'https://img.utdstc.com/screen/460/952/460952430d2357d6f5453c3c026c0a42250fecc4e4a7e6677459e773db9baa71:800', 'Adudu Attacks! adalah game aksi arcade berbasis karakter dari serial animasi BoBoiBoy. Dalam game ini, pemain mengambil peran sebagai BoBoiBoy untuk menghadapi serangan dari alien jahat bernama Adudu yang ingin mencuri sumber energi coklat di bumi.\r\n\r\nDengan gaya permainan side-scrolling shooter yang cepat dan menegangkan, pemain harus bergerak ke kanan layar sambil menembak musuh, menghindari rintangan, dan mengumpulkan item kekuatan. BoBoiBoy dapat menggunakan berbagai kekuatannya seperti Elemen Petir atau Tanah untuk menghadapi berbagai jenis musuh dan bos yang lebih kuat.\r\n\r\nGame ini memiliki grafis penuh warna, efek suara yang energik, dan cocok dimainkan oleh anak-anak maupun penggemar setia BoBoiBoy. Tantangan utama terletak pada refleks cepat dan strategi dalam menggunakan kekuatan untuk bertahan hidup sejauh mungkin.\r\n\r\nFitur Utama:\r\n- Menggunakan karakter BoBoiBoy dan kekuatan spesialnya.\r\n- Gameplay tembak-tembakan cepat bergaya arcade.\r\n- Desain level penuh warna dan interaktif.\r\n- Power-up dan musuh yang bervariasi.'),
(7, 'The Sims', 'Simulasi', 'https://miro.medium.com/v2/resize:fit:1053/1*zGydZzGt2dj1K4whWSc4Bg.png', 'The Sims adalah game simulasi kehidupan di mana pemain dapat menciptakan, mengendalikan, dan mengatur kehidupan karakter virtual mulai dari aktivitas harian, pekerjaan, hingga hubungan sosial dalam dunia yang sepenuhnya dapat disesuaikan.');

--
-- Triggers `games`
--
DELIMITER $$
CREATE TRIGGER `after_game_delete` AFTER DELETE ON `games` FOR EACH ROW BEGIN
    INSERT INTO admin_log (admin_id, activity)
    VALUES (@admin_id, CONCAT('Menghapus game: ', OLD.title));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_game_insert` AFTER INSERT ON `games` FOR EACH ROW BEGIN
    INSERT INTO admin_log (admin_id, activity)
    VALUES (@admin_id, CONCAT('Menambahkan game baru: ', NEW.title));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `name`, `description`) VALUES
(1, 'Aksi', 'Game dengan gameplay cepat dan penuh tantangan'),
(2, 'Petualangan', 'Game eksplorasi dunia virtual dan cerita menarikkkk'),
(3, 'Pendidikan', 'Game edukatif untuk meningkatkan keterampilan dan pengetahuan'),
(4, 'Balapan', 'Game kecepatan dan ketangkasan di lintacan'),
(5, 'Strategi', 'Game berpikir logis dan perencanaan jangka panjang'),
(7, 'Simulasi', 'Game yang meniru aktivitas atau situasi dunia nyata untuk memberikan pengalaman belajar, eksplorasi, atau pengambilan keputusan secara interaktif.'),
(9, 'Horor', 'Game yang dapat dimainkan bersama');

--
-- Triggers `kategori`
--
DELIMITER $$
CREATE TRIGGER `insert_kategori` AFTER INSERT ON `kategori` FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (
    @admin_id, CONCAT('Menambahkan kategori: ', NEW.name)
  );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_kategori` AFTER UPDATE ON `kategori` FOR EACH ROW BEGIN
  INSERT INTO admin_log (admin_id, activity)
  VALUES (
    @admin_id, CONCAT('Mengubah kategori: ', OLD.name, ' menjadi ', NEW.name)
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'minyoongi', 'mygpunyamel@gmail.com', '$2y$10$RRbW5TS66UjGYid8xshttukT3D9hhxrPE94WQAiipxcJKMXTiulzu', 'user', '2025-06-12 09:59:02'),
(11, 'adminmel', 'mel21@gmail.com', '$2y$10$0quEz7rlXhRPbNAN7p38eOONmqg5SDno61oWuJg5BzuC4l3FepXQW', 'admin', '2025-06-12 10:37:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_log`
--
ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_log`
--
ALTER TABLE `admin_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
