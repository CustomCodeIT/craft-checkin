<?php
/**
 * @copyright Copyright (c) Custom Code IT
 */

namespace customcodeit\checkin\models;

use Craft;
use craft\base\Model;

class SettingsModel extends Model
{
    public string $apiKey = '';

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['apiKey'], 'required'];

        return $rules;
    }

    public function attributeLabels(): array
    {
        $labels = parent::attributeLabels();

        $labels['apiKey'] = Craft::t('checkin', 'API Key');

        return $labels;
    }
}