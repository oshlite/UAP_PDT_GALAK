<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'database_galak';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

function get_game_count_by_genre($genre) {
    global $conn;

    $sql_call = "CALL GetGameCountByGenre(?, @game_count)";
    $stmt = $conn->prepare($sql_call);

    if ($stmt === false) {
        return "Gagal mempersiapkan statement: " . $conn->error;
    }

    $stmt->bind_param("s", $genre);

    if ($stmt->execute()) {
        $stmt->close();
        $result = $conn->query("SELECT @game_count as count");
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        } else {
            return "Gagal mendapatkan jumlah game.";
        }
    } else {
        return "Gagal memanggil stored procedure: " . $stmt->error;
    }
}

function get_game_count_by_kategori_id($kategori_id) {
    global $conn;
    $sql = "SELECT GetGameCountByKategoriId(?) AS jumlah";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Gagal mempersiapkan statement: " . $conn->error;
    }
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jumlah = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $jumlah = $row['jumlah'];
    }
    $stmt->close();
    return $jumlah;
}

$genre_to_count = 'Action';
$game_count = get_game_count_by_genre($genre_to_count);

?>
