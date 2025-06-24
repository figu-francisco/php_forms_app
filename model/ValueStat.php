<?php
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
class ValueStat extends Model
{


    public function __construct(
        private ?string $value = null, 
        private ?int $count = null, 
        private ?int $instance_count = null
        ) {}

    public function get_value(): string|null{
        return $this->value;
    }

    public function get_count(): int|null{
        return $this->count;
    }

    public function get_instance_count(): int|null{
        return $this->instance_count;
    }

}