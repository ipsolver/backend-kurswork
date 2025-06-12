<?php

namespace controllers;
use core\Template;
use core\Core;
use core\Controller;
use core\Config;
use models\Users;
use models\Items;
use models\ItemLikes;
use models\News;
use models\Orders;
use models\Categories;
use models\Genres;
use models\Logs;

class AdminController extends Controller
{

    public function beforeAction(string $action): bool
    {
        $public = [];

        if (!in_array(strtolower($action), $public)) 
        {
            if (!Users::hasRole('admin')) 
            {
                Core::get()->router->error(403);
                return false;
            }
        }

        return true;
    }

    private array $modelToTable = [
    'Categories' => 'categories',
    'Genres' => 'genres',
    'Glass' => 'glass',
    'GlassTypes' => 'glass_types',
    'Items' => 'items',
    'Logs' => 'logs',
    'News' => 'news',
    'Users' => 'users',
    'Tags' => 'tags',
];

private array $tableToModel = [
    'Tags' => \models\Tags::class,
    'Categories' => \models\Categories::class,
    'Genres' => \models\Genres::class,
    'GlassTypes' => \models\GlassTypes::class,
    'Glass' => \models\Glass::class,
    'Items' => \models\Items::class
];


    public function actionIndex()
    {
         $models = ["Categories", "Genres", "Glass", "GlassTypes", "Items",
          "Logs", "News", "Users", "Tags"];

        $this->template->setParams([
            'models' => $models
        ]);


        return $this->render();
    }

    public function actionGetTable()
    {
        $modelName = $this->get->tableName ?? '';

        if (!isset($this->modelToTable[$modelName])) 
        {
            http_response_code(400);
            echo json_encode(['error' => 'Невідома таблиця']);
            exit;
        }

        $tableName = $this->modelToTable[$modelName];
        $data = Core::get()->db->select($tableName, '*', '1 ORDER by id ASC');

        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    public function actionSearchOptions()
    {
        $table = $this->get->table;
        header("Content-Type: application/json");
        $data = Core::get()->db->select($table, "*");
        echo json_encode($data);
        exit;
    }

    public function actionDeleteRow()
    {
        $tableName = $this->post->table ?? null;
        $id = $this->post->id ?? null;

        if (!isset($this->modelToTable[$tableName]) || !is_numeric($id)) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            exit;
        }

        $realTable = $this->modelToTable[$tableName];

        if ($realTable === 'users') 
        {
            $user = Users::findById($id);
            if ($user && $user['username'] === 'vader') 
            {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Цього користувача не можна видалити']);
                exit;
            }
        }

        Core::get()->db->delete($realTable, ['id' => intval($id)]);

        header("Content-Type: application/json");
        echo json_encode(['success' => true]);
        exit;
    }

    public function actionUpdateRow()
    {
        $tableName = $this->post->table ?? null;
        $id = $this->post->id ?? null;
        $fields = $this->post->fields ?? null;

        if (!isset($this->modelToTable[$tableName]) || !is_numeric($id) || !is_array($fields)) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            exit;
        }

         if ($tableName === 'Logs') 
         {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Редагування логів заборонено']);
            exit;
        }


        $simpleTables = ['Tags', 'Categories', 'Genres', 'GlassTypes'];
        if (in_array($tableName, $simpleTables)) 
        {
            if (isset($fields['name'])) 
            {
                $name = trim($fields['name']);

                if ($name === '') 
                {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Назва не може бути порожньою']);
                    exit;
                }

                if (mb_strlen($name) > 50) 
                {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Назва не може бути довшою за 50 символів']);
                    exit;
                }

                $modelClass = $this->tableToModel[$tableName] ?? null;
                if ($modelClass)
                {
                    $existing = $modelClass::findByCondition(['name' => $name]);
                    if (!empty($existing)) 
                {
                    foreach ($existing as $record) 
                    {
                        if ($record['id'] != $id) 
                        {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Такий запис вже існує']);
                            exit;
                        }
                    }
                }
                }
            }
        }

        $realTable = $this->modelToTable[$tableName];

        if ($tableName === 'Users' && isset($fields['role'])) 
        {
            $user = Core::get()->db->selectOne($realTable, ['id' => intval($id)]);
            if ($user && $user['username'] === 'vader') 
            {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Неможливо змінити роль цього користувача']);
                exit;
            }
        }

        unset($fields['id']);

        Core::get()->db->update($realTable, $fields, ['id' => intval($id)]);

