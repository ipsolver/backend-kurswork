<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $user_id ID користувача
    * @property int $item_id ID товару
    * @property timestamp $created_at Час коли поставлено лайк
    */
class ItemLikes extends Model
{
    public static $tableName = 'item_likes';

    public static function addLike($userId, $itemId)
    {
        return Core::get()->db->insert(self::$tableName, [
            'user_id' => $userId,
            'item_id' => $itemId
        ]);
    }

    public static function removeLike($userId, $itemId)
    {
        return Core::get()->db->delete(self::$tableName, [
            'user_id' => $userId,
            'item_id' => $itemId
        ]);
    }

    public static function isLikedByUser($userId, $itemId)
    {
        $res = Core::get()->db->select(
            self::$tableName,
            '*',
            ['user_id' => $userId, 'item_id' => $itemId]
        );

        return !empty($res);
    }

    public static function getLikesCount($itemId)
    {
        $res = Core::get()->db->select(
            self::$tableName,
            ['COUNT(*) as count'],
            ['item_id' => $itemId]
        );

        return $res[0]['count'] ?? 0;
    }


}