<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use core\ItemsImageUploader;
use core\Session;
use models\Users;
use models\Items;
use models\Categories;
use models\ItemLikes;
use models\Genres;
use models\Glass;
use models\GlassTypes;
use models\ItemImages;

class ItemsController extends Controller
{
    public function beforeAction(string $action): bool
    {
        $public = ['index', 'view', 'filter', 'togglelike'];

        if (!in_array(strtolower($action), $public)) 
        {
            if (!Users::hasRole('admin')) 
            {
                $this->redirect('/crystal/items');
                return false;
            }
        }

        return true;
    }


    public function actionIndex($params = [])
    {
        $role = Users::getCurrentUser()['role'] ?? 'none';

        $page = isset($params[0]) ? max(1, intval($params[0])) : 1;
        $perPage = 4;
        $offset = ($page - 1) * $perPage;

        $items = Items::getItemsPaginated($perPage, $offset);
        $total = Items::getTotalCount();
        $totalPages = ceil($total / $perPage);

        foreach ($items as &$item) 
        {
            $category = Core::get()->db->select('categories', '*', ['id' => $item['category_id']]);
            $item['category_name'] = $category[0]['name'] ?? 'Без категорії';

            $genre = Core::get()->db->select('genres', '*', ['id' => $item['genre_id']]);
            $item['genre_name'] = $genre[0]['name'];

            $mainImage = ItemImages::getMainImage($item['id']);
            $item['image'] = $mainImage['path'] ?? '/crystal/assets/img/default-item.png';
        }

        $categories = Categories::getCategories();
        $genres = Genres::getAll();
        $unpublishedCount = Items::getUnpublishedCount();
        

        $this->template->setParams([
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'role' => $role,
            'categories' => $categories,
            'genres' => $genres,
            'unpublishedCount' => $unpublishedCount
        ]);
        return $this->render();
    }

    public function actionFilter()
    {
        if ($this->isPost) 
        {
            $title = $this->post->title ?? '';
            $code = $this->post->code ?? '';
            $categoryId = $this->post->category ?? '';
            $genreId = $this->post->genre ?? '';
            $sort = $this->post->sort ?? 'default';
            $page = max(1, intval($this->post->page ?? 1));
            $perPage = 4;
            $offset = ($page - 1) * $perPage;

            $items = Items::getFilteredItems($title, $code, $categoryId, $genreId, $sort, $perPage, $offset);
            $total = Items::getFilteredCount($title, $code, $categoryId, $genreId);

            foreach ($items as &$item) 
            {
                $category = Core::get()->db->select('categories', '*', ['id' => $item['category_id']]);
                $item['category_name'] = $category[0]['name'] ?? 'Без категорії';

                $genre = Core::get()->db->select('genres', '*', ['id' => $item['genre_id']]);
                $item['genre_name'] = $genre[0]['name'];
                $item['likes_count'] = ItemLikes::getLikesCount($item['id']);

                $mainImage = ItemImages::getMainImage($item['id']);
                $item['image'] = $mainImage['path'] ?? '/crystal/assets/img/default-item.png';
            }

            header('Content-Type: application/json');
            echo json_encode([
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'totalPages' => ceil($total / $perPage)
            ]);
            exit;
        }
    }

    public function actionView($params = [])
    {
        $user = Users::getCurrentUser();
        if($this->isGet)
        {
            $id = $this->get->id ?? ($params[0] ?? null);
            if (!$id)
                return $this->redirect('/crystal/items');

            $item = Items::findById($id);
            if (!$item || (strtotime($item['published_at']) > time() && !Users::hasRole('admin') && !Users::hasRole('manager')))
                return $this->redirect('/crystal/items');
            
            if ($item['glass_id']) 
            {
            $glass = Glass::findById($item['glass_id']);
            $glassType = $glass ? Core::get()->db->select('glass_types', '*', ['id' => $glass['glass_type']])[0] ?? null : null;

            $item['glass'] = $glass;
            $item['glass_type_name'] = $glassType['name'] ?? 'Невідомо';
            }

            $item['main_image'] = ItemImages::getMainImage($item['id']);

            $item['images'] = Core::get()->db->select('item_images', '*', [
                'item_id' => $item['id'],
                'is_main' => 0,
                'ORDER' => ['id' => 'ASC']
            ]);


            $category = Categories::findById($item['category_id']);
            $genre = Genres::findById($item['genre_id']);
            $item['category_name'] = $category['name'] ?? 'Без категорії';
            $item['likes_count'] = ItemLikes::getLikesCount($item['id']);
            $item['is_liked'] = $user ? ItemLikes::isLikedByUser($user['id'], $item['id']) : false;
            $item['genre_name'] = $genre['name'] ?? 'Без жанру';


            $this->template->setParams(['item' => $item]);
        
        }
        return $this->render();
    }

