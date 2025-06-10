<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use core\OrdersImageUploader;
use models\News;
use models\Users;
use models\Tags;
use models\Orders;
use models\Categories;
use models\Genres;
use models\Items;
use models\ItemImages;
use models\Glass;
use models\GlassTypes;


class OrdersController extends Controller
{

        public function beforeAction(string $action): bool
        {
            $restrictedActions = ['index', 'add', 'delete', 'filter'];

            if (in_array(strtolower($action), $restrictedActions)) 
            {
                if (!Users::hasRole('admin') && !Users::hasRole('user')) 
                {
                    Core::get()->router->error(403);
                     return false;
                }
            }

            return true;
        }

    public function actionIndex()
    {
       $this->template->setParams([]);
        return $this->render();
    }

    public function actionAdd()
    {
        $currentUser = Users::getCurrentUser();
        $username = $currentUser['username'];
        $categories = Categories::getCategories();
        $genres = Genres::getAll();


        $itemData = null;
        if (isset($_GET['from_item'])) 
        {
            $itemId = (int)$_GET['from_item'];
            $itemData = Items::findById($itemId);

            if ($itemData) 
            {
                $mainImage = ItemImages::getMainImage($itemId);
                $preDescription = "Подобається картина \"{$itemData['title']}\" (код: {$itemData['code']})";

                if (!empty($itemData['glass_id'])) 
                {
                    $glass = Glass::getGlass($itemData['glass_id']);
                    $glass_type = GlassTypes::findById($glass['glass_type']);
                    $preDescription .= ", розміри: {$glass['length_cm']}×{$glass['width_cm']} см, скло: {$glass_type['name']} ({$glass['name']})";
                }

                $this->template->setParams([
                    'preDescription' => $preDescription,
                    'preCategoryId' => $itemData['category_id'],
                    'preGenreId' => $itemData['genre_id'],
                    'preImagePath' => $mainImage['path'] ?? null,
                ]);
            }

        }


        if ($this->isPost) 
        {
            $user_id = $currentUser['id'];
            $description = trim($this->post->description);
            $deadline = $this->post->deadline;
            $category_id = strlen($this->post->category_id) > 0 && $this->post->category_id !== '' ? (int)$this->post->category_id : null;           
            $genre_id = strlen($this->post->genre_id) > 0 && $this->post->genre_id !== '' ? (int)$this->post->genre_id : null;
            
            if (empty($description) || empty($deadline))
                $this->addErrorMessage("Опис і дата дедлайну обов’язкові");
            if (strtotime($deadline) < time())
                    $this->addErrorMessage("Дата дедлайну не може бути в минулому");


            if (!$this->isErrorMessageExist()) 
            {
                $orderId  = Core::get()->db->insert(
                    'orders',
                [
                    'user_id' => $currentUser['id'],
                    'description' => $description,
                    'category_id' => $category_id,
                    'genre_id' => $genre_id,
                    'deadline' => $deadline
                ]
                );

                $imagePath = null;
                if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) 
                {
                    $imagePath = OrdersImageUploader::uploadSingle($_FILES['image'], $username, $orderId);
                    if ($imagePath)
                        Core::get()->db->update('orders', ['image' => $imagePath], ['id' => $orderId]);
                }

                elseif (!empty($mainImage['path'])&& $mainImage['path'] != "/crystal/assets/img/default-item.png")
                {
                    $originalPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($mainImage['path'], '/');
                     if (file_exists($originalPath)) 
                    {
                     $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
                     $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/crystal/assets/uploads/orders/{$username}";
                      if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0775, true);

                    $pathDB = "/crystal/assets/uploads/orders/{$username}/{$username}_{$orderId}_order." . $ext;
                    $target = $_SERVER['DOCUMENT_ROOT'].'/'.$pathDB;

                    if (copy($originalPath, $target)) 
                    {
                        $imagePath = $pathDB;
                        Core::get()->db->update('orders', ['image' => $imagePath], ['id' => $orderId]);
                    } 
                    else
                        error_log("Не вдалося скопіювати зображення з $originalPath до $target");
                    }
                }

                return $this->redirect('/crystal/orders');
            }
        }
            $this->template->setParams([
                'categories' => $categories,
                'genres' => $genres
            ]);
            return $this->render();
    }

    public function actionFilter()
    {
        header('Content-Type: application/json');

        $status = $this->post->status ?? null;
        $page = max((int)($this->post->page ?? 1), 1);
        $perPage = 6;
        $offset = ($page - 1) * $perPage;

        $currentUser = Users::getCurrentUser();

        $orders = Orders::getFilteredOrders($currentUser['id'], $status, $perPage, $offset);
        $totalOrders = Orders::getFilteredCount($currentUser['id'], $status);
        $totalPages = ceil($totalOrders / $perPage);

        echo json_encode([
            'orders' => $orders,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
        exit;
    }

    public function actionDelete()
    {
        if($this->isPost)
        {
            header('Content-Type: application/json');
            $id = $this->post->id;

        if (!$id || !is_numeric($id)) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }

        $order = Orders::findById($id);
        if (!$order) 
        {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Замовлення не знайдено']);
            exit;
        }
        
        if (!in_array($order['status'], ['Обробка', 'Відхилено'])) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Не можна видалити дане замовлення!']);
            exit;            
        }

        $currentUser = Users::getCurrentUser();
        if ($order['user_id'] != $currentUser['id'] && $currentUser['role']!="admin") 
        {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'У вас немає прав на видалення цього замовлення']);
            exit;
        }

        // OrdersImageUploader::deleteOrderImage($currentUser['username'], $order['id']);
        // Orders::deleteById($id);
        Orders::deleteWithImage($order);
        echo json_encode(['success' => true]);
        exit;
        }
    }

    public function actionView()
    {
        if ($this->isPost) 
        {
            header('Content-Type: application/json');
            $id = $this->post->id;

            if (!$id || !is_numeric($id)) 
            {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Невірний ID']);
                exit;
            }

            $order = Orders::findById($id);
            if (!$order) 
            {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Замовлення не знайдено']);
                exit;
            }

            $currentUser = Users::getCurrentUser();
            if ($order['user_id'] != $currentUser['id']) 
            {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'У вас немає прав на перегляд цього замовлення']);
                exit;
            }

            $order['user_fullname'] = $currentUser['last_name']." ".$currentUser['first_name'];
            $order['user_phone'] = $currentUser['phone'];

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

            echo json_encode(['success' => true, 'order' => $order]);
            exit;
        }
    }

    public function actionupdateStatus()
    {
        if($this->isPost)
        {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $status = $data['status'] ?? '';

            if (!$id || !$status) 
            {
                echo json_encode(['success' => false]);
                exit;
            }
            ///////////////////////////
            $db = Core::get()->db;

        if ($status === 'Прийнято') 
        {
            $currentOrder = $db->select('orders', '*', ['id' => $id])[0] ?? null;

            if (!$currentOrder) 
            {
                echo json_encode(['success' => false, 'message' => 'Замовлення не знайдено']);
                exit;
            }

            $newStart = new \DateTime();
            $deadline = new \DateTime($currentOrder['deadline']);
            $newEnd = (clone $deadline)->modify('+3 days');

            $acceptedOrders = $db->select('orders', '*', [
                'status' => 'Прийнято',
            ]);

            $overlappingCount = 0;
            foreach ($acceptedOrders as $order) 
            {
                if (empty($order['accepted_at'])) 
                    continue;

                $start = new \DateTime($order['accepted_at']);
                $end = new \DateTime($order['deadline']);
                $end->modify('+3 days');

                // перевірка перетину
                if ($newStart <= $end && $start <= $newEnd)
                    $overlappingCount++;
            }

            if ($overlappingCount >= 2) 
            {
                echo json_encode([
                    'success' => false,
                    'message' => 'Неможливо прийняти більше 2 замовлень з перекриванням у часі'
                ]);
                exit;
            }
        }
            //////////////////////////
            $fields = ['status' => $status];

            if ($status === 'Прийнято')
                $fields['accepted_at'] = date('Y-m-d H:i:s');
            elseif ($status === 'Готово')
                {
                    $completedAt = new \DateTime();
                    $fields['completed_at'] = $completedAt->format('Y-m-d H:i:s');

                    $currentOrder = $db->select('orders', '*', ['id' => $id])[0] ?? null;

                    if ($currentOrder) 
                    {
                        $deadline = new \DateTime($currentOrder['deadline']);
                        if ($completedAt > $deadline) 
                        {
                            $diffDays = (int)$deadline->diff($completedAt)->format('%a');
                            $discount = $diffDays * 5;
                            $fields['discount'] = min($discount, 100);
                        }
                        else
                            $fields['discount'] = 0;
                    }
                }


            $success = Core::get()->db->update('orders', $fields, ['id' => $id]);

            echo json_encode(['success' => $success]);
            exit;
        }
    }



}