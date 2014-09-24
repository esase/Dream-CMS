<?php
namespace Application\Event;

use Zend\EventManager\EventManager as EventManager;

use User\Service\UserIdentity as UserIdentityService;
use User\Model\UserBase as UserBaseModel;

abstract class ApplicationAbstractEvent
{
    /**
     * Event manager
     * @var object
     */
    protected static $eventManager;

    /**
     * Fire an event
     *
     * @param string $event
     * @param integer|string $objectid
     * @param integer $userId
     * @param string $description
     * @param array $descriptionParams
     * @return object
     */
    public static function fireEvent($event, $objectid, $userId, $description, array $descriptionParams = [])
    {
        return self::getEventManager()->trigger($event, __METHOD__, [
            'object_id' => $objectid,
            'description' => $description,
            'description_params' => $descriptionParams,
            'user_id' => $userId
        ]);
    }

    /**
     * Get instance of event manager
     *
     * @return object
     */
    public static function getEventManager()
    {
        if (self::$eventManager) {
            return self::$eventManager;
        }

        self::$eventManager = new EventManager();
        return self::$eventManager;
    }

    /**
     * Get a user id
     *
     * @param boolean $isSystemEvent
     * @return integer
     */
    protected static function getUserId($isSystemEvent = false)
    {
        return $isSystemEvent
            ? UserBaseModel::DEFAULT_SYSTEM_ID
            : UserIdentityService::getCurrentUserIdentity()['user_id'];
    }
}