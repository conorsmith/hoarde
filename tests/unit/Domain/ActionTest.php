<?php
declare(strict_types=1);

namespace ConorSmith\HoardeTest\Unit\Domain;

use DomainException;
use PHPUnit\Framework\TestCase;

final class ActionTest extends TestCase
{
    /**
     * @test
     */
    function has_an_id()
    {
        $id = UuidFactory::create();

        $action = (new ActionBuilder)
            ->withId($id)
            ->build();

        $this->assertThat(
            $action->getId(),
            $this->identicalTo($id)
        );
    }

    /**
     * @test
     */
    function has_a_label()
    {
        $label = "the label";

        $action = (new ActionBuilder)
            ->withLabel($label)
            ->build();

        $this->assertThat(
            $action->getLabel(),
            $this->identicalTo($label)
        );
    }

    /**
     * @test
     */
    function has_an_icon()
    {
        $icon = "the icon";

        $action = (new ActionBuilder)
            ->withIcon($icon)
            ->build();

        $this->assertThat(
            $action->getIcon(),
            $this->identicalTo($icon)
        );
    }

    /**
     * @test
     */
    function has_performing_variety_ids()
    {
        $performingVarietyIds = [
            UuidFactory::create(),
        ];

        $action = (new ActionBuilder)
            ->withPerformingVarietyIds($performingVarietyIds)
            ->build();

        $this->assertThat(
            $action->getPerformingVarietyIds(),
            $this->identicalTo($performingVarietyIds)
        );
    }

    /**
     * @test
     */
    function performing_variety_ids_must_be_uuids()
    {
        $this->expectException(DomainException::class);

        (new ActionBuilder)
            ->withPerformingVarietyIds([
                UuidFactory::create(),
                "this value is not a uuid",
                UuidFactory::create(),
            ])
            ->build();
    }

    /**
     * @test
     */
    function can_be_performed_by_one_of_its_performing_varieties()
    {
        $performingVarietyId = UuidFactory::generate();

        $action = (new ActionBuilder)
            ->withPerformingVarietyIds([
                UuidFactory::generate(),
                $performingVarietyId,
                UuidFactory::generate(),
            ])
            ->build();

        $this->assertThat(
            $action->canBePerformedBy($performingVarietyId),
            $this->isTrue()
        );
    }

    /**
     * @test
     */
    function can_not_be_performed_by_any_other_variety()
    {
        $anotherVarietyId = UuidFactory::generate();

        $action = (new ActionBuilder)
            ->withPerformingVarietyIds([
                UuidFactory::generate(),
                UuidFactory::generate(),
                UuidFactory::generate(),
            ])
            ->build();

        $this->assertThat(
            $action->canBePerformedBy($anotherVarietyId),
            $this->isFalse()
        );
    }
}
