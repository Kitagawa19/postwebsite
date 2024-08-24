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
CREATE TABLE POSTS(
  id INT AUTO_INCREMENT PRIMARY KEY  
  content TEXT NOT NULL  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP  
)
```
