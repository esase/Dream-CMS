<?php

namespace Membership\Form;

use Application\Form\CustomFormBuilder;
use Application\Form\AbstractCustomForm;
use Application\Service\Service as ApplicationService;
use Membership\Model\MembershipAdministration as MembershipAdministrationModel;

class AclRole extends AbstractCustomForm 
{
    /**
     * Cost max string length
     */
    const COST_MAX_LENGTH = 11;

    /**
     * Lifetime string length
     */
    const LIFETIME_MAX_LENGTH = 4;

    /**
     * Expiration notification string length
     */
    const EXPIRATION_NOTIFICATION_MAX_LENGTH = 4;

    /**
     * Description string length
     */
    const DESCRIPTION_MAX_LENGTH = 65535;

    /**
     * Form name
     * @var string
     */
    protected $formName = 'acl-role';

    /**
     * List of ignored elements
     * @var array
     */
    protected $ignoredElements = array('image');

    /**
     * Image
     * @var string
     */
    protected $image;

    /**
     * Edit mode
     * @var boolean
     */
    protected $editMode = false;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = array(
        'role_id' => array(
            'name' => 'role_id',
            'type' => CustomFormBuilder::FIELD_SELECT,
            'label' => 'Role',
            'required' => true,
            'category' => 'General info',
        ),
        'cost' => array(
            'name' => 'cost',
            'type' => CustomFormBuilder::FIELD_FLOAT,
            'label' => 'Cost',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::COST_MAX_LENGTH
        ),
        'lifetime' => array(
            'name' => 'lifetime',
            'type' => CustomFormBuilder::FIELD_INTEGER,
            'label' => 'Lifetime in days',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::LIFETIME_MAX_LENGTH
        ),
        'expiration_notification' => array(
            'name' => 'expiration_notification',
            'type' => CustomFormBuilder::FIELD_INTEGER,
            'label' => 'Expiration notification reminder in days',
            'description' => 'You can remind  users about the expiration after N days after the beginning',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::EXPIRATION_NOTIFICATION_MAX_LENGTH
        ),
        'description' => array(
            'name' => 'description',
            'type' => CustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Description',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::DESCRIPTION_MAX_LENGTH
        ),
        'image' => array(
            'name' => 'image',
            'type' => CustomFormBuilder::FIELD_IMAGE,
            'label' => 'Image',
            'required' => true,
            'extra_options' => array(
                'file_url' => null,
                'preview' => false,
                'delete_option' => false
            ),
            'category' => 'General info'
        ),
        'active' => array(
            'name' => 'active',
            'type' => CustomFormBuilder::FIELD_CHECKBOX,
            'label' => 'Active',
            'required' => false,
            'category' => 'General info'
        ),
        'language' => array(
            'name' => 'language',
            'type' => CustomFormBuilder::FIELD_SELECT,
            'label' => 'Language',
            'required' => false,
            'category' => 'Localization',
        ),
        'submit' => array(
            'name' => 'submit',
            'type' => CustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Submit',
        ),
    );

    /**
     * Get form instance
     *
     * @return object
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            // get list of acl roles
            $this->formElements['role_id']['values'] = ApplicationService::getAclRoles();

            // init localizations
            $localizations = ApplicationService::getLocalizations();
            if (count($localizations) > 1) {
                $languages = array();
                foreach ($localizations as $localization) {
                    $languages[$localization['language']] = $localization['description'];
                }

                $this->formElements['language']['values'] = $languages;
                $this->formElements['language']['value']  = ApplicationService::getCurrentLocalization()['language'];
            }
            else {
                unset($this->formElements['language']);
            }

            // add preview for the image
            if ($this->image) {
                $this->formElements['image']['required'] = false;
                $this->formElements['image']['extra_options']['preview'] = true;
                $this->formElements['image']['extra_options']['file_url'] =
                        ApplicationService::getResourcesUrl() . MembershipAdministrationModel::getImagesDir() . $this->image;
            }

            // init edit mode
            if ($this->editMode) {
                unset($this->formElements['role_id']);
            }

            // add extra validators
            $this->formElements['expiration_notification']['validators'] = array(
                array (
                    'name' => 'callback',
                    'options' => array(
                        'callback' => array($this, 'validateExpirationNotification'),
                        'message' => 'The expiration notification value  must be less than role\'s lifetime'
                    )
                )
            );

            $this->form = new CustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }

    /**
     * Set an image
     *
     * @param string $image
     * @return object fluent interface
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set edit mode
     *
     * @param string $mode
     * @return object fluent interface
     */
    public function setEditMode($mode)
    {
        $this->editMode = $mode;
        return $this;
    }

    /**
     * Validate the expiration notification
     *
     * @param $value
     * @param array $context
     * @return boolean
     */
    public function validateExpirationNotification($value, array $context = array())
    {
        return (int) $value < $context['lifetime'];
    }
}