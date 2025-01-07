<?php
$dbh=new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');
session_start();
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15; 
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0; 
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename
        FROM bbs_entries
        INNER JOIN users ON bbs_entries.user_id = users.id
        ORDER BY bbs_entries.created_at DESC
        LIMIT :limit OFFSET :offset';
$select_sth = $dbh->prepare($sql);
$select_sth->bindValue(':limit', $limit, PDO::PARAM_INT);
$select_sth->bindValue(':offset', $offset, PDO::PARAM_INT);
$select_sth->execute();


$result_entries = [];
foreach ($select_sth as $entry) {
    $image_file_urls = [];
    if (!empty($entry['image_filename'])) {
        $image_filenames = json_decode($entry['image_filename'], true); 
        if (is_array($image_filenames)) {
            foreach ($image_filenames as $filename) {
                $image_file_urls[] = '/image/' . $filename; 
            }
        }
    }
    $result_entry = [
        'id' => $entry['id'],
        'user_name' => $entry['user_name'],
        'user_icon_file_url' => empty($entry['user_icon_filename']) ? '' : ('/image/' . $entry['user_icon_filename']),
        'body' => nl2br(htmlspecialchars($entry['body'])),
        'image_file_urls' => $image_file_urls, 
        'created_at' => $entry['created_at'],
    ];
    $result_entries[] = $result_entry;
}
header("HTTP/1.1 200 OK");
header("Content-Type: application/json");
print(json_encode(['entries' => $result_entries]));
