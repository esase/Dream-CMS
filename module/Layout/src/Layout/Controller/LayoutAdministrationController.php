<?php
namespace Layout\Controller;

use Application\Controller\ApplicationAbstractAdministrationController;
use Zend\View\Model\ViewModel;

class LayoutAdministrationController extends ApplicationAbstractAdministrationController
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
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Layout\Model\LayoutAdministration');
        }

        return $this->model;
    }

    /**
     * List of not installed custom layouts
     */
    public function listNotInstalledAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        // get data
        $paginator = $this->getModel()->getNotInstalledLayouts($this->
                getPage(), $this->getPerPage(), $this->getOrderBy(), $this->getOrderType());

        return [
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ];
    }

    /**
     * Install selected layouts
     */
    public function installAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($layoutsIds = $request->getPost('layouts', null))) {
                // install selected layouts
                $installResult = false;
                $installedCount = 0;

                foreach ($layoutsIds as $layout) {
                    // get the layout's config
                    $layoutInstallConfig = $this->getModel()->getCustomLayoutInstallConfig($layout);

                    if (false === $layoutInstallConfig) {
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // install the layout
                    if (true !== ($installResult = $this->
                            getModel()->installCustomLayout($layout, $layoutInstallConfig))) {

                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage(($installResult ? $this->getTranslator()->translate($installResult)
                                : $this->getTranslator()->translate('Error occurred')));

                        break;
                    }

                    $installedCount++;
                }

                if (true === $installResult) {
                    $message = $installedCount > 1
                        ? 'Selected layouts have been installed'
                        : 'The selected layout has been installed';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $this->redirectTo('layouts-administration', 'list-not-installed', [], true);
    }

    /**
     * Upload a new layout
     */
    public function uploadAction()
    {
        $request = $this->getRequest();
        $host = $request->getUri()->getHost();

        // get an layout form
        $layoutForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Layout\Form\Layout')
            ->setHost($host);

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $layoutForm->getForm()->setData($post, false);

            // upload a layout
            if ($layoutForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // upload the layout
                if (true === ($result =
                        $this->getModel()->uploadCustomLayout($layoutForm->getForm()->getData(), $host))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Layout has been uploaded'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('layouts-administration', 'upload');
            }
        }

        return new ViewModel([
            'layout_form' => $layoutForm->getForm()
        ]);
    }

    /**
     * Delete a layout
     */
    public function deleteAction()
    {
        $layoutName = $this->getSlug();

        // layout should be not installed
        if (null != ($layoutInfo = $this->getModel()->getLayoutInfo($layoutName)) 
                || false === ($layoutInstallConfig = $this->getModel()->getCustomLayoutInstallConfig($layoutName))) {

            return $this->createHttpNotFoundModel($this->getResponse());
        }

        $request = $this->getRequest();
        $host = $request->getUri()->getHost();

        // get an layout form
        $layoutForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Layout\Form\Layout')
            ->setHost($host)
            ->setDeleteMode();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $layoutForm->getForm()->setData($request->getPost(), false);

            // delete a layout
            if ($layoutForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                if (true === ($result = $this->getModel()->
                        deleteCustomLayout($layoutName, $layoutForm->getForm()->getData(), $host))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Layout has been deleted'));

                    return $this->redirectTo('layouts-administration', 'list-not-installed');
                }

                $this->flashMessenger()
                    ->setNamespace('error')
                    ->addMessage($this->getTranslator()->translate($result));

                return $this->redirectTo('layouts-administration', 'delete', [
                    'slug' => $layoutName
                ]);
            }
        }

        return new ViewModel([
            'layout_name' => $layoutName,
            'layout_form' => $layoutForm->getForm()
        ]);
    }

    /**
     * Upload updates
     */
    public function uploadUpdatesAction()
    {
        $request = $this->getRequest();
        $host = $request->getUri()->getHost();

        // get an layout form
        $layoutForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Layout\Form\Layout')
            ->setHost($host);

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $layoutForm->getForm()->setData($post, false);

            // upload updates
            if ($layoutForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                if (true === ($result = $this->
                        getModel()->uploadLayoutUpdates($layoutForm->getForm()->getData(), $host))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Updates of layout have been uploaded'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('layouts-administration', 'upload-updates');
            }
        }

        return new ViewModel([
            'layout_form' => $layoutForm->getForm()
        ]);
    }
}