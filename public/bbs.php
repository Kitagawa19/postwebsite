<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {
    // 投稿内容があり、ログイン状態の場合

    $image_filenames = []; // 保存する画像ファイル名を格納する配列

    if (isset($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            if ($index >= 4) {
                break; // 最大4枚まで
            }
            if (preg_match('/^image\//', mime_content_type($tmp_name)) !== 1) {
                // アップロードされたものが画像ではなかった場合
                header("HTTP/1.1 302 Found");
                header("Location: ./bbs.php");
                exit;
            }
            // 元のファイル名から拡張子を取得
            $pathinfo = pathinfo($_FILES['images']['name'][$index]);
            $extension = $pathinfo['extension'];
            // 新しいファイル名を決める
            $image_filename = strval(time()) . bin2hex(random_bytes(5)) . '.' . $extension;
            $filepath = '/var/www/upload/image/' . $image_filename;

            // ファイルを保存
            if (move_uploaded_file($tmp_name, $filepath)) {
                $image_filenames[] = $image_filename; // ファイル名を配列に追加
            }
        }
    }

    // データベースに挿入
    $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename);");
    $insert_sth->execute([
        ':user_id' => $_SESSION['login_user_id'], // ログインしている会員情報の主キー
        ':body' => $_POST['body'],
        ':image_filename' => json_encode($image_filenames), // JSON形式で保存
    ]);

    // 処理が終わったらリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./bbs.php");
    exit;
}
?>

<?php if (empty($_SESSION['login_user_id'])): ?>
  投稿するには<a href="/login.php">ログイン</a>が必要です。
<?php else: ?>
</div><a href="/icon.php">アイコン画像の設定はこちら</a>。</div>
<form method="POST" action="./bbs.php" enctype="multipart/form-data">
  <textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="images[]" id="imageInput" multiple>
    <p>※画像は最大4枚までアップロード可能です。</p>
  </div>
  <button type="submit">送信</button>
</form>
<?php endif; ?>

<hr>

<dl id="entryTemplate" style="display: none; margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
  <dt>番号</dt>
  <dd data-role="entryIdArea"></dd>
  <dt>投稿者</dt>
  <dd>
    <a href="" data-role="entryUserAnchor">
      <img data-role="entryUserIconImage"
        style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <span data-role="entryUserNameArea"></span>
    </a>
  </dd>
  <dt>日時</dt>
  <dd data-role="entryCreatedAtArea"></dd>
  <dt>内容</dt>
  <dd data-role="entryBodyArea"></dd>
</dl>
<div id="entriesRenderArea"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const entryTemplate = document.getElementById('entryTemplate');
    const entriesRenderArea = document.getElementById('entriesRenderArea');
    const request = new XMLHttpRequest();
    request.onload = (event) => {
        const response = event.target.response;
        response.entries.forEach((entry) => {
            const entryCopied = entryTemplate.cloneNode(true);
            entryCopied.style.display = 'block';

            entryCopied.querySelector('[data-role="entryIdArea"]').innerText = entry.id.toString();

            if (entry.user_icon_file_url) {
                entryCopied.querySelector('[data-role="entryUserIconImage"]').src = entry.user_icon_file_url;
            } else {
                entryCopied.querySelector('[data-role="entryUserIconImage"]').style.display = 'none';
            }
            entryCopied.querySelector('[data-role="entryUserNameArea"]').innerText = entry.user_name;

            entryCopied.querySelector('[data-role="entryCreatedAtArea"]').innerText = entry.created_at;

            entryCopied.querySelector('[data-role="entryBodyArea"]').innerHTML = entry.body;

            // 複数画像を表示
            if (entry.image_file_urls && Array.isArray(entry.image_file_urls)) {
                entry.image_file_urls.forEach((url) => {
                    const imageElement = new Image();
                    imageElement.src = url;
                    imageElement.style.display = 'block';
                    imageElement.style.marginTop = '1em';
                    imageElement.style.maxHeight = '300px';
                    imageElement.style.maxWidth = '300px';
                    entryCopied.querySelector('[data-role="entryBodyArea"]').appendChild(imageElement);
                });
            }

            entriesRenderArea.appendChild(entryCopied);
        });
    };
    request.open('GET', '/bbs_json.php', true); // bbs_json.php を叩く
    request.responseType = 'json';
    request.send();

    const imageInput = document.getElementById("imageInput");
    imageInput.addEventListener("change", () => {
        if (imageInput.files.length > 4) {
            alert("画像は最大4枚まで選択できます。");
            imageInput.value = ""; // 入力をクリア
        }
        Array.from(imageInput.files).forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                alert("5MB以下のファイルを選択してください。");
                imageInput.value = ""; // 入力をクリア
            }
        });
    });
});
</script>

