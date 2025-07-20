<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);

$id = $_GET['id'] ?? $_POST['thread_id'] ?? null;
if (!$id) {
  echo 'スレッドが指定されていません';
  exit;
}

// スレッド詳細情報の取得
try {
  $pdo = new PDO(
    'mysql:dbname=phpkadai;host=localhost;charset=utf8',
    'kurihara',
    'uCmCLu2e8H',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
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

// コメント一覧取得・ページャー処理
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

// いいね登録・削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['thread_id'])) {
  $commentId = (int)$_POST['comment_id'];
  $memberId = (int)$_SESSION['user']['id'];
  $threadId = (int)$_POST['thread_id'];

  try {
    // 既にいいねしているか確認
    $stmt = $pdo->prepare('SELECT * FROM likes WHERE member_id = :member_id AND comment_id = :comment_id');
    $stmt->execute([':member_id' => $memberId, ':comment_id' => $commentId]);
    $like = $stmt->fetch();

    if ($like) {
      // いいね解除
      $stmt = $pdo->prepare('DELETE FROM likes WHERE member_id = :member_id AND comment_id = :comment_id');
      $stmt->execute([':member_id' => $memberId, ':comment_id' => $commentId]);
    } else {
      // いいね追加
      $stmt = $pdo->prepare('INSERT INTO likes (member_id, comment_id) VALUES (:member_id, :comment_id)');
      $stmt->execute([':member_id' => $memberId, ':comment_id' => $commentId]);
    }

    header("Location: thread_detail.php?id={$threadId}&page={$page}");
    exit;
  } catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
  }
}

// コメント登録
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['comment']) {

  // エラーチェック
  $errorMessages = [];
  if (empty($_POST["comment"])) {
    $errorMessages["comment"] = 'コメントが未入力です。';
  } else if (mb_strlen($_POST["comment"]) > 500) {
    $errorMessages["comment"] = 'コメントが500文字以内で入力してください。';
  }

  if (empty($errorMessages)) {
    try {
      // 登録処理
      $stmt = $pdo->prepare('INSERT INTO comments (member_id, thread_id, comment, created_at, updated_at) VALUES (:member_id, :thread_id, :comment, NOW(), NOW())');
      $stmt->bindParam(':member_id', $_SESSION['user']['id'], PDO::PARAM_INT);
      $stmt->bindParam(':thread_id', $_POST['thread_id'], PDO::PARAM_INT);
      $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
      $stmt->execute();

      // フォーム情報を削除（二重送信防止）
      unset($_SESSION['form']);

      // 同じページを表示
      header('Location: thread_detail.php?id=' . urlencode($_POST['thread_id']) . '&page=' . urlencode($_POST['page']));
      exit;
    } catch (PDOException $e) {
      echo 'エラーが発生しました：' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
      exit;
    }
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
    <a href="thread.php" class="btn back">スレッド一覧に戻る</a>
  </div>

  <!-- スレッドタイトルと作成日時 -->
  <h2 class="thread-title"><?= htmlspecialchars($thread['title'], ENT_QUOTES) ?></h2>
  <p class="thread-comments">コメント数: <?= $totalComments ?> 件</p>
  <p class="thread-date"><?= date('Y/n/j H:i', strtotime($thread['created_at'])) ?></p>

  <!-- 投稿者情報とスレッド -->
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
      <a href="thread_detail.php?id=<?= $id ?>&page=<?= $page - 1 ?>" class="active">« 前へ</a>
    <?php else: ?>
      <span class="disabled">« 前へ</span>
    <?php endif; ?>

    <?php if ($offset + count($comments) < $totalComments): ?>
      <a href="?id=<?= $id ?>&page=<?= $page + 1 ?>" class="active">次へ »</a>
    <?php else: ?>
      <span class="disabled">次へ »</span>
    <?php endif; ?>
  </div>

  <!-- コメント一覧 -->
  <div class="comments-list">
    <h3>コメント一覧</h3>
    <?php if (!empty($comments)): ?>
      <?php foreach ($comments as $c): ?>
        <?php
        // いいね数取得
        $likeStmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE comment_id = :comment_id');
        $likeStmt->execute([':comment_id' => $c['id']]);
        $likeCount = $likeStmt->fetchColumn();

        // ログインユーザーがいいねしているか
        $userLiked = false;
        if ($isLoggedIn) {
          $checkStmt = $pdo->prepare('SELECT 1 FROM likes WHERE member_id = :member_id AND comment_id = :comment_id');
          $checkStmt->execute([
            ':member_id' => $_SESSION['user']['id'],
            ':comment_id' => $c['id']
          ]);
          $userLiked = $checkStmt->fetch() !== false;
        }
        ?>
        <div class="comment-item">
          <div class="comment-header">
            <p class="comment-meta">
              <?= htmlspecialchars($c['name_sei'], ENT_QUOTES) ?><?= htmlspecialchars($c['name_mei'], ENT_QUOTES) ?>
              <?= date('Y/n/j H:i', strtotime($c['created_at'])) ?>
            </p>
            <form action="" method="post" class="like-form">
              <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
              <input type="hidden" name="thread_id" value="<?= $id ?>">
              <input type="hidden" name="page" value="<?= $page ?>">
              <button type="submit" class="like-button <?= $userLiked ? 'liked' : '' ?>">
                <?= $userLiked ? '❤️' : '🤍' ?> <?= $likeCount ?>
              </button>
            </form>
          </div>
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
      <input type="hidden" name="page" value="<?= htmlspecialchars($page, ENT_QUOTES) ?>">
      <?php if (!empty($errorMessages['comment'])): ?>
        <p class="error"><?= htmlspecialchars($errorMessages['comment']) ?></p>
      <?php endif; ?>
      <div class="buttons">
        <button type="submit" class="btn">コメントする</button>
      </div>
    </form>
  <?php endif; ?>
</div>