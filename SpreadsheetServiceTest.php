<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;
use Exception;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_fails_when_file_does_not_exist(): void
    {
        Queue::fake();
        $this->assertDatabaseEmpty('products');

        $mock = Mockery::mock();
        $mock->shouldReceive('import')
        ->with('wrong_path_file.csv')
        ->andThrow(new \Exception('wrong_path_file.csv File not found'));

        app()->instance('importer', $mock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('wrong_path_file.csv File not found');

        (new SpreadsheetService())->processSpreadsheet('wrong_path_file.csv File not found');

        Queue::assertNothingPushed();
        $this->assertDatabaseEmpty('products');
    }

        public function test_skips_failed_validation(): void
        {
            Queue::fake();

            $this->assertDatabaseEmpty('products');
            $mock = Mockery::mock();
            $mock->shouldReceive('import')->andReturn([
                ['product_code' => '', 'quantity' => 5],
            ]);

            app()->instance('importer', $mock);
            Validator::shouldReceive('make')->once()->andReturnSelf();
            Validator::shouldReceive('fails')->andReturn(true);

            (new SpreadsheetService())->processSpreadsheet('valid_file.csv');


            Queue::assertNothingPushed();
            $this->assertDatabaseEmpty('products');
        }
        public function test_success_for_valid_rules_and_creates_product(): void
        {
            Queue::fake();

            $this->assertDatabaseEmpty('products');
            $mock = Mockery::mock();
            $mock->shouldReceive('import')->andReturn([
                ['product_code' => 'phone', 'quantity' => 5],
            ]);

            app()->instance('importer', $mock);
            Validator::shouldReceive('make')->once()->andReturnSelf();
            Product::shouldReceive('create')->once()->andReturn(new Product());

            (new SpreadsheetService())->processSpreadsheet('valid_file.csv');


            Queue::assertPushed();
            $this->assertDatabaseCount('products,1');
        }
}
