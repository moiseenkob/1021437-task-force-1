<?php

namespace frontend\controllers;

use yii\db\Query;
use yii\web\Controller;

class UsersController extends Controller
{
    public function actionIndex()
    {

        $users = (new Query())->select(
            [
                'u.name',
                'u.registration_at',
                'count(t.id) AS count_task',
                'count(f.id) AS count_feedback',
                'round(avg(f.rating), 2) as rating',
                'info' => '(SELECT user_info FROM profile WHERE user_id = u.id)',
                'last_visit' => '(SELECT last_visit FROM profile WHERE user_id = u.id)',
                'categories' => '(SELECT GROUP_CONCAT(c.name SEPARATOR ", ") FROM user_category JOIN category AS c ON c.id = categories_id WHERE user_id = u.id)'
            ])
            ->from('user u')
            ->join('LEFT JOIN', 'task t', 't.executor_id = u.id')
            ->join('LEFT JOIN', 'feedback f', 'f.author_id = u.id')
            ->where('exists(SELECT * FROM user_category WHERE user_id = u.id)')
            ->groupBy('u.id')
            ->orderBy('u.registration_at desc')
            ->all();

        return $this->render('index', compact('users'));
    }

}