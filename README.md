# Web掲示板サービス
## 起動の仕方

### コンテナの起動
```
docker compose up
```
### 以下のSQL文でテーブルを作成する
```
docker compose exec mysql mysql kyototech
```

```
CREATE TABLE users(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY  
  name TEXT NOT NULL  
  email TEXT NOT NULL
  password TEXT NOT NULL
  icon_filename TEXT DEFAULT NULL
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP  
)
```

```
CREATE TABLE bbs_entries(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
  user_id INT UNSIGNED NOT NULL
  body TEXT NOT NULL
  image_filename TEXT DEFAULT NULL
  created_at DATATIME DEFAULT CURRENT_TIMESTAMP
)
```

以上を行なった上で以下のURLをたたくと表示されます：
```
ipアドレス/
```
