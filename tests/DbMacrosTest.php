<?php

namespace AbdelilahEzzouini\DbMacros\Tests;

use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class DbMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['AbdelilahEzzouini\DbMacros\DbMacrosServiceProvider'];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /** @test */
    public function it_can_execute_simple_select()
    {
        $result = DB::binding('SELECT 1+1 as total');

        $this->assertIsArray($result);
        $this->assertEquals(2, $result[0]->total);
    }

    /** @test */
    public function it_can_execute_simple_select_with_params()
    {
        $result = DB::binding('SELECT :num1+:num2 as total', [
            'num1' => 5,
            'num2' => 3
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(8, $result[0]->total);
    }

    /** @test */
    public function it_can_handle_array_binding()
    {
        $result = DB::binding("SELECT COUNT(*) as count FROM (SELECT 1 as id UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t WHERE t.id IN ([:ids])", [
            'ids' => [1, 3, 4]
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result[0]->count);
    }

    /** @test */
    public function it_can_handle_single_id_binding()
    {
        $result = DB::binding('SELECT 1 as found FROM (SELECT 1 as id) t WHERE t.id = :id', [
            'id' => 1
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result[0]->found);
    }

    /** @test */
    public function it_can_handle_different_statement_types()
    {
        // Test INSERT
        $insertResult = DB::binding('CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)', [], 'affectingStatement');
        $this->assertEquals(0, $insertResult);

        $insertResult = DB::binding('INSERT INTO test_table (name) VALUES (:name)', [
            'name' => 'test'
        ], 'affectingStatement');
        $this->assertEquals(1, $insertResult);

        // Test UPDATE
        $updateResult = DB::binding('UPDATE test_table SET name = :new_name WHERE name = :old_name', [
            'new_name' => 'updated',
            'old_name' => 'test'
        ], 'affectingStatement');
        $this->assertEquals(1, $updateResult);

        // Test DELETE
        $deleteResult = DB::binding('DELETE FROM test_table WHERE name = :name', [
            'name' => 'updated'
        ], 'affectingStatement');
        $this->assertEquals(1, $deleteResult);
    }
}
