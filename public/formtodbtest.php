<?php
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');
if (isset($_POST['body'])) {
  $insert_sth = $dbh->prepare("INSERT INTO posts(text) VALUES (:body)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
  ]);
  header("HTTP/1.1 302 Found");
  header("Location: ./formtodbtest.php");
  return;
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$count_per_page = 5;
$skip_page = $count_per_page * ($page - 1);

$count_sth = $dbh->prepare("SELECT COUNT(*) FROM posts;");
$count_sth->execute();
$count_all = $count_sth->fetchColumn();

?>

<div>
<div>
<h1>投稿画面</h1>
</div>
<form method="POST" action="./formtodbtest.php">
<textarea name="body"></textarea>
<button type="submit">送信</button>
</form>
<div>
  <?= $page ?>ページ目
(全<?= floor($count_all / $count_per_page) + 1 ?>ページ中)
  </div>
  <div>
  <?php if ($page > 1): ?>
  <a href="?page=<?= $page - 1 ?>">前へ</a>
  <?php endif; ?>
  </div>
  <div>
  <?php if ($count_all > $page * $count_per_page): ?>
  <a href="?page=<?= $page + 1 ?>">次へ</a>
  <?php endif; ?>
  </div>
  <?php
  if ($count_all == 0) {
    echo "まだ投稿はありません。";
  } else {
    if ($skip_page >= $count_all) {
      echo "このページは存在しません";
    } else {
      $select_sth = $dbh->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_page");
      $select_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
      $select_sth->bindParam(':skip_page', $skip_page, PDO::PARAM_INT);
      $select_sth->execute();

      foreach ($select_sth as $data): ?>
        <p><?= nl2br(htmlspecialchars($data['text'])) ?></p>
        <p><?= $data['created_at'] ?></p>
        <?php endforeach;
    }
  }
?>
</div>

