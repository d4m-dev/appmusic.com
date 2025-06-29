<?php
include '../inc/db.php';
session_start();

$is_admin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
$user_id = $_SESSION['user']['user_id'] ?? null;

try {
    $database = new Database();
    $conn = $database->connect();

    $query_song = "SELECT * FROM songs ORDER BY views DESC LIMIT 1";
    $stmt_song = $conn->query($query_song);
    $top_song = $stmt_song ? $stmt_song->fetch(PDO::FETCH_ASSOC) : null;

    if ($user_id) {
        $query_playlist = "SELECT * FROM playlists WHERE user_id = :user_id";
        $stmt_playlist = $conn->prepare($query_playlist);
        $stmt_playlist->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_playlist->execute();
        $playlists = $stmt_playlist->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $playlists = [];
    }

} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

$sort = $_GET['sort'] ?? 'title';
$search = $_GET['search'] ?? '';
$search_query = $search ? "AND (title LIKE :search OR artist LIKE :search OR genre LIKE :search)" : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tìm Kiếm</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background-color: white; }

    .search-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px 10px;
    }

    .search-input {
      border-radius: 12px;
      padding: 10px 20px;
      width: 100%;
      max-width: 400px;
      border: 1px solid #38a169;
      outline: none;
    }

    .search-button {
      background-color: #38a169;
      color: white;
      white-space: nowrap;
      border: none;
      padding: 10px 20px;
      border-radius: 12px;
      margin-left: 10px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .search-button:hover {
      background-color: #48bb78;
    }

    /* Card tam giác */
    .card {
      position: relative;
      width: 100%;
      height: 200px;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      font-family: 'Segoe UI', sans-serif;
    }

    .card .triangle-top {
      position: absolute;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #FF416C, #FF4B2B);
      clip-path: polygon(0 0, 60% 0, 0 60%);
      z-index: 2;
    }

    .card .triangle-top .content {
      position: absolute;
      top: 2px;
      left: 4px;
      color: white;
      max-width: 60%;
    }

    .card .triangle-top .content p {
      background: white;
      color: #FF416C;
      font-size: 8px;
      padding: 2px 8px;
      border-radius: 9999px;
      font-weight: bold;
      display: inline-block;
      margin-top: 2px;
      text-align: center;
    }

    .card .triangle-bottom {
      position: absolute;
      width: 100%;
      height: 100%;
      clip-path: polygon(60% 0, 100% 0, 100% 100%, 0 100%, 0 60%);
      z-index: 1;
    }

    .card .triangle-bottom img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .card:hover .triangle-bottom img {
      transform: scale(1.08);
    }
  </style>
</head>
<body>
<?php include('../templates/header.php'); ?>

<div class="search-container">
  <form method="GET" action="song.php" class="flex items-center w-full">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm bài hát..." class="search-input">
    <button type="submit" class="search-button">Tìm kiếm</button>
  </form>
</div>

<a class="ml-[10px] text-[#2d3748] text-xl font-bold mt-3 mb-2 block">Duyệt tìm tất cả</a>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
  <!-- Card Nhạc -->
  <div class="card">
    <a href="song.php" class="block h-full relative">
      <div class="triangle-top">
        <div class="content">
          <h2 class="text-white text-md font-bold uppercase">Nhạc</h2>
          <p>HOT</p>
        </div>
      </div>
      <div class="triangle-bottom">
        <img src="https://i.postimg.cc/mZCX0nqB/marcela-laskoski-Yrt-Flr-Lo2-DQ-unsplash.jpg" alt="Nhạc">
      </div>
    </a>
  </div>

  <!-- Thể Loại -->
  <div class="card">
    <a href="genre.php" class="block h-full relative">
      <div class="triangle-top">
        <div class="content">
          <h2 class="text-white text-md font-bold uppercase">Thể loại</h2>
          <p>2025</p>
        </div>
      </div>
      <div class="triangle-bottom">
        <img src="https://i.postimg.cc/dt9W0ywf/wes-hicks-MEL-j-Jnm7-RQ-unsplash.jpg" alt="Thể Loại">
      </div>
    </a>
  </div>

  <!-- Playlist -->
  <div class="card">
    <a href="playlist.php" class="block h-full relative">
      <div class="triangle-top">
        <div class="content">
          <h2 class="text-white text-md font-bold uppercase">Playlist</h2>
          <p>CỦA BẠN</p>
        </div>
      </div>
      <div class="triangle-bottom">
        <img src="https://i.postimg.cc/brxmkGbs/adrian-korte-5gn2soe-Ac40-unsplash.jpg" alt="Playlist">
      </div>
    </a>
  </div>

  <!-- Mới Phát Hành -->
  <div class="card">
    <a href="new_releases.php" class="block h-full relative">
      <div class="triangle-top">
        <div class="content">
          <h2 class="text-white text-md font-bold uppercase">Phát hành</h2>
          <p>MỚI</p>
        </div>
      </div>
      <div class="triangle-bottom">
        <img src="https://i.postimg.cc/cJMx6hk3/chris-yang-o-X-Ry-Hi-CZ-w-unsplash.jpg" alt="Mới phát hành">
      </div>
    </a>
  </div>

  <!-- Quản lý (admin) -->
  <?php if ($is_admin): ?>
  <div class="card">
    <a href="../admin/admin.php" class="block h-full relative">
      <div class="triangle-top">
        <div class="content">
          <h2 class="text-white text-md font-bold uppercase">Quản lý</h2>
          <p>ADMIN</p>
        </div>
      </div>
      <div class="triangle-bottom">
        <img src="https://i.postimg.cc/jdTgKX0N/path-digital-t-R0jvlsm-Cu-Q-unsplash.jpg" alt="Quản lý">
      </div>
    </a>
  </div>
  <?php endif; ?>
</div>

<?php include('../templates/menu.php'); ?>
<?php include('../templates/footer.php'); ?>
</body>
</html>