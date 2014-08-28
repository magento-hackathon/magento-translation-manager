<?php

namespace Application\Resource;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class Base extends AbstractTableGateway {

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
}