<?php

namespace application\components\matview;


use extenders\logger\LoggerTrait;
use models\Company;

abstract class MatviewAbstract
{
    use LoggerTrait;

    protected $tableName = null;

    protected $sourceTable = '';

    /** @var Company */
    protected $company = null;

    public function __construct($company)
    {
        $this->company = $company;

        $mvName = $this->makeMvName();
        $this->setTableName($mvName);

        $this->createLogger('_' . $this->getTableName() . '.log', false);
    }

    protected function setTableName($name)
    {
        $this->tableName = $name;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    protected function createView($sql)
    {

        $this->drop();

        $sql = "
        create MATERIALIZED view $this->tableName as 
        
        $sql
        
        ";

        $res = \Yii::app()->db->createCommand($sql)->execute();

        $this->logger->info($sql);
        $this->logger->info("res: $res");

        return $res;
    }

    public function drop()
    {
        $sql = "drop MATERIALIZED VIEW if EXISTS $this->tableName";
        $res = \Yii::app()->db->createCommand($sql)->execute();

        $this->logger->info($sql);
        $this->logger->info("res: $res");
    }

    public function refresh()
    {
        $sql = "REFRESH MATERIALIZED VIEW $this->tableName";
        $res = \Yii::app()->db->createCommand($sql)->execute();

        $this->logger->info($sql);
        $this->logger->info("res: $res");

        return $res;
    }

    public function addIndexes(array $indexes = [])
    {
        //Example
//        $indexes = [
//            'CREATE INDEX id_company_idx_multi ON mat_view_ds_dashboards_process_time (id_company, action_date, id_param);',
//            'CREATE INDEX id_orgunit_idx ON mat_view_ds_dashboards_process_time (id_orgunit);',
//            'CREATE INDEX id_category_idx ON mat_view_ds_dashboards_process_time (id_category);',
//            'CREATE INDEX id_vac_priority_idx ON mat_view_ds_dashboards_process_time (id_vac_priority);',
//            'CREATE INDEX id_vac_orgunit_type_idx ON mat_view_ds_dashboards_process_time (id_vac_orgunit_type);',
//        ];

        foreach ($indexes as $index) {
            $res = \Yii::app()->db->createCommand($index)->execute();

            $this->logger->info($index);
            $this->logger->info("res: $res");
        }
    }

    protected function makeMvName()
    {
        return 'mv_' . $this->company->id . '_' . $this->sourceTable;
    }
}