<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  try {
    // DB接続
    $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
    $user = 'kurihara';
    $password = 'uCmCLu2e8H';
    $pdo = new PDO($dsn, $user, $password);

    $id = $_GET['id'];
    $stmt = $pdo->prepare('
      SELECT 
        threads.*, 
        members.name_sei, 
        members.name_mei
      FROM threads
      LEFT JOIN members ON threads.member_id = members.id
      WHERE threads.id = :id
    ');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log('DBエラー: ' . $e->getMessage());
    echo 'DBエラーが発生しました。';
    exit;
  }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スレッド一覧</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>


<div class="thread-container">
  <div class="thread-header">
    <a href="logout.php" class="btn back">スレッド一覧に戻る</a>
  </div>

  <!-- スレッドタイトルと作成日時 -->
  <h2 class="thread-title"><?= htmlspecialchars($thread['title'], ENT_QUOTES) ?></h2>
  <p class="thread-date"><?= date('Y/n/j H:i', strtotime($thread['created_at'])) ?></p>

  <!-- 投稿者情報とコメント -->
  <div class="thread-post">
    <p class="thread-meta">
      投稿者: <?= htmlspecialchars($thread['name_sei'], ENT_QUOTES) ?> <?= htmlspecialchars($thread['name_mei'], ENT_QUOTES) ?>　
      <?= date('Y.n.j H:i', strtotime($thread['created_at'])) ?>
    </p>
    <div class="thread-content">
      <?= nl2br(htmlspecialchars($thread['content'], ENT_QUOTES)) ?>
    </div>
  </div>

  <!-- コメント投稿フォーム（ログイン時のみ） -->
  <?php if ($isLoggedIn): ?>
    <form action="comment_post.php" method="post" class="comment-form">
      <textarea name="comment" placeholder="コメントを入力してください"></textarea>
      <div class="buttons">
        <button type="submit" class="btn">コメントする</button>
      </div>
    </form>
  <?php endif; ?>
</div>