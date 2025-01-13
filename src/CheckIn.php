<?php
/**
 * @copyright Copyright (c) Custom Code IT
 */

namespace customcodeit\checkin;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use yii\base\Event;
use craft\web\UrlManager;

/**
 *
 * @property-read string[] $cpRoutes
 */
class CheckIn extends Plugin
{
    public bool $hasCpSection = false;
    public bool $hasCpSettings = false;
    public string $schemaVersion = '1.0.0';

    public static CheckIn $plugin;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->onInit(function() {
            $this->registerApiRoutes();
        });
    }

    private function registerApiRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'GET api/checkin' => 'checkin/api/index',
                ]);
            }
        );
    }
}