<?php
namespace Application\Utility;

use Zend\Log\Logger as Logger;
use Zend\Log\Writer\Stream as LogWriterStream;
use Application\Service\ApplicationServiceLocator as ServiceLocatorService;
use Application\Service\ApplicationSetting as SettingService;
use Localization\Service\Localization as LocalizationService;
use Application\Utility\ApplicationEmailNotification;

class ApplicationErrorLogger
{
    /**
     * Log an error
     *
     * @param string $errorMessage
     * @return boolean
     */
    public static function log($errorMessage)
    {
        try {
            $writer = new LogWriterStream(ServiceLocatorService::getServiceLocator()->get('Config')['paths']['error_log']);
            $logger = new Logger();
            $logger->addWriter($writer);
            $logger->err($errorMessage);

            // do we need send this error via email?
            if (null != ($errorEmail = SettingService::getSetting('application_errors_notification_email'))) {
                ApplicationEmailNotification::sendNotification($errorEmail,
                    SettingService::getSetting('application_error_notification_title', LocalizationService::getDefaultLocalization()['language']),
                    SettingService::getSetting('application_error_notification_message', LocalizationService::getDefaultLocalization()['language']), [
                        'find' => [
                            'ErrorDescription'
                        ],
                        'replace' => [
                            $errorMessage
                        ]
                    ]);
            }
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }
}