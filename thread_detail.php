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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // エラーチェック

  if (empty($_POST["comment"])) {
    $errorMessages["comment"] = 'コメントが未入力です。';
  } else if (mb_strlen($_POST["comment"]) > 500) {
    $errorMessages["comment"] = 'コメントが500文字以内で入力してください。';
  }

  if (empty($errorMessages)) {
    try {
      $dsn = 'mysql:host=localhost;dbname=phpkadai;charset=utf8mb4';
      $username = 'kurihara';
      $password = 'uCmCLu2e8H';

      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // コメント登録
      $stmt = $pdo->prepare('INSERT INTO comments (member_id, thread_id, comment, created_at, updated_at) VALUES (:member_id, :thread_id, :comment, NOW(), NOW())');
      $stmt->bindParam(':member_id', $_SESSION['user']['id'], PDO::PARAM_INT);
      $stmt->bindParam(':thread_id', $_POST['thread_id'], PDO::PARAM_INT);
      $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
      $stmt->execute();

      // フォーム情報を削除（二重送信防止）
      unset($_SESSION['form']);

      // 完了後にトップへ
      header('Location: thread_detail.php?id=' . urlencode($_POST['thread_id']));
      exit;
    } catch (PDOException $e) {
      echo 'エラーが発生しました：' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
      exit;
    }
  }
}

// --- ここからコメント一覧取得・ページャー処理 ---
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// 総コメント数を取得
$countStmt = $pdo->prepare('
  SELECT COUNT(*) 
  FROM comments 
  WHERE thread_id = :thread_id
');
$countStmt->bindValue(':thread_id', $id, PDO::PARAM_INT);
$countStmt->execute();
$totalComments = (int)$countStmt->fetchColumn();

// ページごとのコメントを取得
$commentsStmt = $pdo->prepare('
  SELECT c.*, m.name_sei, m.name_mei
  FROM comments AS c
  LEFT JOIN members AS m ON c.member_id = m.id
  WHERE c.thread_id = :thread_id
  ORDER BY c.created_at ASC
  LIMIT :limit OFFSET :offset
');
$commentsStmt->bindValue(':thread_id', $id, PDO::PARAM_INT);
$commentsStmt->bindValue(':limit',    $limit, PDO::PARAM_INT);
$commentsStmt->bindValue(':offset',   $offset, PDO::PARAM_INT);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

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
  <p class="thread-comments">コメント数: <?= $totalComments ?> 件</p>
  <p class="thread-date"><?= date('Y/n/j H:i', strtotime($thread['created_at'])) ?></p>

  <!-- 投稿者情報とコメント -->
  <div class="thread-post">
    <p class="thread-meta">
      投稿者: <?= htmlspecialchars($thread['name_sei'], ENT_QUOTES) ?> <?= htmlspecialchars($thread['name_mei'], ENT_QUOTES) ?>　
      <?= date('Y.n.j H:i', strtotime($thread['created_at'])) ?>
    </p>
    <div class="thread-content">
      <?= nl2br(htmlspecialchars(trim($thread['content']), ENT_QUOTES)) ?>
    </div>
  </div>

  <!-- ページャー -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?id=<?= $id ?>&page=<?= $page - 1 ?>" class="active">« 前へ</a>
    <?php else: ?>
      <span class="disabled">« 前へ</span>
    <?php endif; ?>

    <?php if ($offset + count($comments) < $totalComments): ?>
      <a href="?id=<?= $id ?>&page=<?= $page + 1 ?>" class="active">次へ »</a>
    <?php else: ?>
      <span class="disabled">次へ »</span>
    <?php endif; ?>
  </div>

  <!-- コメント一覧　-->
  <div class="comments-list">
    <h3>コメント一覧</h3>
    <?php if (!empty($comments)): ?>
      <?php foreach ($comments as $c): ?>
        <div class="comment-item">
          <p class="comment-meta">
            <?= htmlspecialchars($c['name_sei'], ENT_QUOTES) ?><?= htmlspecialchars($c['name_mei'], ENT_QUOTES) ?>　
            <?= date('Y/n/j H:i', strtotime($c['created_at'])) ?>
          </p>
          <div class="comment-content">
            <?= nl2br(htmlspecialchars($c['comment'], ENT_QUOTES)) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- ページャー -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?id=<?= $id ?>&page=<?= $page - 1 ?>" class="active">« 前へ</a>
    <?php else: ?>
      <span class="disabled">« 前へ</span>
    <?php endif; ?>

    <?php if ($offset + count($comments) < $totalComments): ?>
      <a href="?id=<?= $id ?>&page=<?= $page + 1 ?>" class="active">次へ »</a>
    <?php else: ?>
      <span class="disabled">次へ »</span>
    <?php endif; ?>
  </div>

  <!-- コメント投稿フォーム（ログイン時のみ） -->
  <?php if ($isLoggedIn): ?>
    <form action="thread_detail.php" method="post" class="comment-form">
      <textarea name="comment" placeholder="コメントを入力してください"></textarea>
      <input type="hidden" name="thread_id" value="<?= htmlspecialchars($thread['id'], ENT_QUOTES) ?>">
      <?php if (!empty($errorMessages['comment'])): ?>
        <p class="error"><?= htmlspecialchars($errorMessages['comment']) ?></p>
      <?php endif; ?>
      <div class="buttons">
        <button type="submit" class="btn">コメントする</button>
      </div>
    </form>
  <?php endif; ?>
</div>