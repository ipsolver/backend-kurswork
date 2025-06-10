<?php
namespace models;

use core\Model;
use core\Core;

    /**
    * @property int $id ID логу
    * @property string $method Назва методу запиту
    * @property int $status_code Статус-код
    * @property string $ip IP адреса користувача
    * @property string $user_agent Браузер користувача
    * @property string $controller Контролер
    * @property string $action Метод контролера
    * @property string $url Посилання на сторінку
    * @property timestamp $created_at Час коли зроблено лог
    */
class Logs extends Model
{
    public static $tableName = 'logs';

    public static function Add(array $data)
    {
        Core::get()->db->insert(self::$tableName, $data);
    }

    public static function Stats($days)
    {
        $dateLimit = date('Y-m-d H:i:s', strtotime("-$days days"));

        $db = Core::get()->db;
        $sql = "SELECT status_code, COUNT(*) as count 
                FROM logs 
                WHERE created_at >= :date 
                GROUP BY status_code";

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute(['date' => $dateLimit]);

        return $stmt->fetchAll();
    }

    public static function StatsByMethod($days = 7)
    {
        $dateLimit = date('Y-m-d H:i:s', strtotime("-$days days"));

        $db = Core::get()->db;
        $sql = "SELECT method, COUNT(*) as count 
                FROM logs 
                WHERE created_at >= :date 
                GROUP BY method";

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute(['date' => $dateLimit]);

        return $stmt->fetchAll();
    }



}