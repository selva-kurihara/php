<?php
// セッション開始
session_start();

// エラーメッセージの初期化
$errorMessages = [];

// POSTを受け取った場合
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // 都道府県
	$validPrefectures = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
    '岐阜県', '静岡県', '愛知県', '三重県',
    '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
    '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県',
    '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
  ];

	// エラーチェック
	// if (empty($_POST["last_name"])) {
  //   $errorMessages["last_name"] = '姓が未入力です。';
  // } else if (mb_strlen($_POST["last_name"]) > 20) {
  //   $errorMessages["last_name"] = '姓は20文字以内で入力してください。';
  // } else if (!preg_match('/^[ぁ-んァ-ン一-龥々ー]+$/u', $_POST["last_name"])) {
  //   $errorMessages["last_name"] = '姓にはひらがな・カタカナ・漢字以外の文字は使用できません。';

  // } else if (empty($_POST["first_name"])) {
  //   $errorMessages["first_name"] = '名が未入力です。';
  // } else if (mb_strlen($_POST["first_name"]) > 20) {
  //   $errorMessages["first_name"] = '名は20文字以内で入力してください。';
  // }  else if (!preg_match('/^[ぁ-んァ-ン一-龥々ー]+$/u', $_POST["first_name"])) {
  //   $errorMessages["first_name"] = '名にはひらがな・カタカナ・漢字以外の文字は使用できません。';

	// } else if (empty($_POST["gender"])) {
	// 	$errorMessages["gender"] = '性別が未入力です。';
  // } else if ($_POST["gender"] !== '男性' && $_POST["gender"] !== '女性') {
  //   $errorMessages["gender"] = '性別の値が不正です。';
  
  // } else if (empty($_POST["prefecture"])) {
  //   $errorMessages["prefecture"] = '都道府県が未選択です。';
  // } else if (!in_array($_POST["prefecture"], $validPrefectures, true)) {
  //   $errorMessages["prefecture"] = '都道府県の値が不正です。';

  // } else if (mb_strlen($_POST["address"]) > 100) {
  //   $errorMessages["address"] = 'アドレスは100文字以内で入力してください。';

	// } else if (empty($_POST["password"])) {
  //     $errorMessages["password"] = 'パスワードが未入力です。';
  // } else if (!preg_match('/^[a-zA-Z0-9]{8,20}$/', $_POST["password"])) {
  //     $errorMessages["password"] = 'パスワードは半角英数字8〜20文字で入力してください。';

  // } else if (empty($_POST["password_confirm"])) {
  //     $errorMessages["password_confirm"] = '確認用パスワードが未入力です。';
  // } else if ($_POST["password"] !== $_POST["password_confirm"]) {
  //     $errorMessages["password_confirm"] = 'パスワードと確認用パスワードが一致しません。';

  // } else if (empty($_POST["email"])) {
  //     $errorMessages["email"] = 'メールアドレスが未入力です。';
  // } else if (mb_strlen($_POST["email"]) > 200) {
  //     $errorMessages["email"] = 'メールアドレスは200文字以内で入力してください。';
  // } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
  //     $errorMessages["email"] = 'メールアドレスの形式が正しくありません。';
  // }
		
  
  // 「確認へ進む」ボタンが押された場合のみ、セッション保存＋リダイレクト
  if (isset($_POST['action']) && $_POST['action'] === 'confirm' && empty($errorMessages)) {
    $_SESSION['form'] = $_POST;

    header("Location: member_confirm.php");
    exit;
  }
}

