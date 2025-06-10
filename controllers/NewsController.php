<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use models\News;
use models\Users;
use models\Tags;
use core\NewsImageUploader;

class NewsController extends Controller
{

        public function beforeAction(string $action): bool
        {
            $restrictedActions = ['add', 'edit', 'delete'];

            if (in_array(strtolower($action), $restrictedActions)) 
            {
                if (!Users::hasRole('admin') && !Users::hasRole('manager')) 
                {
                    $this->redirect('/crystal/news');
                     return false;
                }
            }

            return true;
        }


     public function actionIndex($params = [])
    {
        $role = Users::getCurrentUser()['role'] ?? 'none';

        $page = isset($params[0]) ? max(1, intval($params[0])) : 1;
        $perPage = 6;
        $offset = ($page - 1) * $perPage;

        $news = News::getNewsPaginated($perPage, $offset);

        foreach ($news as &$item)
            $item['tags'] = News::getTags($item['id']);
        $tags = Tags::getAll();

        $total = News::getTotalCount();
        $totalPages = ceil($total / $perPage);

        $this->template->setParams([
            'news' => $news,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'role' => $role,
            'tags' => $tags
        ]);
        return $this->render();
    }

    public function actionSearch()
    {
        $query = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $tagId = $_GET['tag'] ?? 'all';


        $perPage = 6;
        $offset = ($page - 1) * $perPage;

        if ($query || $tagId) 
        {
            $news = News::searchNews($query, $tagId, $perPage, $offset);
            $total = News::getSearchCount($query, $tagId);
        } 
        else 
        {
            $news = News::getNewsPaginated($perPage, $offset);
            $total = News::getTotalCount();
        }

        foreach ($news as &$item)
            $item['tags'] = News::getTags($item['id']);


        header('Content-Type: application/json');
        echo json_encode([
            'news' => $news,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $perPage)
        ]);
        exit;
    }

    public function actionView($params = [])
    {
        if($this->isGet)
        {
            $id = $this->get->id ?? ($params[0] ?? null);
            if (!$id)
                return $this->redirect('/crystal/news');

            $newsItem = News::findById($id);

            if (!$newsItem) 
                return $this->redirect('/crystal/news/index');

            $newsItem['tags'] = News::getTags($newsItem['id']);

            $this->template->setParams([
                'newsItem' => $newsItem
            ]);
        }

        return $this->render();
    }

    
    public function actionAdd()
    {

        if ($this->isPost) 
        {
            $title = trim($this->post->title);
            $short_text = trim($this->post->short_text);
            $content = trim($this->post->content);
            $tags = $this->post->tags ?? [];

            if(strlen($this->post->title) === 0)
                $this->addErrorMessage('Назва не вказана!');
            if(strlen($this->post->content) === 0)
                $this->addErrorMessage('Контент не вказано!'); 

            if(!$this->isErrorMessageExist())
            {
                
                $imagePath = NewsImageUploader::handleUpload('image', $title);
                News::Add($title, $short_text, $content, $imagePath, $tags);
               return $this->redirect('/crystal/news/index'); 
            }
            
            

            $this->addErrorMessage('Всі поля мають бути заповнені');
        }

        return $this->render();
    }

    public function actionEdit($params = [])
    {

        $id = $this->get->id ?? ($params[0] ?? null);
        if (!$id)
            return $this->redirect('/crystal/news/index');

        $newsItem = News::findById($id);
        if (!$newsItem)
            return $this->redirect('/crystal/news/index');

        if ($this->isPost)
        {
            $title = trim($this->post->title);
            $short_text = trim($this->post->short_text);
            $content = trim($this->post->content);

            if (empty($title)) 
                $this->addErrorMessage('Назва не вказана!');
            if (empty($content)) 
                $this->addErrorMessage('Контент не вказано!');

            $newsModel = new News();
            $newsModel->id = $newsItem['id'];

            if (!$this->isErrorMessageExist())
            {
                $imagePath = $newsItem['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE)
                {
                    $imagePath = NewsImageUploader::handleUpload('image', $title);
                    if (!NewsImageUploader::isDefaultNewsImage($newsItem['image']))
                        NewsImageUploader::deleteIfExists($newsItem['image']);
                }
                $newsModel->title = $title;
                $newsModel->short_text = $short_text;
                $newsModel->content = $content;
                $newsModel->image = $imagePath;
                $newsModel->save();

                // Оновлення тегів
                $selectedTagIds = $this->post->tags ?? [];

                $selectedTagIds = array_filter($selectedTagIds, fn($id) => is_numeric($id));
                $selectedTagIds = array_map('intval', $selectedTagIds);

                // Поточні теги
                $currentTags = News::getTags($newsModel->id);
                $currentTagIds = array_column($currentTags, 'id');

                $tagsToAdd = array_diff($selectedTagIds, $currentTagIds);
                $tagsToRemove = array_diff($currentTagIds, $selectedTagIds);

                foreach ($tagsToAdd as $tagId)
                    News::addTag($newsModel->id, $tagId);

                foreach ($tagsToRemove as $tagId)
                    News::removeTag($newsModel->id, $tagId);

                return $this->redirect('/crystal/news/index');
            }

        }

        $allTags = Tags::getAll();
        $currentTags = News::getTags($newsItem['id']);
        $currentTagIds = array_column($currentTags, 'id');


        $this->template->setParams([
            'newsItem' => $newsItem,
            'allTags' => $allTags,
            'currentTagIds' => $currentTagIds,
        ]);
        return $this->render();
    }

    public function actionDelete()
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

        $newsItem = News::findById($id);
        if (!$newsItem) 
        {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Новину не знайдено']);
            exit;
        }

        if (!NewsImageUploader::isDefaultNewsImage($newsItem['image']))
            NewsImageUploader::deleteIfExists($newsItem['image']);

        News::deleteById($id);
        echo json_encode(['success' => true]);
        exit;
    }


    

}