<?php
namespace Page\Controller;

use Application\Controller\AbstractBaseController;

class PageController extends AbstractBaseController
{
    /**
     * Index page
     */
    public function indexAction()
    {
    echo __METHOD__ , '<br>';
    print_r(explode('/', $this->getPageName()));
        // check received page
        /*$currentPage = array_filter(explode('/', $this->getPageName()));
        //print_r(array_filter($currentPage));
        $currentPage = end($currentPage);
        
        echo $currentPage , '<br>';;
        echo $this->getPage('page'), '<br>';
        echo $this->getSlug('page'), '<br>';
        return false;
        // ���������� ������ �����, ����� � ������ ���� �������� ������� �������� � ����� �������
        // ��������. ���� ���� ������ �� ��� ok ���� �� ������ 404, ���� ������������� �������� ��� 404
        */
       // echo $this->request->getUri();;
        echo '<br>'.$this->params()->fromRoute('page_name') , '<br>';
        echo $this->getPage(), '<br>';
        echo $this->getPerPage(), '<br>';
        echo $this->getOrderBy(), '<br>';
        echo $this->getSlug(), '<br>';
        echo $this->getExtra(), '<br>';
        return false;
    }
}