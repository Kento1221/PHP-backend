<?php

namespace App;

class Config {

    /**
     * path to the sqlite file
     */
    public const DATABASE_FILE_PATH = 'D://Dev/php/Php-backend/database/db.sqlite';
    public const DATABASE_RECORD_TABLE_FIELDS = array('customer_id', 'call_date', 'call_duration', 'number_called', 'customer_ip');
    public const IP_STACK_ACCESS_KEY = '13966c211380b04c5a16defd306bc65f';

}