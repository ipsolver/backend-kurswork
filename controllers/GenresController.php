<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use models\Users;
use models\Items;
use models\Genres;


class GenresController extends Controller
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
        $genres = Genres::getAll();
        foreach ($genres as &$genre)
            $genre['item_count'] = Genres::getItemCountByGenre($genre['id']);

        $this->template->setParams(['genres' => $genres]);
        return $this->render();
    }

    public function actionAdd()
    {
        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
                $this->addErrorMessage('Назва жанру не може бути порожньою');
            else
            {
                if (Genres::findByName($name))
                    $this->addErrorMessage('Жанр з такою назвою вже існує');
                else
                {
                    Genres::Add($name);
                    return $this->redirect('/crystal/genres/index');
                }
            }
        }

        return $this->render();
    }

    public function actionEdit($params = [])
    {
        $id = $this->get->id ?? ($params[0] ?? null);
        if (!$id) 
            return $this->redirect('/crystal/genres/index');

        $genre = Genres::findById($id);
        if (!$genre) 
            return $this->redirect('/crystal/genres/index');

        if ($this->isPost)
        {
            $name = trim($this->post->name);

            if (empty($name))
            {
                $this->addErrorMessage('Назва жанру не може бути порожньою');
            }
            else
            {
                $existingGen = Genres::findByName($name);

                if ($existingGen && $existingGen['id'] != $id)
                    $this->addErrorMessage('Жанр з такою назвою вже існує');

                else
                {
                    $genModel = new Genres();
                    $genModel->id = $id;
                    $genModel->name = $name;
                    $genModel->save();

                    return $this->redirect('/crystal/genres/index');
                }
            }
        }

        $this->template->setParams(['genre' => $genre]);
        return $this->render();
    }

    public function actionDelete($params = [])
    {
        $id = $this->post->id ?? ($this->get->id ?? ($params[0] ?? null));

        if (is_numeric($id)) 
        {
            $deleted = Genres::deleteById($id);

            header('Content-Type: application/json');
            echo json_encode(['success' => !Genres::findById($id)]);
            exit;
        }

        return $this->redirect('/crystal/genres/index');
    }



}