// セッションからフォーム値を取得
$last_name    = isset($_SESSION['form']['last_name']) ? $_SESSION['form']['last_name'] : '';
$first_name   = isset($_SESSION['form']['first_name']) ? $_SESSION['form']['first_name'] : '';
$gender       = isset($_SESSION['form']['gender']) ? $_SESSION['form']['gender'] : '';
$prefecture   = isset($_SESSION['form']['prefecture']) ? $_SESSION['form']['prefecture'] : '';
$address      = isset($_SESSION['form']['address']) ? $_SESSION['form']['address'] : '';
$password     = isset($_SESSION['form']['password']) ? $_SESSION['form']['password'] : '';
$confirm_password = isset($_SESSION['form']['confirm_password']) ? $_SESSION['form']['confirm_password'] : '';
$email        = isset($_SESSION['form']['email']) ? $_SESSION['form']['email'] : '';

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>会員登録</title>
    <link rel="stylesheet" href="stylesheet.css">
  </head>
  <body>
    <div class="container">
      <h2>会員情報登録フォーム</h2>
      <form action="member_regist.php" method="post">
        
        <label>氏名</label>
        姓 <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
        名 <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>">

        <?php
        
        if (!empty($errorMessages["last_name"])) {
          echo $errorMessages["last_name"];
        }
          
        if (!empty($errorMessages["first_name"])) {
          echo $errorMessages["first_name"];
        }

        if (!empty($errorMessages["first_name"])) {
          echo $errorMessages["first_name"];
        }

        ?>
      
        <label>性別</label>
        <label class="gender"><input type="radio" name="gender" value="男性" <?= $gender === '男性' ? 'checked' : '' ?>> 男性</label>
        <label class="gender"><input type="radio" name="gender" value="女性" <?= $gender === '女性' ? 'checked' : '' ?>> 女性</label>

        <label>住所</label>
        都道府県
        <select name="prefecture">
          <option value="" <?= $prefecture === '' ? 'selected' : '' ?>>選択してください▼</option>
          <option value="北海道" <?= $prefecture === '北海道' ? 'selected' : '' ?>>北海道</option>
          <option value="青森県" <?= $prefecture === '青森県' ? 'selected' : '' ?>>青森県</option>
          <option value="岩手県" <?= $prefecture === '岩手県' ? 'selected' : '' ?>>岩手県</option>
          <option value="宮城県" <?= $prefecture === '宮城県' ? 'selected' : '' ?>>宮城県</option>
          <option value="秋田県" <?= $prefecture === '秋田県' ? 'selected' : '' ?>>秋田県</option>
          <option value="山形県" <?= $prefecture === '山形県' ? 'selected' : '' ?>>山形県</option>
          <option value="福島県" <?= $prefecture === '福島県' ? 'selected' : '' ?>>福島県</option>
          <option value="茨城県" <?= $prefecture === '茨城県' ? 'selected' : '' ?>>茨城県</option>
          <option value="栃木県" <?= $prefecture === '栃木県' ? 'selected' : '' ?>>栃木県</option>
          <option value="群馬県" <?= $prefecture === '群馬県' ? 'selected' : '' ?>>群馬県</option>
          <option value="埼玉県" <?= $prefecture === '埼玉県' ? 'selected' : '' ?>>埼玉県</option>
          <option value="千葉県" <?= $prefecture === '千葉県' ? 'selected' : '' ?>>千葉県</option>
          <option value="東京都" <?= $prefecture === '東京都' ? 'selected' : '' ?>>東京都</option>
          <option value="神奈川県" <?= $prefecture === '神奈川県' ? 'selected' : '' ?>>神奈川県</option>
          <option value="新潟県" <?= $prefecture === '新潟県' ? 'selected' : '' ?>>新潟県</option>
          <option value="富山県" <?= $prefecture === '富山県' ? 'selected' : '' ?>>富山県</option>
          <option value="石川県" <?= $prefecture === '石川県' ? 'selected' : '' ?>>石川県</option>
          <option value="福井県" <?= $prefecture === '福井県' ? 'selected' : '' ?>>福井県</option>
          <option value="山梨県" <?= $prefecture === '山梨県' ? 'selected' : '' ?>>山梨県</option>
          <option value="長野県" <?= $prefecture === '長野県' ? 'selected' : '' ?>>長野県</option>
          <option value="岐阜県" <?= $prefecture === '岐阜県' ? 'selected' : '' ?>>岐阜県</option>
          <option value="静岡県" <?= $prefecture === '静岡県' ? 'selected' : '' ?>>静岡県</option>
          <option value="愛知県" <?= $prefecture === '愛知県' ? 'selected' : '' ?>>愛知県</option>
          <option value="三重県" <?= $prefecture === '三重県' ? 'selected' : '' ?>>三重県</option>
          <option value="滋賀県" <?= $prefecture === '滋賀県' ? 'selected' : '' ?>>滋賀県</option>
          <option value="京都府" <?= $prefecture === '京都府' ? 'selected' : '' ?>>京都府</option>
          <option value="大阪府" <?= $prefecture === '大阪府' ? 'selected' : '' ?>>大阪府</option>
          <option value="兵庫県" <?= $prefecture === '兵庫県' ? 'selected' : '' ?>>兵庫県</option>
          <option value="奈良県" <?= $prefecture === '奈良県' ? 'selected' : '' ?>>奈良県</option>
          <option value="和歌山県" <?= $prefecture === '和歌山県' ? 'selected' : '' ?>>和歌山県</option>
          <option value="鳥取県" <?= $prefecture === '鳥取県' ? 'selected' : '' ?>>鳥取県</option>
          <option value="島根県" <?= $prefecture === '島根県' ? 'selected' : '' ?>>島根県</option>
          <option value="岡山県" <?= $prefecture === '岡山県' ? 'selected' : '' ?>>岡山県</option>
          <option value="広島県" <?= $prefecture === '広島県' ? 'selected' : '' ?>>広島県</option>
          <option value="山口県" <?= $prefecture === '山口県' ? 'selected' : '' ?>>山口県</option>
          <option value="徳島県" <?= $prefecture === '徳島県' ? 'selected' : '' ?>>徳島県</option>
          <option value="香川県" <?= $prefecture === '香川県' ? 'selected' : '' ?>>香川県</option>
          <option value="愛媛県" <?= $prefecture === '愛媛県' ? 'selected' : '' ?>>愛媛県</option>
          <option value="高知県" <?= $prefecture === '高知県' ? 'selected' : '' ?>>高知県</option>
          <option value="福岡県" <?= $prefecture === '福岡県' ? 'selected' : '' ?>>福岡県</option>
          <option value="佐賀県" <?= $prefecture === '佐賀県' ? 'selected' : '' ?>>佐賀県</option>
          <option value="長崎県" <?= $prefecture === '長崎県' ? 'selected' : '' ?>>長崎県</option>
          <option value="熊本県" <?= $prefecture === '熊本県' ? 'selected' : '' ?>>熊本県</option>
          <option value="大分県" <?= $prefecture === '大分県' ? 'selected' : '' ?>>大分県</option>
          <option value="宮崎県" <?= $prefecture === '宮崎県' ? 'selected' : '' ?>>宮崎県</option>
          <option value="鹿児島県" <?= $prefecture === '鹿児島県' ? 'selected' : '' ?>>鹿児島県</option>
          <option value="沖縄県" <?= $prefecture === '沖縄県' ? 'selected' : '' ?>>沖縄県</option>
        </select>

        <label>それ以降の住所</label>
        <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">

        <label>パスワード</label>
        <input type="password" name="password" value="<?= htmlspecialchars($password) ?>">
        
        <label>パスワード確認</label>
        <input type="password" name="confirm_password" value="<?= htmlspecialchars($confirm_password) ?>">

        <label>メールアドレス</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

        <input type="hidden" name="action" value="confirm">
        <div class="buttons">
          <button type="submit" class="btn">確認画面へ</button>
        </div>
      </form>
    </div>
  </body>
</html>