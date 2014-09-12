<?php
namespace User\PagePrivacy;

use Application\Utility\RouteParam as RouteParamUtility;
use Application\Service\ServiceManager as ServiceManagerService;
use Page\PagePrivacy\AbstractPagePrivacy;
use User\Model\UserWidget as UserWidgetModel;

class UserViewPrivacy extends AbstractPagePrivacy
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = ServiceManagerService::getServiceManager()
                ->get('Application\Model\ModelManager')
                ->getInstance('User\Model\UserWidget');
        }

        return $this->model;
    }

    /**
     * Is allowed view page
     * 
     * @param array $privacyOptions
     * @return boolean
     */
    public function isAllowedViewPage(array $privacyOptions = [])
    {
        $userId = !empty($privacyOptions['user_id']) 
            ? $privacyOptions['user_id'] 
            : RouteParamUtility::getParam('slug', -1);

        $userField = !empty($privacyOptions['user_id']) 
            ? UserWidgetModel::USER_INFO_BY_ID
            : UserWidgetModel::USER_INFO_BY_SLUG;

        if (null == ($userInfo = $this->getModel()->getUserInfo($userId, $userField))) {
            return false;
        }

        return true;
    }
}