    public function actionToggleLike()
    {
        if ($this->isPost)
        {
            header('Content-Type: application/json');
            $user = Users::getCurrentUser();

            if (!$user) 
            {
                echo json_encode([
                    'success' => false,
                    'message' => 'Щоб поставити лайк, потрібно авторизуватися'
                ]);
                exit;
            }

            $itemId = intval($this->post->item_id);
            $userId = $user['id'];

            $liked = ItemLikes::isLikedByUser($userId, $itemId);

            if ($liked)
                ItemLikes::removeLike($userId, $itemId);
            else
                ItemLikes::addLike($userId, $itemId);

            $newCount = ItemLikes::getLikesCount($itemId);

            header('Content-Type: application/json');
            echo json_encode([
                'liked' => !$liked,
                'count' => $newCount,
                'success' => true
            ]);
            exit;
        }
    }

    public function actionUnpublished($params = [])
    {
        $role = Users::getCurrentUser()['role'] ?? 'none';

        $page = isset($params[0]) ? max(1, intval($params[0])) : 1;
        $perPage = 4;
        $offset = ($page - 1) * $perPage;

        $items = Items::getUnpublishedItems($perPage, $offset);
        $total = Items::getUnpublishedCount();
        $totalPages = ceil($total / $perPage);

        foreach ($items as &$item) 
        {
            $category = Core::get()->db->select('categories', '*', ['id' => $item['category_id']]);
            $item['category_name'] = $category[0]['name'] ?? 'Без категорії';

            $genre = Core::get()->db->select('genres', '*', ['id' => $item['genre_id']]);
            $item['genre_name'] = $genre[0]['name'];

            $mainImage = ItemImages::getMainImage($item['id']);
            $item['image'] = $mainImage['path'] ?? '/crystal/assets/img/default-item.png';
        }

        $categories = Categories::getCategories();
        $genres = Genres::getAll();

        $this->template->setParams([
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'role' => $role,
            'categories' => $categories,
            'genres' => $genres,
            'isUnpublished' => true
        ]);

        return $this->render('views/items/index.php');
    }

    public function actionFilterUnpublished()
    {
        if ($this->isPost) 
        {
            $title = $this->post->title ?? '';
            $code = $this->post->code ?? '';
            $categoryId = $this->post->category ?? '';
            $genreId = $this->post->genre ?? '';
            $sort = $this->post->sort ?? 'default';
            $page = max(1, intval($this->post->page ?? 1));
            $perPage = 4;
            $offset = ($page - 1) * $perPage;
            $genres = Genres::getAll();
            
            $items = Items::getFilteredUnpublishedItems($title, $code, $categoryId, $genreId, $sort, $perPage, $offset);
            $total = Items::getFilteredUnpublishedCount($title, $code, $categoryId, $genreId);

            foreach ($items as &$item) 
            {
                $category = Core::get()->db->select('categories', '*', ['id' => $item['category_id']]);
                $item['category_name'] = $category[0]['name'] ?? 'Без категорії';

                $genre = Core::get()->db->select('genres', '*', ['id' => $item['genre_id']]);
                $item['genre_name'] = $genre[0]['name'];

                $mainImage = ItemImages::getMainImage($item['id']);
                $item['image'] = $mainImage['path'] ?? '/crystal/assets/img/default-item.png';
            }

            header('Content-Type: application/json');
            echo json_encode([
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'genres' => $genres,
                'totalPages' => ceil($total / $perPage)
            ]);
            exit;
        }
    }



