<?php
/**
 * @copyright Copyright (c) Custom Code IT
 */

namespace customcodeit\checkin\controllers;

use Craft;
use craft\errors\InvalidPluginException;
use craft\helpers\App;
use craft\web\Controller;
use craft\web\ServiceUnavailableHttpException;
use customcodeit\checkin\CheckIn;
use customcodeit\checkin\models\SettingsModel;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use craft\models\Updates as UpdatesModel;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;
    protected array|bool|int $allowAnonymous = ['index'];


    /**
     * @throws ServiceUnavailableHttpException
     * @throws UnauthorizedHttpException
     * @throws HttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        /** @var SettingsModel $settings */
        $settings = CheckIn::$plugin->getSettings();

        if(!parent::beforeAction($action))
        {
            return false;
        }

        $submittedToken = Craft::$app->getRequest()->getParam('token', '');

        $validToken = App::parseEnv($settings->apiKey);

        if(empty($submittedToken) || empty($validToken) || $submittedToken !== $validToken)
        {
            throw new HttpException(403, 'Unauthorized');
        }

        return true;
    }

    /**
     * Gets a list of updates available
     * Inspiration taken from the Craft UpdateController
     * @return Response
     */
    public function actionIndex(): Response
    {
        $updateData = Craft::$app->getApi()->getUpdates();
        $updates = new UpdatesModel($updateData);
        $updateCount = $updates->getTotal();

        $updateJson = [];

        if($updateCount === 0)
        {
            return $this->asJson([]);
        }

        if($updates->cms->getHasReleases())
        {
            $updateJson[] = $this->parseUpdate('craft', Craft::$app->version, $updates->cms->getLatest()->version, $updates->cms->getHasCritical(), $updates->cms->status, $updates->cms->phpConstraint);
        }

        $plugins = Craft::$app->getPlugins();
        foreach($updates->plugins as $handle => $update)
        {
            if($update->getHasReleases())
            {
                try {
                    $pluginInfo = $plugins->getPluginInfo($handle);
                } catch (InvalidPluginException) {
                    continue;
                }
                if ($pluginInfo['isInstalled']) {
                    $updateJson[] = $this->parseUpdate($handle, $pluginInfo['version'], $update->getLatest()->version, $update->getHasCritical(), $update->status, $update->phpConstraint);
                }
            }
        }

        return $this->asJson($updateJson);
    }

    /**
     * Return structured information about the update.
     * Inspiration taken from the Craft UpdateController.
     * @param string $handle
     * @param string $from
     * @param string $to
     * @param bool $critical
     * @param string $status
     * @param string|null $phpConstraint
     * @return array
     */
    private function parseUpdate(string $handle, string $from, string $to, bool $critical, string $status, ?string $phpConstraint = null): array
    {
        return [
            'handle' => $handle,
            'from' => $from,
            'to' => $to,
            'isCritical' => $critical,
            'status' => $status,
        ];
    }

}