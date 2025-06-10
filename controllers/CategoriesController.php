<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use models\Users;
use models\Items;
use models\Categories;


class CategoriesController extends Controller
{


    public function beforeAction(string $action): bool
    {
        $public = [];

        if (!in_array(strtolower($action), $public)) 
        {
            if (!Users::hasRole('admin') && !Users::hasRole('manager')) 
            {
                $this->redirect('/crystal/items');
                return false;
            }
        }

        return true;
    }

    
    public function actionIndex()
    {
        $categories = Categories::getCategories();
        foreach ($categories as &$category)
            $category['item_count'] = Categories::getItemCountByCategory($category['id']);

        $this->template->setParams(['categories' => $categories]);
        return $this->render();
    }

    public function actionAdd()
    {
        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
                $this->addErrorMessage('Назва категорії не може бути порожньою');
            else
            {
                if (Categories::findByName($name))
                    $this->addErrorMessage('Категорія з такою назвою вже існує');
                else
                {
                    Categories::Add($name);
                    return $this->redirect('/crystal/categories/index');
                }
            }
        }

        return $this->render();
    }

    public function actionEdit($params = [])
    {
        $id = $this->get->id ?? ($params[0] ?? null);
        if (!$id) 
            return $this->redirect('/crystal/categories/index');

        $category = Categories::findById($id);
        if (!$category) 
            return $this->redirect('/crystal/categories/index');

        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
            {
                $this->addErrorMessage('Назва категорії не може бути порожньою');
            }
            else
            {
                $existingCat = Categories::findByName($name);
                if ($existingCat && $existingCat['id'] != $id)
                    $this->addErrorMessage('Категорія з такою назвою вже існує');

                else
                {
                    $catModel = new Categories();
                    $catModel->id = $id;
                    $catModel->name = $name;
                    $catModel->save();

                    return $this->redirect('/crystal/categories/index');
                }
            }
        }

        $this->template->setParams(['category' => $category]);
        return $this->render();
    }

    public function actionDelete($params = [])
    {
        $id = $this->post->id ?? ($this->get->id ?? ($params[0] ?? null));

        if (is_numeric($id)) 
        {
            $deleted = Categories::deleteById($id);

            header('Content-Type: application/json');
            echo json_encode(['success' => !Categories::findById($id)]);
            exit;
        }

        return $this->redirect('/crystal/categories/index');
    }


}