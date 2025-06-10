<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $id ID користувача
    * @property string $first_name Ім'я користувача
    * @property string $last_name Прізвище користувача
    * @property string $username логін користувача
    * @property string $password Пароль користувача
    * @property string $profile_picture Аватарка користувача
    * @property enum $role admin, user, manager
    * @property string $phone Номер телефону користувача
    * @property bool $created_at час коли створено акаунт
    */
class Users extends Model
{
    public static $tableName = 'users';

    public static function FindByLoginAndPassword($username, $password)
    {
        $user = self::FindByLogin($username);
        if ($user && password_verify($password, $user['password'])) 
            return $user;

        return null;
    }

    public static function FindByLogin($username)
    {
        $rows = self::findByCondition(['username' => $username]);
        if(!empty($rows))
            return $rows[0];
        else
            return null;
    }

    public static function isUserLogged(): bool
    {
        return !empty(Core::get()->session->get('user'));
    }

    public static function hasRole(string $role): bool
    {
        $user = Core::get()->session->get('user');
        return isset($user['role']) && $user['role'] === $role;
    }


    public static function LoginUser($user)
    {
        Core::get()->session->set('user', $user);
        $db = Core::get()->db;
    }

    public static function LogoutUser($user)
    {
        $user = Core::get()->session->get('user');
        if ($user && isset($user['id'])) 
        {
            $db = Core::get()->db;
        }

        Core::get()->session->remove('user');
    }

    public static function RegisterUser($username, $password, $last_name, $first_name, $profile_picture, $phone, $role = 'user'): void
    {
        $user = new Users();
        $user->username = $username;
        $user->password = self::hashPassword($password);
        $user->last_name = $last_name;
        $user->first_name = $first_name;
        $user->profile_picture = $profile_picture;
        $user->phone = $phone;
        $user->role = in_array($role, ['admin', 'user']) ? $role : 'user';
        $user->save();
    }

    public static function getCurrentUser(): ?array
    {
        $user = Core::get()->session->get('user');
        if ($user && is_array($user)) 
            $user['profile_picture_url'] = self::getProfilePictureUrl($user['profile_picture']);

        return $user;
    }

    public static function getProfilePictureUrl(?string $relativePath): string
    {
        if (empty($relativePath)) 
            return '/crystal/assets/img/default-avatar.png';

        if (str_starts_with($relativePath, '/') || str_starts_with($relativePath, 'http')) 
            return $relativePath;

        return helper::asset($relativePath);
    }

    public static function hashPassword($password)
    {
        return password_hash($password,  PASSWORD_DEFAULT);
    }

    public static function getUsernameById($id)
    {
        $user = self::findById($id);
        return $user ? $user['username'] : null;
    }

}