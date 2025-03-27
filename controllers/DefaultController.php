<?php

namespace wdmg\pages\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use wdmg\pages\models\Pages;

/**
 * DefaultController implements actions for Pages model.
 */
class DefaultController extends Controller
{
    /**
     * Default language locale
     * @var string|null
     */
    private $_lang;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        // Set a default layout
        $this->layout = $this->module->baseLayout;

        // Sets the default language locale
        $this->_lang = Yii::$app->sourceLanguage;
        if (isset(Yii::$app->translations)) {
            if (Yii::$app->translations->module->hideDefaultLang) {
                $this->_lang = Yii::$app->translations->getDefaultLang();
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * View of page.
     * If the page was found and it has a route setup that does not match the current route
     * of the request, an NotFoundHttpException will be thrown.
     * If the page does not have a route, such a check is not performed and the page can be
     * displayed if such a route is allowed as the default setting in the module.
     *
     * @param $alias
     * @param null $route
     * @param null $lang
     * @param bool $draft
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($alias, $route = null, $lang = null, $draft = false)
    {
        // Check probably need redirect to new page URL
        if (isset(Yii::$app->redirects)) {
            if (Yii::$app->redirects->check(Yii::$app->request->getUrl())) {
                return Yii::$app->redirects->check(Yii::$app->request->getUrl());
            }
        }

        // Separate route from request URL
        if (is_null($route) && preg_match('/^([\/]+[A-Za-z0-9_\-\_\/]+[\/])*([A-Za-z0-9_\-\_]*)/i', Yii::$app->request->url, $matches)) {
            if ($alias == $matches[2]) {
                $route = rtrim($matches[1], '/');
            }
        } else {
            // Normalize route
            $normalizer = new \yii\web\UrlNormalizer();
            $route = $normalizer->normalizePathInfo($route, '');
            $route = '/' . $route;
        }

        // If route is root
        if (empty($route)) {
            $route = '/';
        }

        // Search page model with alias
        $model = $this->findModel($alias, $route, $lang, $draft);
        // If model was not found and $lang is null, search again
        // now using the default value for the $lang parameter.
        if (is_null($model) && is_null($lang)) {
            $model = $this->findModel($alias, $route, $this->_lang, $draft);
        }
        if (is_null($model)) {
             throw new NotFoundHttpException(Yii::t('app/modules/pages', 'The requested page does not exist.'));
        }

        // Checking requested route with page route if set
        if (isset($model->route)) {
            if ($model->route !== $route) {
                throw new NotFoundHttpException();
            }
        }

        // Set a custom layout to render page
        if (isset($model->layout)) {
            $this->layout = $model->layout;
        }

        return $this->render('index', [
            'model' => $model,
            'route' => $route
        ]);
    }

    /**
     * Finds the Page model based on alias and route values.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param $alias
     * @param null $route
     * @param null $lang
     * @param bool $draft
     * @return Pages|null
    */
    protected function findModel($alias, $route = null, $lang = null, $draft = false)
    {
        $locale = null;
        if (!is_null($lang)) {
            $locales = [];
            if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
                $locales = Yii::$app->translations->getLocales(true, true, true);
                $locales = ArrayHelper::map($locales, 'url', 'locale');
            } else {
                if (is_array($this->module->supportLocales)) {
                    $supportLocales = $this->module->supportLocales;
                    foreach ($supportLocales as $locale) {
                        if ($lang === \Locale::getPrimaryLanguage($locale)) {
                            $locales[$lang] = $locale;
                            break;
                        }
                    }
                }
            }
            if (isset($locales[$lang])) {
                $locale = $locales[$lang];
            }
        }

        if ((!$draft) && !is_null($lang) && is_null($locale)) {
            return \null;
        }

        if (!$draft) {
            $cond = [
                'alias' => $alias,
                'route' => $route,
                'status' => Pages::STATUS_PUBLISHED,
            ];
            if (isset($locale)) {
                $cond['locale'] = $locale;
            }
        } else {
            $cond = [
              'alias' => $alias,
              'route' => $route,
              'status' => Pages::STATUS_DRAFT,
            ];
        }

        return Pages::findOne($cond);
    }
}
