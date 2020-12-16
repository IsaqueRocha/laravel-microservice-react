<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test for listing categories and their json structure.
     *
     * @return void
     */
    public function testList()
    {
        Category::factory(10)->create();
        $categories = Category::all();
        $this->assertCount(10, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $categoryKeys
        );
    }

    /**
     * Test a new category creation.
     *
     * @return void
     */
    public function testCreate()
    {
        $category = Category::create(['name' => 'test1']);
        $category->refresh();

        $this->assertEquals(36, strlen($category->id));
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue((bool) $category->is_active);

        $category = Category::create(['name' => 'test1', 'description' => null]);
        $this->assertNull($category->description);

        $category = Category::create(['name' => 'test1', 'description' => 'test_description']);
        $this->assertEquals('test_description', $category->description);

        $category = Category::create(['name' => 'test1', 'is_active' => false]);
        $this->assertFalse($category->is_active);

        $category = Category::create(['name' => 'test1', 'is_active' => true]);
        $this->assertTrue($category->is_active);
    }

    /**
     * Test for updating a existing category.
     *
     * @return void
     */
    public function testUpdate()
    {
        /** @var Category $category */
        $category = Category::factory()->create(['description' => 'test_description'])->first();
        
        $data = [
            'name'          => 'test_name_updated',
            'description'   => 'test_description_updated',
            'is_active'     => true
        ];

        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }
/**
     * Test for deletion of a category.
     *
     * @return void
     */
    public function testDelete()
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        $category->delete();
        $this->assertNull(Category::find($category->id));
    }
}
