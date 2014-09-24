<?php
namespace User\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use User\Event\UserEvent;

abstract class UserAbstractWidget extends PageAbstractWidget
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Auth service
     * @var object  
     */
    protected $authService;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('User\Model\UserWidget');
        }

        return $this->model;
    }

    /**
     * Get auth service
     *
     * @return object
     */
    protected function getAuthService()
    {
        if (!$this->authService) {
            $this->authService = $this->getServiceLocator()->get('User\AuthService');
        }

        return $this->authService;
    }

    /**
     * Login user
     *
     * @param integer $userId
     * @param string $userNickname
     * @param boolean $rememberMe
     * @return string
     */
    protected function loginUser($userId, $userNickname, $rememberMe = false)
    {
        $user = [];
        $user['user_id'] = $userId;

        // save user id
        $this->getAuthService()->getStorage()->write($user);

        // fire the user login event
        UserEvent::fireLoginEvent($userId, $userNickname);

        if ($rememberMe) {
            $this->serviceLocator->
                    get('Zend\Session\SessionManager')->rememberMe((int) $this->getSetting('user_session_time'));
        }

        // check the user's dashboard url
        $userDashboard = $this->getView()->pageUrl('user-dashboard', ['skip_checking' => 'true']);

        return false !== $userDashboard
            ? $this->redirectTo(['page_name' => $userDashboard])
            : $this->redirectTo(); // redirect to home page
    }
}