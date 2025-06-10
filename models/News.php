<?php

namespace models;

use core\Model;
use core\Core;

 /**
    * @property int $id ID новини
    * @property string $title назва новини
    * @property string $short_text короткий текст новини
    * @property text $content контент новини
    * @property text $image фото новини
    */
class News extends Model
{
    public static $tableName = 'news';


    public static function Add($title, $short_text, $content, $image, $tagIds = []): void
    {
        $news = Core::get()->db->insert(self::$tableName, [
            'title' => $title,
            'short_text' => $short_text,
            'content' => $content,
            'image' => $image
        ]);

        if(!empty($tagIds))
        {
            foreach ($tagIds as $tagId) 
            {
            if (is_numeric($tagId))
                self::addTag($news, intval($tagId));
            }
        }
    }

    public static function getNewsPaginated($limit, $offset)
    {
        return Core::get()->db->select(
            self::$tableName,
            '*',
            [
                'id >' => '0',
                'ORDER' => ['created_at' => 'DESC'],
                'LIMIT' => "{$offset}, {$limit}"
            ]
        );
    }

    public static function getTotalCount()
    {
        $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count']);
        return $result[0]['count'] ?? 0;
    }

    public static function searchNews($query, $tagId, $limit, $offset)
    {
        $db = Core::get()->db;
        $pdo = $db->pdo;

        $querySql = "SELECT * FROM news";
        $params = [];
        $whereClauses = [];

        if (!empty($query)) 
        {
            $whereClauses[] = "title LIKE :title";
            $params[':title'] = '%' . self::escapeLike($query) . '%';
        }

        if ($tagId !== 'all') 
        {
            $newsIdsRaw = $db->select('news_tags', 'news_id', ['tag_id' => $tagId]);
            $newsIds = array_column($newsIdsRaw, 'news_id');

            if (empty($newsIds))
                return [];

            // Додаю IN (...)
            $inPlaceholders = [];
            foreach ($newsIds as $index => $newsId) 
            {
                $placeholder = ":news_id_{$index}";
                $inPlaceholders[] = $placeholder;
                $params[$placeholder] = $newsId;
            }

            $whereClauses[] = "id IN (" . implode(", ", $inPlaceholders) . ")";
        }

        if (!empty($whereClauses)) {
            $querySql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $querySql .= " ORDER BY created_at DESC LIMIT {$offset}, {$limit}";

        $stmt = $pdo->prepare($querySql);
        foreach ($params as $key => $value)
            $stmt->bindValue($key, $value);
        
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public static function getSearchCount($query, $tagId)
    {
        $db = Core::get()->db;
        $pdo = $db->pdo;

        $sql = "SELECT COUNT(*) as count FROM news";
        $params = [];
        $whereClauses = [];

        if (!empty($query)) 
        {
            $whereClauses[] = "title LIKE :title";
            $params[':title'] = '%' . self::escapeLike($query) . '%';
        }

        if ($tagId !== 'all') 
        {
            $newsIdsRaw = $db->select('news_tags', 'news_id', ['tag_id' => $tagId]);
            $newsIds = array_column($newsIdsRaw, 'news_id');

            if (empty($newsIds))
                return 0;

            $inPlaceholders = [];
            foreach ($newsIds as $index => $newsId) 
            {
                $placeholder = ":news_id_{$index}";
                $inPlaceholders[] = $placeholder;
                $params[$placeholder] = $newsId;
            }

            $whereClauses[] = "id IN (" . implode(", ", $inPlaceholders) . ")";
        }

        if (!empty($whereClauses)) 
            $sql .= " WHERE " . implode(" AND ", $whereClauses);

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value)
            $stmt->bindValue($key, $value);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['count'] ?? 0;
    }


    // Екранує %, _ та \ в LIKE-запиті
    protected static function escapeLike($str)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
    }


    public static function addTag($newsId, $tagId)
    {
        Core::get()->db->insert('news_tags', [
            'news_id' => $newsId,
            'tag_id' => $tagId
        ]);
    }

    public static function removeTag($newsId, $tagId)
    {
        Core::get()->db->delete('news_tags', [
            'news_id' => $newsId,
            'tag_id' => $tagId
        ]);
    }

    public static function getTags($newsId)
    {
        $rows = Core::get()->db->select('news_tags', '*', ['news_id' => $newsId]);
        $tags = [];

        foreach ($rows as $row)
            $tags[] = Core::get()->db->select('tags', '*', ['id' => $row['tag_id']])[0];

        return $tags;
    }


}