        header("Content-Type: application/json");
        echo json_encode(['success' => true]);
        exit;
    }

    public function actionAddUser()
    {
        if (!$this->isPost) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Некоректний запит']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['username'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Логін не вказано']);
            exit;
        }

        if (empty($data['password']) || strlen($data['password']) < 3) 
        {
            echo json_encode(['success' => false, 'message' => 'Пароль не вказано або занадто короткий']);
            exit;
        }

        if (empty($data['first_name']) || empty($data['last_name'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Імʼя або прізвище не вказані']);
            exit;
        }

        if (empty($data['phone'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Номер телефону не вказано']);
            exit;
        }

        if (!preg_match('/^0\d{9}$/', $data['phone'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Некоректний номер телефону']);
            exit;
        }


        if (Users::FindByLogin($data['username'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Користувач із таким логіном вже існує']);
            exit;
        }

        $role = $data['role'] ?? 'user';
        if (!in_array($role, ['user', 'admin']))
            $role = 'user';

        try {
            Users::RegisterUser(
                $data['username'],
                $data['password'],
                $data['last_name'],
                $data['first_name'],
                null,
                $data['phone'],
                $role
            );

            echo json_encode(['success' => true]);
            exit;
        } catch (\Exception $e) 
        {
            echo json_encode([
                'success' => false,
                'message' => 'Помилка при збереженні користувача: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function actionGetOrders()
    {
        $db = Core::get()->db;

        $now = new \DateTime();
        $orders = $db->select('orders', '*', '1 ORDER BY created_at DESC');
        $idsToDelete = [];

        foreach ($orders as $order) 
        {
            if ($order['status'] === 'Відхилено') 
            {
                $created = new \DateTime($order['created_at']);
                $interval = $now->diff($created);
                if ($interval->days > 5)
                    {
                        Orders::deleteWithImage($order);
                        continue;
                    }
            }
        }

        $orders = $db->select('orders', '*', '1 ORDER BY created_at DESC');

        foreach ($orders as &$order) 
        {
            $user = $db->select('users', '*', ['id' => $order['user_id']])[0] ?? null;
            $order['user_fullname'] = $user ? "{$user['first_name']} {$user['last_name']}" : 'Невідомо';
            $order['user_phone'] = $user['phone'] ?? 'Невідомо';
            if (!empty($order['category_id'])) 
            {
                $category = Categories::findById($order['category_id']);
                $order['category_name'] = $category ? $category['name'] : null;
            }

            if (!empty($order['genre_id'])) 
            {
                $genre = Genres::findById($order['genre_id']);
                $order['genre_name'] = $genre ? $genre['name'] : null;
            }
        }

        header("Content-Type: application/json");
        echo json_encode([
            'orders' => $orders]);
        exit;
    }

    public function actionAddSimple()
    {
        if ($this->isPost) 
        {
            header("Content-Type: application/json");
            $data = json_decode(file_get_contents('php://input'), true);
            $table = $data['table'] ?? null;
            $name = trim($data['name'] ?? '');

            if (!$table || !$name) {
                echo json_encode(['success' => false, 'message' => 'Невірні дані']);
                exit;
            }

            $allowed = [
                'Tags' => \models\Tags::class,
                'Categories' => \models\Categories::class,
                'Genres' => \models\Genres::class,
                'GlassTypes' => \models\GlassTypes::class
            ];

            if (!array_key_exists($table, $allowed)) 
            {
                echo json_encode(['success' => false, 'message' => 'Недозволена таблиця']);
                exit;
            }

             if (mb_strlen($name) > 50 || mb_strlen($name)<=2) 
             {
                echo json_encode(['success' => false, 'message' => 'Некоректна довжина назви!']);
                exit;
            }

            $modelClass = $allowed[$table];

            $existing = $modelClass::findByCondition(['name' => $name]);
            if ($existing)
            {
                echo json_encode(['success' => false, 'message' => 'Такий запис вже існує']);
                exit;
            }

            $modelClass::Add($name);

            echo json_encode(['success' => true]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Невірний метод']);
        exit;
    }

    public function actionDeleteOldLogs()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['date'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Дата не вказана']);
            exit;
        }

        $date = $data['date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) 
        {
            echo json_encode(['success' => false, 'message' => 'Некоректний формат дати']);
            exit;
        }

        $selectedDate = strtotime($date);
        $minDate = strtotime('-3 days');

        if ($selectedDate > $minDate) 
        {
            echo json_encode(['success' => false, 'message' => 'Не можна видаляти логи за останні 3 дні']);
            exit;
        }

        Logs::deleteByCondition(['created_at <' => $date]);

        echo json_encode(['success' => true]);
        exit;
    }

    public function actionAddGlass()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        header("Content-Type: application/json");
        if (empty($data['name']) || empty($data['glass_type']) || empty($data['length_cm']) || empty($data['width_cm']) ||
            empty($data['thickness_mm']) || empty($data['cost'])) 
        {
            echo json_encode(['success' => false, 'message' => 'Всі поля обовʼязкові']);
            exit;
        }

        $glass = new \models\Glass();
        $glass->name = $data['name'];
        $glass->glass_type = (int)$data['glass_type'];
        $glass->length_cm = (float)$data['length_cm'];
        $glass->width_cm = (float)$data['width_cm'];
        $glass->thickness_mm = (int)$data['thickness_mm'];
        $glass->cost = (float)$data['cost'];

        $glass->save();

        echo json_encode(['success' => true]);
        exit;
    }

    public function actionGetGlassTypes()
    {
        $types = \models\GlassTypes::getAll();

        $result = array_map(function ($type) {
            return [
                'id' => $type['id'],
                'name' => $type['name']
            ];
        }, $types);

        echo json_encode($result);
        exit;
    }

    public function actionLogStats()
    {
        $days = isset($_GET['days']) ? intval($_GET['days']) : 7;
        $rows = Logs::Stats($days);
        $methodStats = Logs::StatsByMethod($days);

        $data = [];
        foreach ($rows as $row) {
            $data[$row['status_code']] = (int)$row['count'];
        }
         $methodData = [];
        foreach ($methodStats as $row)
            $methodData[$row['method']] = (int)$row['count'];


        header("Content-Type: application/json");
        echo json_encode(['success' => true, 'data' => $data, 'methods' => $methodData]);
        exit;
    }

    public function actionStats()
    {
        return $this->render();
    }




}