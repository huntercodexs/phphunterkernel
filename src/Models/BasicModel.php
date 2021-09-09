<?php

namespace PhpHunter\Kernel\Models;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Builders\QueryBuilder;
use PhpHunter\Kernel\Controllers\InitServerController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;

/*Specific Builders*/
use PhpHunter\Kernel\Builders\MySqlQueryBuilder;
use PhpHunter\Kernel\Builders\MsSqlQueryBuilder;

abstract class BasicModel extends QueryBuilder
{
    //-----------------------------------------------------------------------------------------------------------
    // Basic Configurations to Security Model
    //-----------------------------------------------------------------------------------------------------------

    /**
     * @description Use to storage the result of the querys
    */
    //protected array $dataResult = [];

    /**
     * @description Use to mask fields in results that don't can is clear, note that
     * already is defined any values by default
    */
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
        'api_token',
        'remember_token'
    ];

    /**
     * @description Use to hidden fields in results that don't can is visible
    */
    protected array $dataHidden = [];

    /**
     * @description Use to filter results and show only fields that are in array
     */
    protected array $dataOnly = [];

    /**
     * @description Fill (persist) data that put into array dataFill
     */
    protected array $dataFill = [];

    /**
     * @description Alias to model
     */
    protected string $alias;

    /**
     * @description Name of Model
     */
    protected string $modelName;

    /**
     * @description Columns on model (in database)
     */
    //protected array $modelColumns = [];

    //-----------------------------------------------------------------------------------------------------------
    // Basic Fields Model
    //-----------------------------------------------------------------------------------------------------------

    /**
     * @description id
     * @var int|string $id
     */
    protected int|string $id;

    /**
     * @description name
     * @var string $name
     */
    protected string $name;

    /**
     * @description description
     * @var string $description
     */
    protected string $description;

    /**
     * @description create_at
     * @var string $create_at
     */
    protected string $create_at;

    /**
     * @description updated_at
     * @var string $updated_at
     */
    protected string $updated_at;

    /**
     * @description active
     * @var int $active
     */
    protected int $active;

    /**
     * @description password
     * @var string $password
     */
    protected string $password;

    /**
     * @description token
     * @var string $token
     */
    protected string $token;

    /**
     * @description api_token
     * @var string $api_token
     */
    protected string $api_token;

    //-----------------------------------------------------------------------------------------------------------
    // Basic Operations Model
    //-----------------------------------------------------------------------------------------------------------

    /**
     * @description Constructor Class
     */
    /*public function __construct()
    {
        $this->qb = new QueryBuilder();
    }*/

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
     * @return void
     */
    protected function firstly(): void
    {
        $array_handler = new ArrayHandler();

        /**
         * @description There is a priority in the manipulation of array data, as follows,
         * see that one case overwrites the previous one...
         *          1. If defined dataMask
         *          2. If defined dataHidden overwrite dataMask
         *              3. If defined dataOnly overwrite dataHidden
         */

        /*First*/
        if(isset($this->dataMask) && count($this->dataMask) > 0) {
            $array_handler->setArrayData($this->dataResult);
            $array_handler->setArraySearch($this->dataMask);
            $this->dataResult = $array_handler->arrayMasterHandler('mask');
        }

        /*Second*/
        if(isset($this->dataHidden) && count($this->dataHidden) > 0) {
            $array_handler->setArrayData($this->dataResult);
            $array_handler->setArraySearch($this->dataHidden);
            $this->dataResult = $array_handler->arrayMasterHandler('hidden');
        }

        /*Third*/
        if(isset($this->dataOnly) && count($this->dataOnly) > 0) {
            $array_handler->setArrayData($this->dataResult);
            $array_handler->setArraySearch($this->dataOnly);
            $this->dataResult = $array_handler->arrayMasterHandler('only');
        }
    }

    /**
     * @description Check Use Class
     * @param string $only_fields #Mandatory
     * @param string $method_class #Mandatory
     * @return void
     */
    private static function checkUseClass(string $model, string $method_class): void
    {
        if (!method_exists($model, $method_class)) {
            HunterCatcherController::hunterException(
                "Model Exception: Method {$method_class} not foun in {$model}",
                true
            );
        }

        $model = explode("\\", $model);
        $model_name = array_pop($model);
        $model = implode('\\', $model);

        $valid_models =
            [
                "PhpHunter\Kernel\Models",
                "PhpHunter\Framework\Models",
                "PhpHunter\Application\Models",
                "Models"
            ];

        if (!in_array($model, $valid_models) && stristr($model_name, "model") == false) {
            HunterCatcherController::hunterException(
                'Model Exception: The model class or the called class is not valid for this operation, 
                for more details please see the PhpHunter documentation',
                true
            );
        }
    }

    /**
     * @description Check Use Id
     * @param int|string $id #Mandatory
     * @return void
     */
    private static function checkUseId(int $id): void
    {
        if (!$id || $id == "") {
            HunterCatcherController::hunterException(
                'Model Exception: missing Id to find an uniq data',
                true
            );
        }
    }

    /**
     * @description Check Body Params
     * @param array $params #Mandatory
     * @return void
     */
    private static function checkBodyParams(array $params): void
    {
        if (count($params) == 0) {
            HunterCatcherController::hunterException(
                'Model Exception: missing body params',
                true
            );
        }
    }

    /**
     * @description Set Model Name
     * @param string $db_type #Mandatory
     * @return void
     */
    protected function setBasicModel(string $db_type): void
    {
        $this->dbType = $db_type;
        $this->modelName = self::getModelName(get_called_class());
        $this->alias = self::firstLetterModelName($this->modelName);
    }

    /**
     * @description First Letter Model Name
     * @param string $model_name #Mandatory
     * @return string
     */
    private static function firstLetterModelName(string $model_name): string
    {
        return substr($model_name, 0, 1);
    }

    /**
     * @description Get Model Name
     * @param array|string $data #Mandatory
     * @return string
     */
    private static function getModelName(array|string $data): string
    {
        if (!preg_match('/Model$/', $data)) {
            HunterCatcherController::hunterException(
                "Query Builder Exception: Invalid Model Name {$data}",
                true
            );
        }

        $model = explode("\\", $data);
        $model_name = str_replace("Model", "", array_pop($model));
        preg_match_all('/([A-Z][a-z]+)/', $model_name, $m, PREG_OFFSET_CAPTURE);
        $table_name = [];

        for ($i = 0; $i < count($m); $i++) {
            $table_name[] = $m[$i][$i][0];
        }

        return (strtolower(implode("_", $table_name)));
    }

    //-----------------------------------------------------------------------------------------------------------
    // Basic Query Operations Model
    //-----------------------------------------------------------------------------------------------------------

    /**
     * @description New [CREATE:HTTP/POST]
     * @param array $body_params #Mandatoy
     * @example [POST] http://local.phphunter.dockerized/api/user
     * @return bool
     */
    protected function new(array $body_params): bool
    {
        return true;
    }

    /**
     * @description Find [READ:HTTP/GET]
     * @param int|string $id #Mandatory
     * @param array $only_fields #Optional
     * @example [GET] http://local.phphunter.dockerized/api/user/444444
     * @return array
     */
    protected function findId(int|string $id, array $only_fields = []): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Find All [READ:HTTP/GET]
     * @param array $only_fields #Optional
     * @param array $criteria #Optional
     * @example [GET] http://local.phphunter.dockerized/api/user
     * @return array
     */
    protected function findAll(array $only_fields = [], array $criteria = []): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Overwrite [UPDATE:HTTP/PUT]
     * @param int|string $id #Mandatory
     * @param array $body_params #Mandatoy
     * @example [PUT] http://local.phphunter.dockerized/api/user/333333
     * @return bool
     */
    protected function overwrite(int|string $id, array $body_params): bool
    {
        return true;
    }

    /**
     * @description Remove [DELETE:HTTP/DELETE]
     * @param int|string $id #Mandatory
     * @example [DELETE] http://local.phphunter.dockerized/api/user/222222
     * @return bool
     */
    protected function remove(int|string $id): bool
    {
        return true;
    }

    /**
     * @description Patch #BasicModel
     * @param int|string $id #Mandatory
     * @param array $body_params #Mandatory
     * @example [PATCH] http://local.phphunter.dockerized/api/user/111111
     * @return bool
     */
    protected function patch(int|string $id, array $body_params): bool
    {
        return true;
    }

    /**
     * @description Generate [CREATE:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function generate(array $fields): bool
    {
        return true;
    }

    /**
     * @description Lock [LOCK:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function lock(array $fields): bool
    {
        return true;
    }

    /**
     * @description UnLock [UNLOCK:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function unLock(array $fields): bool
    {
        return true;
    }

    //-----------------------------------------------------------------------------------------------------------
    // Static Methods Model
    //-----------------------------------------------------------------------------------------------------------

    /**
     * @description Add
     * @param array $body_params #Mandatoy
     * @return bool
     */
    public static function add(array $body_params): bool
    {
        $model = get_called_class();
        self::checkUseClass($model, "new");
        self::checkBodyParams($body_params);
        $model_instance = new $model();
        return $model_instance->new($body_params);
    }

    /**
     * @description Find
     * @param int|string $id #Mandatory
     * @param array $only_fields #Optional
     * @return array
     */
    public static function find(int|string $id, array $only_fields = []): array
    {
        $model = get_called_class();
        self::checkUseClass($model, "findId");
        self::checkUseId($id);
        $model_instance = new $model();
        return $model_instance->findId($id, $only_fields);
    }

    /**
     * @description All
     * @param array $only_fields #Optional
     * @param array $criteria #Optional
     * @return array
     */
    public static function all(array $only_fields = [], array $criteria = []): array
    {
        $model = get_called_class();
        self::checkUseClass($model, "findAll");
        $model_instance = new $model();
        return $model_instance->findAll($only_fields, $criteria);
    }

    /**
     * @description Up
     * @param int|string $id #Optional
     * @param array $body_params #Optional
     * @return bool
     */
    public static function up(int|string $id, array $body_params): bool
    {
        $model = get_called_class();
        self::checkUseClass($model, "overwrite");
        self::checkUseId($id);
        self::checkBodyParams($body_params);
        $model_instance = new $model();
        return $model_instance->overwrite($id, $body_params);
    }

    /**
     * @description Down
     * @param int|string $id #Mandatory
     * @return bool
     */
    public static function down(int|string $id): bool
    {
        $model = get_called_class();
        self::checkUseClass($model, "remove");
        self::checkUseId($id);
        $model_instance = new $model();
        return $model_instance->remove($id);
    }

    /**
     * @description Fix
     * @param int|string $id #Mandatory
     * @param array $body_params #Mandatory
     * @return bool
     */
    public static function fix(int|string $id, array $body_params): bool
    {
        $model = get_called_class();
        self::checkUseClass($model, "patch");
        self::checkUseId($id);
        self::checkBodyParams($body_params);
        $model_instance = new $model();
        return $model_instance->patch($id, $body_params);
    }
}
