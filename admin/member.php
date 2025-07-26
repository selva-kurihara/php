<?php

session_start();

$isLoggedIn = isset($_SESSION['user']);

try {
  // DB接続
  $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
  $user = 'kurihara';
  $password = 'uCmCLu2e8H';
  $pdo = new PDO($dsn, $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // 検索条件の初期化
  $where = [];
  $params = [];

  if (!empty($_GET['id'])) {
    $where[] = 'id = :id';
    $params[':id'] = $_GET['id'];
  }

  if (!empty($_GET['gender']) && is_array($_GET['gender'])) {
    $genders = [];
    foreach ($_GET['gender'] as $i => $gender) {
      $key = ":gender$i";
      $genders[] = $key;
      $params[$key] = ($gender === 'male') ? 1 : 2; // DBでは 1:男性, 2:女性 だと仮定
    }
    $where[] = 'gender IN (' . implode(', ', $genders) . ')';
  }

  if (!empty($_GET['pref'])) {
    $where[] = 'pref_name = :pref';
    $params[':pref'] = $_GET['pref'];
  }

  if (!empty($_GET['keyword'])) {
    $where[] = '(name_sei LIKE :kw OR name_mei LIKE :kw OR email LIKE :kw LIKE :kw)';
    $params[':kw'] = '%' . $_GET['keyword'] . '%';
  }

  // 並び替え対象と順序を取得
  $sortableColumns = ['id', 'created_at'];
  $sort = in_array($_GET['sort'] ?? '', $sortableColumns) ? $_GET['sort'] : 'id';
  $order = strtolower($_GET['order'] ?? 'desc') === 'desc' ? 'desc' : 'asc';

  // クエリ組み立て
  $sql = 'SELECT * FROM members';
  if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  $sql .= " ORDER BY $sort " . strtoupper($order);

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log('DBエラー: ' . $e->getMessage());
  echo 'データベース接続エラーが発生しました。';
  exit;
}

// 総件数の取得（membersの検索条件付き）
$sqlCount = 'SELECT COUNT(*) FROM members';
if (!empty($where)) {
  $sqlCount .= ' WHERE ' . implode(' AND ', $where);
}
$countStmt = $pdo->prepare($sqlCount);
$countStmt->execute($params);
$totalMembers = (int)$countStmt->fetchColumn();

$limit = 10;
$totalPages = ceil($totalMembers / $limit);
$page = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$offset = ($page - 1) * $limit;

// 会員情報をページごとに取得
$sql .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
  $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>掲示板管理画面メインメニュー</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>
  <div class="container">
    <h2>会員一覧</h2>

    <div class="buttons-left">
      <a href="member_regist.php" class="btn-register">会員登録</a>
    </div>

    <form class="search-form" method="GET" action="">
      <div class="form-group">
        <label for="id">ID</label>
        <input type="text" id="id" name="id">
      </div>

      <div class="form-group">
        <label>性別</label>
        <label class="gender"><input type="checkbox" name="gender[]" value="male"> 男性</label>
        <label class="gender"><input type="checkbox" name="gender[]" value="female"> 女性</label>
      </div>

      <div class="form-group">
        <label for="pref">都道府県</label>
        <select name="pref">
          <option value="">選択してください</option>
          <?php
          $prefs = [
            '北海道',
            '青森県',
            '岩手県',
            '宮城県',
            '秋田県',
            '山形県',
            '福島県',
            '茨城県',
            '栃木県',
            '群馬県',
            '埼玉県',
            '千葉県',
            '東京都',
            '神奈川県',
            '新潟県',
            '富山県',
            '石川県',
            '福井県',
            '山梨県',
            '長野県',
            '岐阜県',
            '静岡県',
            '愛知県',
            '三重県',
            '滋賀県',
            '京都府',
            '大阪府',
            '兵庫県',
            '奈良県',
            '和歌山県',
            '鳥取県',
            '島根県',
            '岡山県',
            '広島県',
            '山口県',
            '徳島県',
            '香川県',
            '愛媛県',
            '高知県',
            '福岡県',
            '佐賀県',
            '長崎県',
            '熊本県',
            '大分県',
            '宮崎県',
            '鹿児島県',
            '沖縄県'
          ];

          foreach ($prefs as $pref) {
            $selected = ($_GET['pref'] ?? '') === $pref ? 'selected' : '';
            echo "<option value=\"{$pref}\" {$selected}>{$pref}</option>";
          }
          ?>
        </select>

      </div>

      <div class="form-group">
        <label for="keyword">フリーワード</label>
        <input type="text" id="keyword" name="keyword">
      </div>

      <div class="buttons">
        <button type="submit" class="btn">検索する</button>
      </div>
    </form>

    <table class="member-table">
      <?php if (!empty($members)): ?>
        <table border="1" cellpadding="5">
          <thead>
            <tr>
              <th>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => ($sort === 'id' && $order === 'asc') ? 'desc' : 'asc'])) ?>">
                  ID <?= $sort === 'id' ? ($order === 'asc' ? '▲' : '▼') : '▼' ?>
                </a>
              </th>
              <th>氏名</th>
              <th>性別</th>
              <th>住所</th>
              <th>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => ($sort === 'created_at' && $order === 'asc') ? 'desc' : 'asc'])) ?>">
                  登録日時 <?= $sort === 'created_at' ? ($order === 'asc' ? '▲' : '▼') : '▼' ?>
                </a>
              </th>
              <th>編集</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $member): ?>
              <tr>
                <td><?= htmlspecialchars($member['id']) ?></td>
                <td><?= htmlspecialchars($member['name_sei'] . ($member['name_mei'])) ?></td>
                <td>
                  <?php
                  if ($member['gender'] == 1) echo '男性';
                  else if ($member['gender'] == 2) echo '女性';
                  else echo 'その他';
                  ?>
                </td>
                <td><?= htmlspecialchars($member['pref_name'] . ($member['address'])) ?></td>
                <td><?= htmlspecialchars($member['created_at']) ?></td>
                <td><a href="member_edit.php?id=<?= htmlspecialchars($member['id']) ?>">編集</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>該当する会員は見つかりませんでした。</p>
      <?php endif; ?>

      <!-- ページャー -->
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&lt; 前へ</a>
        <?php else: ?>
          <span class="disabled">&lt; 前へ</span>
        <?php endif; ?>

        <?php
        $range = 1; // 表示する前後のページ数
        $startPage = max(1, $page - $range);
        $endPage = min($totalPages, $page + $range);
        // 先頭3ページ分なら1〜3固定表示
        if ($page <= 2) {
          $startPage = 1;
          $endPage = min(3, $totalPages);
        }

        // 最後の3ページ分なら totalPages - 2 〜 totalPages 表示
        if ($page >= $totalPages - 1) {
          $endPage = $totalPages;
          $startPage = max(1, $totalPages - 2);
        }
        ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">次へ &gt;</a>
        <?php else: ?>
          <span class="disabled">次へ &gt;</span>
        <?php endif; ?>
      </div>

      <div class="buttons">
        <a href="top.php" class="btn btn-top">トップへ戻る</a>
      </div>
  </div>

</body>

</html>