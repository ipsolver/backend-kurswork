<?php

namespace controllers;

use core\Template;
use core\Controller;
use core\Core;
use core\Session;
use models\Contacts;
use models\Users;

class ContactsController extends Controller
{
    public function beforeAction(string $action): bool
    {
        $public = ['index'];

        if (!in_array(strtolower($action), $public)) 
        {
            if (!Users::hasRole('admin')) 
            {
                $this->redirect('/crystal/contacts');
                return false;
            }
        }

        return true;
    }

    public function actionIndex()
    {
        $role = Users::getCurrentUser()['role'] ?? 'none';

       $contacts = Contacts::getAll();
        $this->template->setParams(['contacts' => $contacts, 'role' => $role]);
            return $this->render();
    }

    public function actionDelete()
    {
        if ($this->isPost) 
        {
            $id = intval($this->post->id ?? 0);

            if (!$id) 
            {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Невірний ID']);
                exit;
            }

            $deleted = Contacts::deleteById($id);

            header('Content-Type: application/json');
            echo json_encode(['success' => !Contacts::findById($id)]);
            exit;
        }
        $this->redirect('/crystal/contacts/index');
    }

    public function actionAdd()
    {
        if ($this->isPost) 
        {
            $title = trim($this->post->title);
            $content = trim($this->post->content);
            $colorBg = trim($this->post->color_bg ?? '#ffffff');
            $colorText = trim($this->post->color_text ?? '#000000');

            if (!$title || !$content)
            {
                echo json_encode(['success' => false, 'message' => 'Заповніть усі поля']);
                exit;
            }

            $contact_id = Core::get()->db->insert(
                'contacts',
                [
                    'title' => $title,
                    'content' => $content,
                    'color_bg' => $colorBg,
                    'color_text' => $colorText
                ]
                );

            echo json_encode([
                'success' => true,
                'contact' => Contacts::findById($contact_id)
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function actionEdit()
    {
        if ($this->isPost) 
        {
            $id = intval($this->post->id ?? 0);
            $title = trim($this->post->title);
            $content = $this->post->content;
            $colorBg = trim($this->post->color_bg ?? '#ffffff');
            $colorText = trim($this->post->color_text ?? '#000000');

            if (!$id || !$title || !$content) 
            {
                echo json_encode(['success' => false, 'message' => 'Заповніть усі поля']);
                exit;
            }

            $contact = new Contacts();
            $contact->id = $id;
            $contact->title = $title;
            $contact->content = $content;
            $contact->color_bg = $colorBg;
            $contact->color_text = $colorText;
            $contact->save();

            echo json_encode(['success' => true, 'contact' => Contacts::findById($id)]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;
    }





}