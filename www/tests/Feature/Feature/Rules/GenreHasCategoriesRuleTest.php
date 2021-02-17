<?php

namespace Tests\Feature\Feature\Rules;

use App\Models\Category;
use App\Models\Genre;
use App\Rules\GenreHasCategoriesRule;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreHasCategoriesRuleTest extends TestCase
{
    use DatabaseMigrations;

    /** @var Collection */
    private $categories;
    private $genres;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = Category::factory(4)->create();
        $this->genres = Genre::factory(2)->create();

        $this->genres[0]->categories()->sync([
            $this->categories[0]->id,
            $this->categories[1]->id,
        ]);

        $this->genres[1]->categories()->sync([
            $this->categories[2]->id
        ]);
    }

    public function testPassesIsValid()
    {
        $rule = new GenreHasCategoriesRule([
            $this->categories[2]->id
        ]);
        $isValid = $rule->passes('', [$this->genres[1]->id]);
        $this->assertTrue($isValid);

        $rule = new GenreHasCategoriesRule([
            $this->categories[0]->id,
            $this->categories[2]->id,
        ]);
        $isValid = $rule->passes('', [$this->genres[0]->id, $this->genres[1]->id]);
        $this->assertTrue($isValid);

        $rule = new GenreHasCategoriesRule([
            $this->categories[0]->id,
            $this->categories[1]->id,
            $this->categories[2]->id,
        ]);
        $isValid = $rule->passes('', [$this->genres[0]->id, $this->genres[1]->id]);
        $this->assertTrue($isValid);
    }

    public function testPassesIsNotValid()
    {
        $rule = new GenreHasCategoriesRule([
            $this->categories[0]->id
        ]);
        $isValid = $rule->passes('', [$this->genres[0]->id, $this->genres[1]->id]);
        $this->assertFalse($isValid);

        $rule = new GenreHasCategoriesRule([
            $this->categories[3]->id
        ]);
        $isValid = $rule->passes('', [$this->genres[0]->id, $this->genres[1]->id]);
        $this->assertFalse($isValid);
    }
}
