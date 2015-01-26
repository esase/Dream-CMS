<?php
namespace User\View\Widget;

use User\Event\UserEvent;
use User\Service\UserIdentity as UserIdentityService;

class UserDeleteWidget extends UserAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        if (!UserIdentityService::isGuest()) {
            // get the user delete form
            $deleteForm = $this->getServiceLocator()
                ->get('Application\Form\FormManager')
                ->getInstance('User\Form\UserDelete');

            $request = $this->getRequest();

            // validate the form
            if ($request->isPost() &&
                    $this->getRequest()->getPost('form_name') == $deleteForm->getFormName()) {

                // fill the form with received values
                $deleteForm->getForm()->setData($request->getPost(), false);

                // delete the user's account
                if ($deleteForm->getForm()->isValid()) {
                    if (true !== ($deleteResult = $this->
                            getModel()->deleteUser(UserIdentityService::getCurrentUserIdentity(), false))) {

                        $this->getFlashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->translate('Error occurred'));

                        return $this->reloadPage();
                    }

                    // clear user's identity
                    $this->logoutUser(UserIdentityService::getCurrentUserIdentity());
                    return $this->redirectTo();
                }
            }

            return $this->getView()->partial('user/widget/delete', [
                'delete_form' => $deleteForm->getForm()
            ]);
        }

        return false;
    }

    /**
     * Logout user
     *
     * @param array $userIdentity
     * @return void
     */
    protected function logoutUser(array $userIdentity)
    {
        // clear logged user's identity
        UserIdentityService::getAuthService()->clearIdentity();

        // skip a remember me time
        $this->getServiceLocator()->get('Zend\Session\SessionManager')->rememberMe(0);

        // fire the user logout event
        UserEvent::fireLogoutEvent($userIdentity['user_id'], $userIdentity['nick_name']);
    }
}