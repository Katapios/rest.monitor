<?php

namespace Rest\Monitor;

use Bitrix\Main\Entity;

class LogTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_rest_api_log';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\DatetimeField('TIMESTAMP_X'),
            new Entity\StringField('METHOD'),
            new Entity\IntegerField('USER_ID'),
            new Entity\StringField('IP'),
            new Entity\FloatField('DURATION'),
            new Entity\TextField('RESULT'),
            new Entity\BooleanField('SENT', ['values' => ['N', 'Y']])
        ];
    }
}
