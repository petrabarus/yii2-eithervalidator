<?php

/**
 * EitherValidator class file.
 * 
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2015.02.12
 */

namespace PetraBarus\Yii2\Validators;

use Yii;
use yii\helpers\Html;

/**
 * EitherValidator forces one of attribute in numbers of attributes have to be
 * filled.
 * 
 * To use this
 * 
 * ```
 *    [['email', EitherValidator::class, 
 *          'otherAttributes' => ['phone'], 
 *          'message' => Yii::t('app', 'Either attribute {attribute}, {other_attribute} is required')
 *    ]
 * ```
 * 
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2015.02.12
 */
class EitherValidator extends \yii\validators\Validator {

    /**
     * The error message.
     * @var string
     */
    public $message;

    /**
     * List of other attributes that have to be filled.
     * @var array
     */
    public $otherAttributes = [];

    /**
     * This has to set as false or it defeats the purpose.
     * @var false
     */
    public $skipOnEmpty = false;

    public function init() {
        if (empty($this->otherAttributes)) {
            throw new Exception(Yii::t('yii', 'Other attributes are not set'));
        }
        if (!isset($this->message)) {
            $this->message = Yii::t('yii', 'Either \'{attribute}\', \'{other_attributes}\' has to be filled');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute the name of the attribute.
     */
    public function validateAttribute($model, $attribute) {
        $values = [];
        $values[$attribute] = $model->{$attribute};
        foreach ($this->otherAttributes as $otherAttribute) {
            $values[$otherAttribute] = $model->{$otherAttribute};
        }
        if (count(array_filter($values, function($e) {
                     return !isset($e) || strlen($e) > 0;
                })) == 0) {
            $this->addError($model, $attribute, $this->message, $this->getErrorParams($model, $attribute));
        }
    }

    /**
     * @param \yii\base\Model $model the model to be validated.
     * @param string $attribute the name of the attribute to be validated.
     */
    private function getErrorParams($model, $attribute) {
        return [
            'attribute' => $model->getAttributeLabel($attribute),
            'other_attributes' => implode(', ', array_map(function($e) use($model) {
                                      return $model->getAttributeLabel($e);
                            }, $this->otherAttributes)),
        ];
    }

    /**
     * @param \yii\base\Model $model the model to be validated.
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view.
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        $options = [
            'attribute' => Html::getInputId($model, $attribute),
        ];
        foreach ($this->otherAttributes as $otherAttribute) {
            $options['otherAttributes'][] = Html::getInputId($model, $otherAttribute);
        }
        $options['message'] = Yii::$app->getI18n()->format($this->message,
                $this->getErrorParams($model, $attribute), Yii::$app->language);
        $optionJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
(function(){
    var options = {$optionJson};
    var values = [];
    values.push($('#' + options.attribute).val());
    for (i in options.otherAttributes) {
        values.push($('#' + options.otherAttributes[i]).val());
    }
    if (values.filter(function(e){
            return e.length > 0;
        }).length == 0) {
        messages.push(options.message);
    }
})();
JS;
    }

}

