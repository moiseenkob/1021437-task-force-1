<?php

namespace frontend\models;

use frontend\controllers\AttachmentController;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UploadedFile;

class CreateTaskForm extends Model
{

    public $title;
    public $description;
    public $category;
    public $budget;
    public $ends_at;

    public function attributeLabels()
    {
        return [
            'title' => 'Мне нужно',
            'description' => 'Подробности задания',
            'category' => 'Категория',
            'budget' => 'Бюджет',
            'ends_at' => 'Срок исполнения',
            'imageFile' => 'Файлы'
        ];
    }

    public function rules()
    {
        return [
            [['title', 'description', 'category'], 'required'],
            [['title', 'description'], 'trim'],
            ['title', 'string', 'min' => 10, 'max' => 255],
            ['description', 'string', 'min' => 30],
            ['category', 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id', 'message' => 'Выбранной категории не существует'],
            ['budget', 'integer', 'min' => '1', 'tooSmall' => 'Значение должно быть целым положительным числом'],
            ['ends_at', 'date', 'format' => 'Y-m-d'],
            ['ends_at', 'checkValidateDate'],
        ];
    }

    public function checkValidateDate($attribute, $params)
    {
        if (strtotime($this->$attribute) < strtotime('tomorrow')) {
            $this->addError($attribute, 'Дата окончания не может быть меньше даты начала задачи');
        }
    }

    public function saveTask()
    {
        AttachmentController::saveImage();

        if ($this->validate()) {
            $task = new Task();
            $task->title = $this->title;
            $task->description = $this->description;
            $task->budget = $this->budget;
            $task->author_id = Yii::$app->user->id;
            $task->ends_at = $this->ends_at;
            $task->category_id = $this->category;
            $task->save();

            if (Yii::$app->session['imageFile']) {
                AttachmentController::attachFiles(Yii::$app->session['imageFile'], $task->id);
                $session = Yii::$app->session;
                unset($session['imageFile']);
            }
        }
        return $task->id ?? null;
    }
}
