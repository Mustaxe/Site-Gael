<?php

namespace app\event;

use Symfony\Component\EventDispatcher\Event;

class SalvarEvent extends Event
{
    /**
     * @var sql
     */
    protected $sql;

    public function __construct($sql)
    {
        $this->setSql($sql);
    }
    
    /**
     * Get SQL
     *
     * @return string $sql
     */
    public function getSql()
    {
        return $this->sql;
    }
    
    /**
     * Set SQL
     *
     * @param string $sql
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }
}
