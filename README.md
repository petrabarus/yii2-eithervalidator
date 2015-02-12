# yii2-eithervalidator
Yii2 validator to force one of attributes to be filled.

## Installation

Add the following line to the composer.

```
"petrabarus/yii2-eithervalidator": "*"
```

## Usage

Add the a line similar to the following to rule of the model

```php
[['email'], PetraBarus\Yii2\Validators\EitherValidator::class, 
	'otherAttributes' => ['phone']]
```
