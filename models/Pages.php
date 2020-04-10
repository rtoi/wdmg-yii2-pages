<?php

namespace wdmg\pages\models;

use Yii;
//use yii\db\ActiveRecord;
use wdmg\base\models\ActiveRecordML;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%pages}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $source_id
 * @property string $name
 * @property string $alias
 * @property string $content
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property boolean $in_sitemap
 * @property boolean $in_turbo
 * @property boolean $in_amp
 * @property string $locale
 * @property boolean $status
 * @property string $route
 * @property string $layout
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Pages extends ActiveRecordML
{
    const STATUS_DRAFT = 0; // Page has draft
    const STATUS_PUBLISHED = 1; // Page has been published

    /**
     * Instance of \wdmg\pages\Module
     *
     * @var object
     */
    private $_module;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!($this->_module = Yii::$app->getModule('admin/pages', false)))
            $this->_module = Yii::$app->getModule('pages', false);

        if (isset(Yii::$app->params["pages.baseRoute"]))
            $this->baseRoute = Yii::$app->params["pages.baseRoute"];
        elseif (isset($this->_module->baseRoute))
            $this->baseRoute = $this->_module->baseRoute;

        if (isset(Yii::$app->params["pages.supportLocales"]))
            $this->supportLocales = Yii::$app->params["pages.supportLocales"];
        elseif (isset($this->_module->supportLocales))
            $this->supportLocales = $this->_module->supportLocales;

    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pages}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge([
            [['name', 'alias', 'content', 'locale'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['title', 'description', 'keywords'], 'string', 'max' => 255],
        ], parent::rules());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => Yii::t('app/modules/pages', 'ID'),
            'parent_id' => Yii::t('app/modules/pages', 'Parent ID'),
            'source_id' => Yii::t('app/modules/pages', 'Source ID'),
            'name' => Yii::t('app/modules/pages', 'Name'),
            'alias' => Yii::t('app/modules/pages', 'Alias'),
            'content' => Yii::t('app/modules/pages', 'Content'),
            'title' => Yii::t('app/modules/pages', 'Title'),
            'description' => Yii::t('app/modules/pages', 'Description'),
            'keywords' => Yii::t('app/modules/pages', 'Keywords'),
            'in_sitemap' => Yii::t('app/modules/pages', 'In sitemap?'),
            'in_turbo' => Yii::t('app/modules/pages', 'Yandex turbo-pages?'),
            'in_amp' => Yii::t('app/modules/pages', 'Google AMP?'),
            'locale' => Yii::t('app/modules/pages', 'Locale'),
            'status' => Yii::t('app/modules/pages', 'Status'),
            'route' => Yii::t('app/modules/pages', 'Route'),
            'layout' => Yii::t('app/modules/pages', 'Layout'),
            'created_at' => Yii::t('app/modules/pages', 'Created at'),
            'created_by' => Yii::t('app/modules/pages', 'Created by'),
            'updated_at' => Yii::t('app/modules/pages', 'Updated at'),
            'updated_by' => Yii::t('app/modules/pages', 'Updated by'),
        ], parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function getStatusesList($allStatuses = false)
    {
        $list = [];
        if ($allStatuses) {
            $list = [
                '*' => Yii::t('app/modules/pages', 'All statuses')
            ];
        }

        $list = ArrayHelper::merge($list, [
            self::STATUS_DRAFT => Yii::t('app/modules/pages', 'Draft'),
            self::STATUS_PUBLISHED => Yii::t('app/modules/pages', 'Published'),
        ]);

        return $list;
    }

    /**
     * @param bool $allLabel
     * @param bool $rootLabel
     * @return array
     */
    public function getParentsList($allLabel = true, $rootLabel = false)
    {

        if ($this->id) {
            $subQuery = self::find()->select('id')->where(['parent_id' => $this->id]);
            $query = self::find()->alias('pages')
                ->where(['not in', 'pages.parent_id', $subQuery])
                ->andWhere(['!=', 'pages.parent_id', $this->id])
                ->orWhere(['IS', 'pages.parent_id', null])
                ->andWhere(['!=', 'pages.id', $this->id])
                ->select(['id', 'name']);

            $pages = $query->asArray()->all();
        } else {
            $pages = self::find()->select(['id', 'name'])->asArray()->all();
        }

        if ($allLabel)
            return ArrayHelper::merge([
                '*' => Yii::t('app/modules/pages', '-- All pages --')
            ], ArrayHelper::map($pages, 'id', 'name'));
        elseif ($rootLabel)
            return ArrayHelper::merge([
                0 => Yii::t('app/modules/pages', '-- Root page --')
            ], ArrayHelper::map($pages, 'id', 'name'));
        else
            return ArrayHelper::map($pages, 'id', 'name');
    }

    /** ********************************************* **/

    /**
     * Returns the URL to the view of the current
     *
     * @param bool $withScheme
     * @param bool $realUrl
     * @return mixed|string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getPageUrl($withScheme = true, $realUrl = false)
    {
        return parent::getModelUrl($withScheme, $realUrl);
    }
}
