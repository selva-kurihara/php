<?php

session_start();

$isLoggedIn = isset($_SESSION['user']);

try {
  // DB接続
  $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
  $user = 'kurihara';
  $password = 'uCmCLu2e8H';
  $pdo = new PDO($dsn, $user, $password);

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    $stmt = $pdo->prepare('SELECT * FROM threads WHERE title LIKE :keyword OR content LIKE :keyword ORDER BY created_at DESC');
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->execute();
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $stmt = $pdo->prepare('SELECT * FROM threads ORDER BY created_at DESC');
    $stmt->execute();
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (PDOException $e) {
  error_log('DBエラー: ' . $e->getMessage());
  echo 'DBエラーが発生しました。';
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スレッド一覧</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body class="thread-page">
  <div class="thread-container">
    <?php if ($isLoggedIn): ?>
      <div class="thread-header">
        <a href="thread_regist.php">新規スレッド作成</a>
      </div>
    <?php endif; ?>
    <form class="thread-search-form" action="thread.php" method="get">
      <input type="text" name="keyword" placeholder="スレッドを検索" value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES) ?>">
      <button type="submit">スレッド検索</button>
    </form>

    <div class="thread-list">
      <?php if (!empty($threads)): ?>
        <?php foreach ($threads as $thread): ?>
          <div>
            <a href="thread_detail.php?id=<?= urlencode($thread['id'])?>">
              ID:<?= htmlspecialchars($thread['id'], ENT_QUOTES) ?>
              <?= htmlspecialchars($thread['title'], ENT_QUOTES) ?>
              <?= date('Y.n.j H:i', strtotime($thread['created_at'])) ?>
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="back-to-top">
      <a href="logout.php">トップに戻る</a>
    </div>
  </div>
</body>

</html>