<?php
return [
    'plugins' => [
        'melis_demo_cms_setup' => [
            'forms' => [
                'melis_installer_demo_cms' => [
                    'attributes' => [
                        'name' => 'form_melis_installer_demo_cms',
                        'id' => 'id_form_melis_installer_demo_cms',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'sdom_scheme',
                                'type' => 'Zend\Form\Element\Select',
                                'options' => [
                                    'label' => 'tr_melis_installer_tool_site_scheme',
                                    'tooltip' => 'tr_melis_installer_tool_site_scheme tooltip',
                                    'value_options' => [
                                        'http' => 'http://',
                                        'https' => 'https://',
                                    ],
                                ],
                                'attributes' => [
                                    'id' => 'id_sdom_scheme',
                                    'value' => '',
                                    'required' => 'required',
                                    'text-required' => '*',
                                    'class' => 'form-control',

                                ],
                            ],
                        ],
                        [
                            'spec' => [
                                'name' => 'sdom_domain',
                                'type' => 'text',
                                'options' => [
                                    'label' => 'tr_melis_installer_tool_site_domain',
                                    'tooltip' => 'tr_melis_installer_tool_site_domain tooltip',
                                ],
                                'attributes' => [
                                    'id' => 'id_sdom_domain',
                                    'value' => '',
                                    'required' => 'required',
                                    'placeholder' => 'www.sample.com',
                                    'class' => 'form-control',
                                    'text-required' => '*',
                                ],
                            ],
                        ],
                    ], // end elements
                    'input_filter' => [
                        'sdom_scheme' => [
                            'name' => 'sdom_scheme',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'InArray',
                                    'options' => [
                                        'haystack' => ['http', 'https'],
                                        'messages' => [
                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'tr_melis_installer_tool_site_scheme_invalid_selection',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'empty brad',
                                        ],
                                    ],
                                ],
                            ],
                            'filters' => [
                            ],
                        ],
                        'sdom_domain' => [
                            'name' => 'sdom_domain',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max' => 50,
                                        'messages' => [
                                            \Zend\Validator\StringLength::TOO_LONG => 'tr_melis_installer_tool_site_domain_error_long',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_melis_installer_tool_site_domain_error_empty',
                                        ],
                                    ],
                                ],
                            ],
                            'filters' => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                    ], // end input_filter
                ], // end melis_installer_platform_id
            ],
        ],
    ],
];
