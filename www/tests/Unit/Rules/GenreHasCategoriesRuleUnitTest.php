<?php

namespace Tests\Unit\Rules;

use App\Rules\GenreHasCategoriesRule;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GenreHasCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIDField()
    {
        $rule = new GenreHasCategoriesRule([1, 1, 2, 2]);

        $reflectionClass = new ReflectionClass(GenreHasCategoriesRule::class);
        $reflectionClassProperty = $reflectionClass->getProperty('categoriesID');
        $reflectionClassProperty->setAccessible(true); //NOSONAR

        $categoriesID = $reflectionClassProperty->getValue($rule); //NOSONAR

        $this->assertEqualsCanonicalizing([1, 2], $categoriesID);
    }

    public function testGenresIDField()
    {
        /** @var $rule */
        $rule = $this->createRuleMock([]);
        $rule->shouldReceive('getRows')->andReturn(null);
        $rule->passes('', [1, 1, 2, 2]);

        $reflectionClass = new ReflectionClass(GenreHasCategoriesRule::class);
        $reflectionClassProperty = $reflectionClass->getProperty('genresID');
        $reflectionClassProperty->setAccessible(true); //NOSONAR

        $genresID = $reflectionClassProperty->getValue($rule); //NOSONAR

        $this->assertEqualsCanonicalizing([1, 2], $genresID);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresArrayIsEmpty()
    {
        /** @var $rule */
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        /** @var $rule */
        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {
        /** @var $rule */
        $rule = $this->createRuleMock([]);
        $rule->shouldReceive('getRows')->andReturn(collect());
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenWhenHasCategoriesWithoutGenres()
    {
        /** @var $rule */
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getRows')->andReturn(collect(['category_id' => 1]));
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        /** @var $rule */
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getRows')->andReturn(collect([
            ['category_id' => 1],
            ['category_id' => 2],
        ]));
        $this->assertTrue($rule->passes('', [1]));

        /** @var $rule */
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getRows')->andReturn(collect([
            ['category_id' => 1],
            ['category_id' => 2],
            ['category_id' => 1],
            ['category_id' => 2],
        ]));
        $this->assertTrue($rule->passes('', [1]));
    }

    protected function createRuleMock(array $categoriesID): MockInterface
    {
        return Mockery::mock(GenreHasCategoriesRule::class, [$categoriesID])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
