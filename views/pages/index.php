<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */

$this->title = $this->context->module->name;
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="pages-index">

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {

                    $output = Html::tag('strong', $model->name);

                    if (isset($model->route)) {
                        $route = $model->route;
                    } else {
                        if (is_array($this->context->module->pagesRoute)) {
                            $route = array_shift($this->context->module->pagesRoute);
                        } else {
                            $route = $this->context->module->pagesRoute;
                        }
                    }

                    if($model->alias)
                        $output .= '<br/>' . Html::a(Url::to($route."/".$model->alias, true), Url::to($route."/".$model->alias, true), [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);

                    return $output;
                }
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->title), 0, 64, '…');

                    if (mb_strlen($model->title) > 81)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                            'class' => 'fa fa-fw fa-warning',
                            'title' => Yii::t('app/modules/pages','Field exceeds the recommended length of {length} characters.', [
                                'length' => 80
                            ])
                        ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->description), 0, 64, '…');

                    if (mb_strlen($model->description) > 161)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                                'class' => 'fa fa-fw fa-warning',
                                'title' => Yii::t('app/modules/pages','Field exceeds the recommended length of {length} characters.', [
                                    'length' => 160
                                ])
                            ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],
            [
                'attribute' => 'keywords',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->keywords), 0, 64, '…');

                    if (mb_strlen($model->keywords) > 181)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                                'class' => 'fa fa-fw fa-warning',
                                'title' => Yii::t('app/modules/pages','Field exceeds the recommended length of {length} characters.', [
                                    'length' => 180
                                ])
                            ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($data->status == $data::PAGE_STATUS_PUBLISHED)
                        return '<span class="label label-success">'.Yii::t('app/modules/pages','Published').'</span>';
                    elseif ($data->status == $data::PAGE_STATUS_DRAFT)
                        return '<span class="label label-default">'.Yii::t('app/modules/pages','Draft').'</span>';
                    else
                        return $data->status;
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/pages','Actions'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
            ]
        ]
    ]); ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/pages', 'Add new page'), ['pages/create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>
