<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Core;
use core\ImageUploader;
use core\Session;
use models\Users;

class ProfileController extends Controller
{
    public function actionIndex()
    {
        if (!Users::isUserLogged()) 
        {
            return $this->redirect('/crystal/profile/login');
        }
        $db = Core::get()->db;

        return $this->render();
    }

    public function actionLogin()
    {
        if(Users::IsUserLogged())
            return $this->redirect('/crystal');

        if($this->isPost)
        {
            $user = Users::FindByLoginAndPassword($this->post->username, $this->post->password);
            if(!empty($user))
            {
                Users::LoginUser($user);
                return $this->redirect('/crystal');
            }
            else
            {
                $error_message = 'Неправильний логін та/або пароль';
                $this->addErrorMessage($error_message);
            }
        }
        
        return $this->render();
    }

    public function actionRegister()
    {
        if(Users::IsUserLogged())
            return $this->redirect('/crystal');
        if($this->isPost)
        {
            $username = trim($this->post->username);
            $password = trim($this->post->password);
            $password2 = trim($this->post->password2);
            $first_name = trim($this->post->first_name);
            $last_name = trim($this->post->last_name);
            $phone = trim($this->post->phone);

            $user = Users::FindByLogin($this->post->username);
            if(!empty($user))
            {
                $this->addErrorMessage('Користувач із таким логіном вже існує!');
            }
            if(strlen($username) === 0)
                $this->addErrorMessage('Логін не вказано!');
            if(strlen($password) === 0)
                $this->addErrorMessage('Пароль не вказано!');
            if(strlen($password2) === 0)
                $this->addErrorMessage('Пароль (ще раз) не вказано!');
            if($password != $password2)
                $this->addErrorMessage('Паролі не співпадають!');
            if(strlen($password) <3)
                $this->addErrorMessage('Пароль занадто малий!');
            if(strlen($first_name) === 0)
                $this->addErrorMessage('Ім\'я не вказано!');
            if(strlen($last_name) === 0)
                $this->addErrorMessage('Прізвище не вказано!');
            if(strlen($phone) === 0)
                $this->addErrorMessage('Номер телефону не вказано!');
            if (!preg_match('/^0\d{9}$/', $phone))
                $this->addErrorMessage('Некоректний номер телефону!');
            
            if(!$this->isErrorMessageExist())
            {
                $imagePath = ImageUploader::handleUpload('profile_picture', $this->post->username);
                Users::RegisterUser($username, $password, $last_name,
                $first_name, $imagePath, $phone);
                

                $newUser = Users::FindByLogin($this->post->username);
                Users::LoginUser($newUser);
    
                return $this->redirect('/crystal');
            }
        }
        return $this->render();
    }

    public function actionRegistered()
    {
        if(Users::IsUserLogged())
            return $this->redirect('/crystal');
        return $this->render();
    }

    public function actionLogout()
    {
        Users::LogoutUser($user);
        return $this->redirect('/crystal');
    }

    public function actionSettings()
    {
        if (!Users::isUserLogged()) 
        {
            return $this->redirect('/crystal/profile/login');
        }
        
        $currentUser = Users::getCurrentUser();
        $userModel = new Users();
        $userModel->id = $currentUser['id'];

        if ($this->isPost) 
        {
            $first_name = trim($this->post->first_name);
            $last_name = trim($this->post->last_name);
            $username = trim($this->post->username);
            $phone = trim($this->post->phone);

            if (strlen($first_name) === 0 || strlen($last_name) === 0
             || strlen($username) === 0 || strlen($phone) === 0)
                $this->addErrorMessage('Не заповнені обов\'язкові поля!');

            if (!preg_match('/^\+?[0-9]{7,15}$/', $phone)) 
                $this->addErrorMessage('Некоректний номер телефону!');

            $userModel->first_name = $first_name;
            $userModel->last_name = $last_name;
            $userModel->username = $username;
            $userModel->phone = $phone;
            
            // Зміна аватарки
            if (!empty($_FILES['profile_picture']['name'])) 
            {
                $newImagePath = ImageUploader::handleUpload('profile_picture', $this->post->username);
    
                if ($newImagePath) 
                {
                    $currentAvatar = $currentUser['profile_picture'];

                    if (!ImageUploader::isDefaultAvatar($currentAvatar)) 
                    {
                        ImageUploader::deleteIfExists($currentAvatar);
                    }

                    $userModel->profile_picture = $newImagePath;
                }
            }
            else
                $userModel->profile_picture = $currentUser['profile_picture'];

            // Зміна паролю
            if (strlen($this->post->password) !=0 && strlen($this->post->new_password) != 0 && strlen($this->post->new_password2) != 0) 
            {
                if ($this->post->new_password !== $this->post->new_password2) 
                    $this->addErrorMessage('Нові паролі не співпадають!');

                $realUser = Users::FindByLoginAndPassword($currentUser['username'], $this->post->password);
                if (!$realUser)
                    $this->addErrorMessage('Старий пароль вказано невірно!');

                $userModel->password = Users::hashPassword($this->post->new_password);
            }
            else
                $userModel->password = $currentUser['password'];

            if(!$this->isErrorMessageExist())
            {
                $userModel->save();
                $updated = Users::findById($userModel->id);
                Users::LoginUser($updated);

                return $this->redirect('/crystal/profile');
            }
        }
        
        return $this->render();
    }

    public function actionDelete()
    {
        if (!Users::isUserLogged()) 
        {
            return $this->redirect('/crystal/profile/login');
        }

        $currentUser = Users::getCurrentUser();

         if ($currentUser['role'] == "admin")
        {
            return $this->redirect('/crystal/profile/');
        }

        if ($this->isPost) 
        {
            $password = $this->post->password;

            if (empty($password))
                $this->addErrorMessage('Введіть пароль для підтвердження!');

            $user = Users::FindByLoginAndPassword($currentUser['username'], $password);
            if (!$user)
                $this->addErrorMessage('Неправильний пароль!');

            if (!$this->isErrorMessageExist()) 
            {
                if (!ImageUploader::isDefaultAvatar($user['profile_picture']))
                    ImageUploader::deleteIfExists($user['profile_picture']);

                Users::deleteById($user['id']);

                Users::LogoutUser($user);
                Core::get()->session->set('deleted', true);
                return $this->redirect('/crystal/profile/deleted');
            }
        }

        return $this->render();
    }

    public function actionDeleted()
    {
        $deletedFlag = Core::get()->session->get('deleted');
        if ($deletedFlag !== true)
            return $this->redirect('/crystal/');


        Core::get()->session->remove('deleted');
        return $this->render();
    }

}