<?php
namespace Page\Model;

use Application\Utility\ApplicationCache as CacheUtility;
use Application\Model\ApplicationAbstractSetting;
use Application\Utility\ApplicationErrorLogger;
use Page\Model\PageBase as PageBaseModel;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression as Expression;
use Exception;

class PageWidgetSetting extends ApplicationAbstractSetting
{
    /**
     * Get widget layouts
     *
     * @param boolean $process
     * @return array|object ResultSet
     */
    public function getWidgetLayouts($process = true)
    {
        $select = $this->select();
        $select->from('page_widget_layout')
            ->columns([
                'id',
                'name',
                'title'
            ])
            ->order('name');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        if ($process) {
            $layouts = [];
            foreach ($resultSet as $layout) {
                $layouts[$layout->id] = $layout->title;
            }
    
            return $layouts;
        }

        return $resultSet;
    }

    /**
     * List of settings
     * @var array
     */
    protected static $settings;

    /**
     * Get widget setting value
     *
     * @param integer $pageId
     * @param integer $connectionId
     * @param string $settingName
     * @param string $language
     * @return string|array|boolean
     */
    public function getWidgetSetting($pageId, $connectionId, $settingName, $language)
    {
        // get all settings
        if (!isset(self::$settings[$pageId][$language])) {
            self::$settings[$pageId][$language] = $this->getAllSettings($pageId, $language);
        }

        if (isset(self::$settings[$pageId][$language][$connectionId][$settingName])) {
            return self::$settings[$pageId][$language][$connectionId][$settingName];
        }

        return false;
    }

    /**
     * Get all settings
     *
     * @param integer $pageId
     * @param string $language
     * @return array
     */
    protected function getAllSettings($pageId, $language)
    {
        // get cache name
        $cacheName = CacheUtility::
                getCacheName(PageBaseModel::CACHE_WIDGETS_SETTINGS_BY_PAGE . $pageId . '_' . $language);

        // check data in cache
        if (null === ($settings = $this->staticCacheInstance->getItem($cacheName))) {
            // get default value
            $subQuery= $this->select();
            $subQuery->from(['i' => 'page_widget_setting_default_value'])
                ->columns([
                    'id'
                ])
                ->limit(1)
                ->order('i.language desc')
                ->where(['b.id' => new Expression('i.setting_id')])
                ->where
                    ->and->equalTo('i.language', $language)
                ->where
                    ->or->equalTo('b.id', new Expression('i.setting_id'))
                    ->and->isNull('i.language');

            $select = $this->select();
            $select->from(['a' => 'page_widget_connection'])
                ->columns([
                    'id'
                ])
                ->join(
                    ['b' => 'page_widget_setting'],
                    'a.widget_id = b.widget',
                    [
                        'name',
                        'type'
                    ]
                )
                ->join(
                    ['c' => 'page_widget_setting_value'],
                    'b.id = c.setting_id and a.id = c.widget_connection',
                    [
                        'value_id' => 'id',
                        'value'
                    ],
                    'left'
                )
                ->join(
                    ['d' => 'page_widget_setting_default_value'],
                    new Expression('d.id = (' .$this->getSqlStringForSqlObject($subQuery) . ')'),
                    [
                        'default_value' => 'value'
                    ],
                    'left'
                )
                ->where([
                    'page_id' => $pageId
                ]);

            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            // convert strings
            $settings = [];
            foreach ($resultSet as $setting) {
                if (!empty($setting['value_id']) || !empty($setting['default_value'])) {
                    $settings[$setting['id']][$setting['name']] =
                            $this->convertString($setting['type'], (!empty($setting['value_id']) ? $setting['value'] : $setting['default_value']));
                }
            }

            // save data in cache
            $this->staticCacheInstance->setItem($cacheName, $settings);
            $this->staticCacheInstance->setTags($cacheName, [PageBaseModel::CACHE_PAGES_DATA_TAG]);
        }

        return $settings;
    }

    /**
     * Get settings list
     *
     * @param integer $widgetConnectionId
     * @param integer $widgetId
     * @param string $language
     * @return array
     */
    public function getSettingsList($widgetConnectionId, $widgetId, $language)
    {
        // get default value
        $subQuery= $this->select();
        $subQuery->from(['d' => 'page_widget_setting_default_value'])
            ->columns([
                'id'
            ])
            ->limit(1)
            ->order('d.language desc')
            ->where(['a.id' => new Expression('d.setting_id')])
            ->where
                ->and->equalTo('d.language', $language)
            ->where
                ->or->equalTo('a.id', new Expression('d.setting_id'))
                ->and->isNull('d.language');

        $mainSelect = $this->select();
        $mainSelect->from(['a' => 'page_widget_setting'])
            ->columns([
                'id',
                'name',
                'label',
                'description',
                'description_helper',
                'type',
                'required',
                'values_provider',
                'check',
                'check_message'
            ])
            ->join(
                ['b' => 'page_widget_setting_value'],
                new Expression('b.setting_id = a.id and b.widget_connection = ?', [$widgetConnectionId]),
                [
                    'value_id' => 'id',
                    'value'
                ],
                'left'
            )            
            ->join(
                ['c' => 'page_widget_setting_default_value'],
                new Expression('c.id = (' .$this->getSqlStringForSqlObject($subQuery) . ')'),
                [
                    'default_value' => 'value'
                ],
                'left'
            )
            ->join(
                ['i' => 'page_widget_setting_category'],
                new Expression('a.category = i.id'),
                [
                    'category_name' => new Expression('i.name')
                ],
                'left'
            )
            ->order('a.order')
            ->where([
                'a.widget' => $widgetId
            ]);

        $statement = $this->prepareStatementForSqlObject($mainSelect);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        // processing settings list
        $settings = [];
        foreach ($resultSet as $setting) {
            // convert an array
            $settingValue = !$setting->value_id && $setting->default_value
                ? $this->convertString($setting->type, $setting->default_value)
                : $this->convertString($setting->type, $setting->value);

            $settings[$setting->id] = [
                'id' => $setting->id,
                'category' => $setting->category_name,
                'name' => $setting->name,
                'label' => $setting->label,
                'description' => $setting->description_helper
                    ? eval($setting->description_helper)
                    : $setting->description,
                'type' => $setting->type,
                'required' => $setting->required,
                'value' => $settingValue,
                'values_provider' => $setting->values_provider,
                'max_length' => self::SETTING_VALUE_MAX_LENGTH 
            ];

            // add extra validators
            if ($setting->check) {
                $settings[$setting->id]['validators'][] = [
                    'name' => 'callback',
                    'options' => [
                        'message' => $setting->check_message,
                        'callback' => function($value) use ($setting) {
                            return eval(str_replace('__value__', $value, $setting->check));
                        }
                    ]
                ];
            }
        }

        if ($settings) {
            // get list of predefined values
            $select = $this->select();
            $select->from('page_widget_setting_predefined_value')
                ->columns([
                    'setting_id',
                    'value'
                ])
                ->where->in('setting_id', array_keys($settings));

            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            // processing predefined list of values
            foreach ($resultSet as $values) {
                $settings[$values->setting_id]['values'][$values->value] = $values->value;
            }
        }

        return $settings ? $settings : [];
    }
}