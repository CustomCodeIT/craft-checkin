<?php
/**
 * @copyright Copyright (c) Custom Code IT
 */

namespace customcodeit\checkin;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use customcodeit\checkin\models\SettingsModel;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use craft\web\UrlManager;
use yii\base\Exception;

/**
 *
 * @property-read string[] $cpRoutes
 * @property-read SettingsModel $settings
 */
class CheckIn extends Plugin
{
    public bool $hasCpSection = false;
    public bool $hasCpSettings = true;
    public string $schemaVersion = '1.0.0';

    public static CheckIn $plugin;

    /**
     * Do setup things.
     * @return void
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->onInit(function() {
            $this->registerApiRoutes();
        });
    }

    /**
     * Register the routes that can be accessed.
     * @return void
     */
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

    /**
     * Define our settings model
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new SettingsModel();
    }

    /**
     * Render our settings template
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws Exception
     * @throws LoaderError
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('checkin/_settings.twig', [
            'settings' => $this->getSettings()
        ]);
    }
}