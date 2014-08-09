<?php

return [
    'router' => [
        'routes' => [
            'administration' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/administration'
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'page' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/[:languge[/:controller[/:action[/page/:page][/per-page/:per_page][/order-by/:order_by][/order-type/:order_type][/:category][/:slug]]]][:trailing_slash]',
                            'constraints' => [
                                'languge' => '[a-z]{2}',
                                'controller' => '[a-z][a-z0-9-]*',
                                'action' => '[a-z][a-z0-9-]*',
                                'page' => '[0-9]+',
                                'per_page' => '[0-9]+',
                                'order_by' => '[a-z][a-z0-9-]*',
                                'order_type' => 'asc|desc',
                                'category'    => '[0-9a-zA-Z-_]+',
                                'slug'     => '[0-9a-zA-Z-_]+',
                                'trailing_slash' => '/'
                            ],
                            'defaults' => [
                                'controller' => 'Page',
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true
                    ]
                ]
            ]
        ]
    ],
    'service_manager' => [
        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'getText',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain'  => 'default'
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'error' => 'Application\Controller\ErrorController',
            'modules-administration' => 'Application\Controller\ModuleAdministrationController',
            'settings-administration' => 'Application\Controller\SettingAdministrationController',
            'acl-administration' => 'Application\Controller\AclAdministrationController',
            'acl' => 'Application\Controller\AclController'
        ]
    ],
    'controller_plugins' => [
        'invokables' => [
            'applicationSetting' => 'Application\Controller\Plugin\ApplicationSetting'
        ]
    ],
    'view_manager' => [
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => []
    ]
];