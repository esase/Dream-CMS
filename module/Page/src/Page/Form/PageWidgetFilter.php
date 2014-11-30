<?php
namespace Page\Form;

use Application\Form\ApplicationAbstractCustomForm;
use Application\Form\ApplicationCustomFormBuilder;
use Page\Model\PageBase as PageBaseModel;

class PageWidgetFilter extends ApplicationAbstractCustomForm 
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
    protected $notValidatedElements = ['submit'];

    /**
     * Model
     * @var object
     */
    protected $model;

    /**
     * Hide status filter
     * @var boolean
     */
    protected $hideStatusFilter;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'modules' => [
            'name' => 'modules',
            'type' => ApplicationCustomFormBuilder::FIELD_MULTI_CHECKBOX,
            'label' => 'Module',
            'values' => []
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Search'
        ]
    ];

    /**
     * Get form instance
     *
     * @return object
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            if ($this->model) {
                // fill the form with default values
                $this->formElements['modules']['values'] = $this->model->getActiveModulesList();
            }

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }

    /**
     * Set model
     *
     * @param object $model
     * @return object fluent interface
     */
    public function setModel(PageBaseModel $model)
    {
        $this->model = $model;
        return $this;
    }
}