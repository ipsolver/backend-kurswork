<?php

namespace controllers;
use core\Template;
use core\Controller;
use core\Config;
use models\Users;
use models\Items;
use models\ItemLikes;
use models\News;
use models\Orders;

class HomeController extends Controller
{
    public function actionIndex()
    {
        $featuredItems = Items::getMostLikedItems(4);

        //Статистика
        $totalUsers = count(Users::findByCondition(['id >' => '0']));
        $totalItems = Items::getTotalCount();
        $unpublishedItems = Items::getUnpublishedCount();
        $totalNews = News::getTotalCount();
        $totalOrders = Orders::countCompletedOrders();

        $this->template->setParams([
        'featuredItems' => $featuredItems,
        'totalUsers' => $totalUsers,
        'totalItems' => $totalItems,
        'unpublishedItems' => $unpublishedItems,
        'totalNews' => $totalNews,
        'totalOrders' => $totalOrders
        ]);
        return $this->render();
    }


    public function actionAbout()
    {
        $config = Config::get();
        $this->template->setParams(
            [
            'admin' => $config->admin,
            'title' => $config->title,
            'description' => $config->description
            ]);
    return $this->render();
    }

}
