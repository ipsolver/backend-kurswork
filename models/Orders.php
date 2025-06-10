<?php
namespace models;

use core\Model;
use core\Core;
use core\OrdersImageUploader;

    /**
    * @property int $id ID замовлення
    * @property int $user_id ID замовника
    * @property string $image Фото від замовника
    * @property string $description Опис замовлення
    * @property date $deadline Дедлайн замовлення
    * @property int $category_id ID категорії (може бути null)
    * @property int $genre_id ID жанру
    * @property timestamp $created_at Дата створення замовлення
    * @property enum $status Статус замовлення (Обробка, Прийнято, Відхилено, Готово)
    * @property timestamp $accepted_at Час взяття замовлення
    * @property timestamp $completed_at Час виконання замовлення
    */

class Orders extends Model
{
    public static $tableName = 'orders';

     public static function findAllByUserId($userId)
     {
       $orders = Orders::findByCondition(["user_id" => $userId]);
       return $orders;
     }

     public static function findPaginated($userId, $offset, $limit)
  {
      return Core::get()->db->select(
          self::$tableName,
          '*',
          ['user_id' => $userId],
          ['created_at' => 'DESC'],
          $limit,
          $offset
      );
  }

  public static function countByUserId($userId)
  {
      $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count'], ['user_id' => $userId]);
      return $result[0]['count'] ?? 0;
  }

  public static function getFilteredOrders($userId, $status, $limit, $offset)
  {
      $where = ['user_id' => $userId];

      if (!empty($status))
          $where['status'] = $status;

      $where['ORDER'] = ['created_at' => 'DESC'];
      $where['LIMIT'] = "{$offset}, {$limit}";

      return Core::get()->db->select(self::$tableName, '*', $where);
  }

  public static function getFilteredCount($userId, $status)
  {
      $where = ['user_id' => $userId];

      if (!empty($status))
          $where['status'] = $status;

      $result = Core::get()->db->select(self::$tableName, ['COUNT(*) AS count'], $where);
      return $result[0]['count'] ?? 0;
  }

  public static function deleteWithImage($order)
    {
        $username = Users::getUsernameById($order['user_id']);
        if ($username)
            OrdersImageUploader::deleteOrderImage($username, $order['id']);
        self::deleteById($order['id']);
    }

    public static function countCompletedOrders()
    {
        $result = Core::get()->db->select(
            self::$tableName,
            ['COUNT(*) AS count'],
            ['status' => 'Готово']
        );
        return $result[0]['count'] ?? 0;
    }




}