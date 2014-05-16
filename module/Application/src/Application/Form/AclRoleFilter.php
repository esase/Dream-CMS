<?php

namespace Application\Form;

use Application\Model\Acl as AclModelBase;
use Application\Form\CustomFormBuilder;

class AclRoleFilter extends AbstractCustomForm 
{
    /**
     * Form name
     * @var string
     */
    protected $formName = 'filter';

    /**
     * Form method
     * @var string
     */
    protected $method = 'get';

    /**
     * List of not validated elements
     * @var array
     */
    protected $notValidatedElements = array('submit');

    /**
     * Form elements
     * @var array
     */
    protected $formElements = array(
        0 => array(
            'name' => 'type',
            'type' => CustomFormBuilder::FIELD_SELECT,
            'label' => 'Type',
            'values' => array(
               AclModelBase::ROLE_TYPE_SYSTEM => 'system',
               AclModelBase::ROLE_TYPE_CUSTOM => 'custom'
            )
        ),
        1 => array(
            'name' => 'submit',
            'type' => CustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Search',
        )
    );
}