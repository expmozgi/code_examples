<?php
/**
 * Created by PhpStorm.
 * User: exp
 * Date: 30.08.16
 * Time: 10:46
 */

namespace application\components;

use models\Company;

/**
 * Class PageLock
 * @package application\components
 *
 * Класс для блокировки страницы
 *
 * Если один пользователь находится на странице, то у другого зайти не получится
 *
 * Работает через запись в таблице page_lock
 */
class PageLock
{
    const PAGE_PLAN_EXCEL = 1;
    const PAGE_PLAN_EXCEL_SUPERVISOR = 2;

    protected $user = null;
    protected $company = null;
    protected $targetPage = null;

    protected $additionalInfo = [];

    public function __construct(Company $company, \WebUser $user, $targetPage)
    {
        $this->user = $user;
        $this->company = $company;
        $this->targetPage = $targetPage;
    }

    /*
     * Проверка на возможность открытия страницы
     *
     * true если можно
     *
     * false если нельзя
     *
     */
    public function check()
    {
        $res = $this->find(true);

        if (!$res) {

            $res = $this->find(false);
            $this->createOrUpdate($res);
            return true;
        } else {

            if ($res->id_user == $this->user->id) {
                $this->createOrUpdate($res);
                return true;
            }

            $this->additionalInfo = $res;
            return false;
        }
    }

    public function test()
    {
        $res = $this->find(true);

        $res = $this->find(false);

        $this->createOrUpdate($res);

        var_dump($res);
    }

    public function find($timeLimit, $ignoreUser = true)
    {
        $criteria = new \CDbCriteria();

        $criteria->compare('id_company', $this->company->id);
        $criteria->compare('id_page', $this->targetPage);

        if ($timeLimit) {
            $criteria->addCondition("last_action_datetime >= now() - interval '3 minutes'");
        }

        if (!$ignoreUser) {
            $criteria->compare('id_user', $this->user->id);
        }

        $res = \models\PageLock::model()->find($criteria);

        return $res;
    }

    public function createOrUpdate($record)
    {
        if (!$record) {
            $record = new \models\PageLock();
            $record->id_company = $this->company->id;
            $record->id_user = $this->user->id;
            $record->id_page = $this->targetPage;
            $record->last_action_datetime = 'NOW()';
        } else {
            $record->id_user = $this->user->id;
            $record->last_action_datetime = 'NOW()';
        }

        $record->save();
    }

    public function update()
    {
        $res = $this->find(true, false);
        if ($res) {
            $this->createOrUpdate($res);
        }

    }

    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
}