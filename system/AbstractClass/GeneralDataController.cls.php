<?php
abstract class GeneralDataController extends GeneralController {

    protected $model;

    public function __construct($modelName) {
        $this->model = new $modelName();
    }

    public function action($action, $id = '', $args = '') {
        switch ($action) {
            case 'update':
                $this->model->ID = $id;
            case 'add':
                if (is_array($args)) {
                    foreach ($args as $key => $value)
                        $this->model->$key = $value;
                    if (!$this->model->insert())
                        throw new Exception("$action failed");
                }
                break;
            case 'delete':
                if (!$this->model->delete())
                    throw new Exception('delete failed');
                break;
			default:
				throw new Exception('action type error');
        }
    }
}
