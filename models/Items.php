<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $id ID товару
    * @property string $code Код товару
    * @property int $manager_id ID менеджера
    * @property int $category_id ID категорії
    * @property string $title Назва товару
    * @property string $description Опис товару
    * @property int $genre_id ID жанру картини
    * @property string $image Фото товару
    * @property float $price Остаточна ціна після врахування знижки
    * @property float $tarif Стандартна ціна товару
    * @property int $discount Знижка на товар
    * @property int $glass_id ID скла
    * @property timestamp $published_at час публікації
    * @property timestamp $created_at час коли створено товар
    */
class Items extends Model
{
    public static $tableName = 'items';

    public static function getTotalCount()
    {
        $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count']);
        return $result[0]['count'] ?? 0;
    }

    public static function getItemsPaginated($limit, $offset)
    {
        return Core::get()->db->select(
            self::$tableName,
            '*',
            [
                'published_at <=' => date('Y-m-d H:i:s'),
                'ORDER' => ['published_at' => 'DESC'],
                'LIMIT' => "{$offset}, {$limit}"
            ]
        );
    }

    public static function getUnpublishedItems($limit, $offset)
    {
        return Core::get()->db->select(
            self::$tableName,
            '*', 
            [
                'published_at >' => date('Y-m-d H:i:s'),
                'ORDER' => ['published_at' => 'ASC'],
                'LIMIT' => "{$offset}, {$limit}"
            ]
        );
    }

    public static function getUnpublishedCount()
    {
        $result = Core::get()->db->select(
            self::$tableName,
            ['COUNT(*) AS count'],
            ['published_at >' => date('Y-m-d H:i:s')]
        );
        return $result[0]['count'] ?? 0;
    }

    public static function publishNow($itemId)
    {
        return Core::get()->db->update(self::$tableName, [
            'published_at' => date('Y-m-d H:i:s')
        ], [
            'id' => $itemId
        ]);
    }


   public static function getFilteredItems($title, $code, $categoryId, $genreId, $sort, $limit, $offset)
    {
        $where = [];

        if (!empty($title))
            $where['title LIKE'] = '%' . $title . '%';

        if (!empty($code))
            $where['code LIKE'] = '%' . $code . '%';

        if (!empty($categoryId) && $categoryId !== 'all')
            $where['category_id'] = $categoryId;

        if (!empty($genreId) && $genreId !== 'all')
            $where['genre_id'] = $genreId;

        $where['published_at <='] = date('Y-m-d H:i:s');

        $order = ['published_at' => 'DESC'];
        if ($sort === 'price_asc') 
            $order = ['price' => 'ASC'];
        
        elseif ($sort === 'price_desc') 
            $order = ['price' => 'DESC'];

        $where['ORDER'] = $order;
        $where['LIMIT'] = "{$offset}, {$limit}";

        return Core::get()->db->select(self::$tableName, '*', $where);
    }

    public static function getFilteredCount($title, $code, $categoryId, $genreId)
    {
        $where = [];

        if (!empty($title))
            $where['title LIKE'] = '%' . $title . '%';

        if (!empty($code))
            $where['code LIKE'] = '%' . $code . '%';

        if (!empty($categoryId) && $categoryId !== 'all')
            $where['category_id'] = $categoryId;

        if (!empty($genreId) && $genreId !== 'all')
            $where['genre_id'] = $genreId;

        $where['published_at <='] = date('Y-m-d H:i:s');

        $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count'], $where);
        return $result[0]['count'] ?? 0;
    }




    public static function getFilteredUnpublishedItems($title, $code, $categoryId, $genreId, $sort, $limit, $offset)
    {
        $where = [];

        if (!empty($title))
            $where['title LIKE'] = '%' . $title . '%';

        if (!empty($code))
            $where['code LIKE'] = '%' . $code . '%';

        if (!empty($categoryId) && $categoryId !== 'all')
            $where['category_id'] = $categoryId;

        if (!empty($genreId) && $genreId !== 'all')
            $where['genre_id'] = $genreId;

        $where['published_at >'] = date('Y-m-d H:i:s');

        $order = ['published_at' => 'ASC'];
        if ($sort === 'price_asc') 
            $order = ['price' => 'ASC'];
        elseif ($sort === 'price_desc') 
            $order = ['price' => 'DESC'];

        $where['ORDER'] = $order;
        $where['LIMIT'] = "{$offset}, {$limit}";

        return Core::get()->db->select(self::$tableName, '*', $where);
    }

    public static function getFilteredUnpublishedCount($title, $code, $categoryId, $genreId)
    {
        $where = [];

        if (!empty($title))
            $where['title LIKE'] = '%' . $title . '%';

        if (!empty($code))
            $where['code LIKE'] = '%' . $code . '%';

        if (!empty($categoryId) && $categoryId !== 'all')
            $where['category_id'] = $categoryId;

        if (!empty($genreId) && $genreId !== 'all')
            $where['genre_id'] = $genreId;

        $where['published_at >'] = date('Y-m-d H:i:s');

        $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count'], $where);
        return $result[0]['count'] ?? 0;
    }



    public static function getMostLikedItems($limit = 4)
    {
        $sql = "
            SELECT 
                items.id,
                items.title,
                items.code,
                items.published_at,
                items.created_at,
                items.discount,
                items.tarif,
                items.manager_id,
                items.genre_id,
                items.category_id,
                items.description,
                COUNT(item_likes.item_id) as like_count,
                (
                SELECT ii.path 
                FROM item_images ii 
                WHERE ii.item_id = items.id AND ii.is_main = 1 
                LIMIT 1
                ) as main_image
            FROM items
            LEFT JOIN item_likes ON items.id = item_likes.item_id
            WHERE items.published_at <= :now
            GROUP BY items.id
            ORDER BY like_count DESC, items.published_at DESC
            LIMIT :limit
        ";

        $stmt = Core::get()->db->pdo->prepare($sql);
        $stmt->bindValue(':now', date('Y-m-d H:i:s'));
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }








}