    public function actionPublish()
    {
        if ($this->isPost) 
        {
            if(!$this->post->id)
                return;
            $itemId = intval($this->post->id);
            Items::publishNow($itemId);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }




    public function actionDelete()
    {
        $id = $this->get->id;
        if (!$id)
            return $this->redirect('/crystal/items/');

        $item = Items::findById($id);
        if (!$item)
            return $this->redirect('/crystal/items/');

        if ($this->isPost)
        {
            ItemsImageUploader::deleteItemFolder($item['code']);

            Items::deleteById($id);
            return $this->redirect('/crystal/items/');
        }

        $this->template->setParams(['item' => $item]);
        return $this->render();
    }


    public function actionDeleteJson()
    {
        header('Content-Type: application/json');

        if (!$this->isPost || !Users::hasRole('admin')) 
        {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Доступ заборонено']);
            exit;
        }

        $id = $this->post->id ?? null;
        if (!$id || !is_numeric($id)) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }

        $item = Items::findById($id);
        if (!$item) 
        {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Картину не знайдено']);
            exit;
        }

        ItemsImageUploader::deleteItemFolder($item['code']);
        Items::deleteById($id);

        echo json_encode(['success' => true]);
        exit;
    }






    public function actionAdd()
    {
        $categories = Categories::getCategories();
        $genres = Genres::getAll();
        $glassTypes = Core::get()->db->select('glass_types', '*', '1 ORDER BY id ASC');
        $glasses = Core::get()->db->select('glass', '*', '1 ORDER BY id ASC');

        if ($this->isPost) 
        {
            $title = trim($this->post->title);
            $code = trim($this->post->code);
            $description = trim($this->post->description);
            $categoryId = $this->post->category_id;
            $genreId = $this->post->genre_id;
            $tarif = floatval($this->post->tarif);
            $discount = intval($this->post->discount);
            $glassId = strlen($this->post->glass_id)!=0 && is_numeric($this->post->glass_id) ? intval($this->post->glass_id) : null;
            $publishedAt = strlen($this->post->published_at)!=0 ? $this->post->published_at : date('Y-m-d H:i:s');

            if(empty($title))
                $this->addErrorMessage('Введіть назву для картини!');
            if(empty($code))
                $this->addErrorMessage('Введіть торговий код для картини!');
            $code_copy = Items::findByCondition(['code' => $code]);
            if($code_copy)
                $this->addErrorMessage('Такий торговий код вже є!');
            if(!is_numeric($categoryId))
                $this->addErrorMessage('Оберіть категорію для картини!');
            if(!is_numeric($genreId))
                $this->addErrorMessage('Оберіть жанр для картини!');
            if(empty($tarif) || $tarif <= 0)
                $this->addErrorMessage('Некоректний тариф!');
            if($discount < 0)
                $this->addErrorMessage('Некоректна знижка для картини!');

             if(!$this->isErrorMessageExist())
            {
                $itemId  = Core::get()->db->insert(
                    'items',
                [
                    'manager_id' => Users::getCurrentUser()['id'],
                    'title' => $title,
                    'code' => $code,
                    'description' => $description,
                    'category_id' => $categoryId,
                    'genre_id' => $genreId,
                    'tarif' => $tarif,
                    'discount' => $discount,
                    'glass_id' => $glassId,
                    'published_at' => $publishedAt,
                    'created_at' => date('Y-m-d H:i:s')
                ]
                );

                $images = $_FILES['images'] ?? null;
                $mainIndex = intval($this->post->main_image_index ?? 0);
                
                if ($images && is_array($images['name']))
                {
                    ItemsImageUploader::hasUploading($images, $code, $itemId, $mainIndex);
                }
                else 
                {
                    // якщо $_FILES['images'] взагалі не було
                    Core::get()->db->insert('item_images', [
                        'item_id' => $itemId,
                        'path' => ItemsImageUploader::getDefaultImage(),
                        'is_main' => 1
                    ]);
                }

                return $this->redirect('/crystal/items');
            }
        }

        $this->template->setParams([
            'categories' => $categories,
            'genres' => $genres,
            'glassTypes' => $glassTypes,
            'glasses' => $glasses
        ]);

        return $this->render();
    }

