<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use core\ItemsImageUploader;
use core\Session;
use models\Categories;
use models\Tags;
use models\Users;

class TagsController extends Controller
{
    public function beforeAction(string $action): bool
    {
        $public = [];

        if (!in_array(strtolower($action), $public)) 
        {
            if (!Users::hasRole('admin')) 
            {
                $this->redirect('/crystal/news');
                return false;
            }
        }

        return true;
    }

    public function actionIndex()
    {
        $tags = Tags::getAll();
        $this->template->setParams(['tags' => $tags]);
        return $this->render();
    }

    public function actionAdd()
    {
        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
                $this->addErrorMessage('Назва тегу не може бути порожньою');
            else
            {
                if (Tags::findByName($name))
                    $this->addErrorMessage('Тег з такою назвою вже існує');
                else
                {
                    Tags::Add($name);
                    return $this->redirect('/crystal/tags/index');
                }
            }
        }

        return $this->render();
    }

    public function actionEdit($params = [])
    {
        $id = $this->get->id ?? ($params[0] ?? null);
        if (!$id) return $this->redirect('/crystal/tags/index');

        $tag = Tags::findById($id);
        if (!$tag) return $this->redirect('/crystal/tags/index');

        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
            {
                $this->addErrorMessage('Назва тегу не може бути порожньою');
            }
            else
            {
                $existingTag = Tags::findByName($name);
                // Перевірка, щоб не змінити на ім'я існуючого тегу, крім цього
                if ($existingTag && $existingTag['id'] != $id)
                {
                    $this->addErrorMessage('Тег з такою назвою вже існує');
                }
                else
                {
                    $tagModel = new Tags();
                    $tagModel->id = $id;
                    $tagModel->name = $name;
                    $tagModel->save();

                    return $this->redirect('/crystal/tags/index');
                }
            }
        }

        $this->template->setParams(['tag' => $tag]);
        return $this->render();
    }


    public function actionDelete($params = [])
    {
        $id = $this->post->id ?? ($this->get->id ?? ($params[0] ?? null));

        if (!is_numeric($id)) 
            return $this->redirect('/crystal/tags/index');

        $deleted = Tags::deleteById($id);

        if ($this->isPost) 
        {
            header('Content-Type: application/json');
            echo json_encode(['success' => '1']);
            exit;
        } 
        else
            return $this->redirect('/crystal/tags/index');
    }





}