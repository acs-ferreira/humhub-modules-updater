<?php

namespace humhub\modules\updater;

use Yii;
use yii\base\Exception;
use humhub\models\Setting;

class Module extends \humhub\components\Module
{

    /**
     * Returns the temp path of updater
     * 
     * @return type
     */
    public static function getTempPath()
    {
        $path = Yii::getAlias('@runtime/updater');

        if (!is_dir($path)) {
            if (!@mkdir($path)) {
                throw new Exception("Could not create updater runtime folder!");
            }
        }

        if (!is_writable($path)) {
            throw new Exception("Updater directory is not writeable!");
        }

        return $path;
    }

    public function getCurlOptions()
    {
        // Compatiblity for older versions
        if (!class_exists('humhub\libs\CURLHelper')) {
            $options = array(
                CURLOPT_SSL_VERIFYPEER => (Yii::$app->getModule('admin')->marketplaceApiValidateSsl) ? true : false,
                CURLOPT_SSL_VERIFYHOST => (Yii::$app->getModule('admin')->marketplaceApiValidateSsl) ? 2 : 0,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_CAINFO => Yii::getAlias('@humhub/config/cacert.pem')
            );

            if (Setting::Get('enabled', 'proxy')) {
                $options[CURLOPT_PROXY] = Setting::Get('server', 'proxy');
                $options[CURLOPT_PROXYPORT] = Setting::Get('port', 'proxy');
                if (defined('CURLOPT_PROXYUSERNAME')) {
                    $options[CURLOPT_PROXYUSERNAME] = Setting::Get('user', 'proxy');
                }
                if (defined('CURLOPT_PROXYPASSWORD')) {
                    $options[CURLOPT_PROXYPASSWORD] = Setting::Get('pass', 'proxy');
                }
                if (defined('CURLOPT_NOPROXY')) {
                    $options[CURLOPT_NOPROXY] = Setting::Get('noproxy', 'proxy');
                }
            }

            return $options;
        }

        return \humhub\libs\CURLHelper::getOptions();
    }

}
