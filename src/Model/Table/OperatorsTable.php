<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class OperatorsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('operators');
        $this->addBehavior('Timestamp');
    }
}