    public function actionEdit($params = [])
    {
        $id = $this->get->id ?? ($params[0] ?? null);
        if (!$id) 
            return $this->redirect('/crystal/items');

        $item = Items::findById($id);
        if (!$item) 
            return $this->redirect('/crystal/items');

        $item['images'] = Core::get()->db->select('item_images', '*', [
            'item_id' => $id,
            'ORDER' => ['is_main' => 'DESC', 'id' => 'ASC']
        ]);

        $categories = Categories::getCategories();
        $genres = Genres::getAll();
        $glassTypes = Core::get()->db->select('glass_types', '*', '1 ORDER BY id ASC');
        $glasses = Core::get()->db->select('glass', '*', '1 ORDER BY id ASC');

        if ($this->isPost)
        {
            $title = trim($this->post->title);
            $code = trim($this->post->code);
            $description = trim($this->post->description);
            $categoryId = $this->post->category_id;
            $genreId = $this->post->genre_id;
            $tarif = floatval($this->post->tarif);
            $discount = intval($this->post->discount);
            $glassId = strlen($this->post->glass_id) != 0 && is_numeric($this->post->glass_id) ? intval($this->post->glass_id) : null;
            $publishedAt = strlen($this->post->published_at) != 0 ? $this->post->published_at : date('Y-m-d H:i:s');
            $mainIndex = intval($this->post->main_image_index ?? 0);

            if (empty($title)) 
                $this->addErrorMessage('Введіть назву для картини!');
            if (empty($code)) 
                $this->addErrorMessage('Введіть торговий код для картини!');
            $code_copy = Items::findByCondition(['code' => $code]);
            if ($code_copy && $code_copy[0]['id'] != $id)
                $this->addErrorMessage('Такий торговий код вже існує для іншого товару!');
            if (!is_numeric($categoryId)) 
                $this->addErrorMessage('Оберіть категорію для картини!');
            if (!is_numeric($genreId)) 
                $this->addErrorMessage('Оберіть жанр для картини!');
            if (empty($tarif) || $tarif <= 0) 
                $this->addErrorMessage('Некоректний тариф!');
            if ($discount < 0) 
                $this->addErrorMessage('Некоректна знижка для картини!');

            if (!$this->isErrorMessageExist()) 
            {
                $oldCode = $item['code'];

                // Оновлення моделі
                $itemModel = new Items();
                $itemModel->id = $id;
                $itemModel->title = $title;
                $itemModel->code = $code;
                $itemModel->description = $description;
                $itemModel->category_id = $categoryId;
                $itemModel->genre_id = $genreId;
                $itemModel->tarif = $tarif;
                $itemModel->discount = $discount;
                $itemModel->glass_id = $glassId;
                $itemModel->published_at = $publishedAt;
                $itemModel->created_at = $item['created_at'];
                $itemModel->manager_id = $item['manager_id'];
                $itemModel->save();

                if ($oldCode !== $code) 
                {
                    $oldPath = $_SERVER['DOCUMENT_ROOT'] . "/crystal/assets/uploads/items/{$oldCode}";
                    $newPath = $_SERVER['DOCUMENT_ROOT'] . "/crystal/assets/uploads/items/{$code}";
                    if (is_dir($oldPath))
                        rename($oldPath, $newPath);
                    
                    $images = Core::get()->db->select('item_images', '*', ['item_id' => $id]);
                    foreach ($images as $img) 
                    {
                        if (!ItemsImageUploader::isDefaultImage($img['path'])) 
                        {
                            $newPath = str_replace("/$oldCode/", "/$code/", $img['path']);
                            Core::get()->db->update('item_images', ['path' => $newPath], ['id' => $img['id']]);
                        }
                    }
                    if (is_dir($oldPath) && count(glob("$oldPath/*")) === 0)
                        rmdir($oldPath);
                }

                // Обробка зображень
                $images = $_FILES['images'] ?? null;
                $hasUploaded = false;

                if ($images && is_array($images['error'])) 
                {
                    foreach ($images['error'] as $e) 
                    {
                        if ($e === UPLOAD_ERR_OK) 
                        {
                            $hasUploaded = true;
                            break;
                        }
                    }
                }

                if ($hasUploaded) 
                {
                    $existing = Core::get()->db->select('item_images', '*', ['item_id' => $id]);
                    foreach ($existing as $img) {
                        if (!ItemsImageUploader::isDefaultImage($img['path']))
                            ItemsImageUploader::deleteIfExists($img['path']);
                        Core::get()->db->delete('item_images', ['id' => $img['id']]);
                    }

                    ItemsImageUploader::hasUploading($images, $code, $id, $mainIndex);
                } 
                else 
                {
                    $existingImages = Core::get()->db->select('item_images', '*', ['item_id' => $id]);
                    foreach ($existingImages as $i => $img) {
                        Core::get()->db->update('item_images', [
                            'is_main' => ($i == $mainIndex) ? 1 : 0
                        ], ['id' => $img['id']]);
                    }
                }

                return $this->redirect('/crystal/items');
            }
        }

        $this->template->setParams([
            'item' => $item,
            'categories' => $categories,
            'genres' => $genres,
            'glassTypes' => $glassTypes,
            'glasses' => $glasses
        ]);

        return $this->render();
    }







}