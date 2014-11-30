<?php
namespace User\PagePrivacy;

use Page\PagePrivacy\PageAbstractPagePrivacy;
use User\Service\UserIdentity as UserIdentityService;
use Application\Service\ApplicationSetting as SettingService;

class UserRegisterPrivacy extends PageAbstractPagePrivacy
{
    /**
     * Is allowed to view page
     *
     * @param array $privacyOptions
     * @param boolean $trustedData
     * @return boolean
     */
    public function isAllowedViewPage(array $privacyOptions = [], $trustedData = false)
    {
        return UserIdentityService::isGuest() && (int) SettingService::getSetting('user_allow_register');
    }
}