<?php

use PHPUnit\Framework\TestCase;
use NicheClassify\Fields\FieldManager;

class FieldManagerTest extends TestCase {
    public function test_get_instance_returns_singleton() {
        $a = FieldManager::get_instance();
        $b = FieldManager::get_instance();
        $this->assertSame($a, $b);
    }

    public function test_field_schema_is_array() {
        $schema = FieldManager::get_instance()->get_field_schema();
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('custom_price', $schema);
    }
}
