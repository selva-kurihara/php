<?php
session_start();

if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  header('Location: member.php');
  exit;
}

$id = (int)$_GET['id'];

try {
  $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
  $user = 'kurihara';
  $password = 'uCmCLu2e8H';
  $pdo = new PDO($dsn, $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
      header('Location: member.php');
      exit;
    }

    unset($_SESSION['token']);

    $stmt = $pdo->prepare('UPDATE members SET deleted_at = NOW() WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: member.php');
    exit;
  }

  $stmt = $pdo->prepare('SELECT * FROM members WHERE id = :id AND deleted_at IS NULL');
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$member) {
    exit('会員が見つかりません。');
  }
} catch (PDOException $e) {
  error_log('DBエラー: ' . $e->getMessage());
  exit('システムエラーが発生しました。');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>会員詳細</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>
  <h2>会員詳細</h2>
  <table>
    <tr>
      <th>ID</th>
      <td><?= htmlspecialchars($member['id']) ?></td>
    </tr>
    <tr>
      <th>姓</th>
      <td><?= htmlspecialchars($member['name_sei']) ?></td>
    </tr>
    <tr>
      <th>名</th>
      <td><?= htmlspecialchars($member['name_mei']) ?></td>
    </tr>
    <tr>
      <th>性別</th>
      <td><?= $member['gender'] === '1' ? '男性' : '女性' ?></td>
    </tr>
    <tr>
      <th>都道府県</th>
      <td><?= htmlspecialchars($member['pref_name']) ?></td>
    </tr>
    <tr>
      <th>住所</th>
      <td><?= htmlspecialchars($member['address']) ?></td>
    </tr>
    <tr>
      <th>メールアドレス</th>
      <td><?= htmlspecialchars($member['email']) ?></td>
    </tr>
  </table>

  <div class="buttons">
    <a href="member_edit.php?id=<?= $member['id'] ?>" class="btn btn-edit">編集</a>

    <form method="post">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="hidden" name="delete" value="1">
      <button type="submit" class="btn btn-danger">削除</button>
    </form>
  </div>

  <p><a href="member.php">← 会員一覧に戻る</a></p>
</body>

</html>