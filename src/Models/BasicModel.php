<?php

namespace PhpHunter\Kernel\Models;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Controllers\ConnectionController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;
use PhpHunter\Kernel\Controllers\InitServerController;

abstract class BasicModel extends ConnectionController
{
    protected array $result = [];
    protected array $dataOnly = [];
    protected array $dataHidden = ['password', 'token'];
    protected array $dataMask = [
        'password',
        'key',
        'secret_key',
        'secret_manager',
        'api_key',
        'pass',
        'passwd',
        'secret',
        'token',
        'remember_token'
    ];

    /**
     * @description Data Hidden Replace
     * @return void
     */
    protected function dataMaskReplace(array $source): void
    {
        $this->dataMask = $source;
    }

    /**
     * @description Data Hidden Append
     * @return void
     */
    protected function dataMaskAdd(array $source): void
    {
        array_merge($this->dataMask, $source);
    }

    /**
     * @description Data Hidden
     * @return array
     */
    protected function dataMask(): array
    {
        return $this->dataMask;
    }

    /**
     * @description Data Hidden
     * @param string $apply #Mandatory
     * @return void
     */
    protected function firstly(string $apply): void
    {
        $array_handler = new ArrayHandler();
        $array_handler->setArrayData($this->result);
        $array_handler->setArraySearch($this->dataMask);

        if ($apply == 'mask') {
            $this->result = $array_handler->arrayValueMask();
        } elseif($apply == 'hidden') {
            //TODO: Code here...
        }
    }

    /**
     * @description Insert [CREATE:HTTP/POST]
     * @return object
     */
    protected function insert()
    {
        return $this;
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @return array
     */
    protected function select()
    {
        //Faker/Test
        /*$this->result = [
            "datafirst" => "data-test",
            [
                "id" => 1,
                "name" => "Mathias Kajima",
                "password" => "1234567890",
                "age" => 30,
                "phone" => "1298822113",
                "midias" => [
                    "facebook" => "profile.facebook.com",
                    "whatsapp" => "90211290909",
                    "youtube" => "youtube.com/profile/123456"
                ],
                "address" => [
                    "rua" => "Rua teste",
                    "numero" => 123,
                    "bairro" => "JARDIM TESTE"
                ]
            ],
            [
                "id" => 2,
                "name" => "Joeh Biden",
                "age" => 40,
                "password" => "8884567890",
                "phone" => "1298822901"
            ],
            "password" => "0x9AOI8AEYFHIUGSHDGJKDGHDSJKFHDJK",
            "others" => [
                "password" => "0x1AOI8AEYFHIUGSHDGJKDGHDSJKFHDJK"
            ],
            "data" => [
                "user" => [
                    "restrict" => [
                        "password" => "123FSFOIDJFDKLGSGSH",
                        "name" => "Hugo Boss"
                    ]
                ]
            ],
            "password2" => [
                "value" => "0x4AOI8AEYFHIUGSHDGJKDGHDSJKFHDJK"
            ],

        ];*/

        $this->result = [
            "DATA" => [
                "id" => 123456,
                "description" => "This is only a test",
                "list" => [
                    "object1" => "cama",
                    "object2" => "sofa",
                    "password" => "123teste"
                ]
            ],
            "SUB-DATA" => "WEBDEV4ALL",
            "SECRET0" => [
                "password" => "123128312U2H12821YH822U",
                "SECRET1" => [
                    "username" => "New Username Test",
                    "password" => "PASSWORD-1111111111111111111",
                    "SECRET2" => [
                        "password" => "PASSWORD-2222222222222222",
                        "SECRET3" => [
                            "password" => "PASSWORD-333333333",
                            "SECRET4" => [
                                "password" => "PASSWORD-444444444444444",
                                "SECRET5" => [
                                    "password" => "PASSWORD-55555",
                                    "SECRET6" => [
                                        "password" => "PASSWORD-666",
                                        "SECRET7" => [
                                            "password" => "PASSWORD-777777777777777777",
                                            "SECRET8" => [
                                                "password" => "PASSWORD-8888888",
                                                "SECRET9" => [
                                                    "password" => "PASSWORD-9999",
                                                    "SECRET10" => [
                                                        "password" => "PASSWORD-1101010101010",
                                                        "SECRET11" => [
                                                            "password" => "PASSWORD-111111"
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "POS-SECRET" => [
                "password" => "1234567890XXXXXXXXXXXXXXXXXXXXX"
            ]
        ];

        if(isset($this->dataMask) && count($this->dataMask) > 0) {
            $this->firstly('mask');
        }

        return $this->result;
    }

    /**
     * @description Update [UPDATE:HTTP/PUT]
     * @return object
     */
    protected function update()
    {
        return $this;
    }

    /**
     * @description Delete [DELETE:HTTP/DELETE]
     * @return object
     */
    protected function delete()
    {
        return $this;
    }

    /**
     * @description Correct [PATCH:HTTP/PATCH]
     * @return object
     */
    protected function correct()
    {
        return $this;
    }

}
