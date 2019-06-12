<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

use ConorSmith\Hoarde\Domain\Entity;
use Ramsey\Uuid\UuidInterface;

final class Result
{
    public static function succeeded(): self
    {
        return new self(true, null);
    }

    public static function failed(string $message): self
    {
        return new self(false, $message);
    }

    public static function gameNotFound(UuidInterface $gameId): self
    {
        return self::failed("Game {$gameId} was not found.");
    }

    public static function entityNotFound(UuidInterface $entityId, UuidInterface $gameId): self
    {
        return self::failed("Entity {$entityId} was not found in game {$gameId}.");
    }

    public static function actorExpired(Entity $actor): self
    {
        return Result::failed("{$actor->getLabel()} has expired.");
    }

    public static function entityHasNoInventory(Entity $entity): self
    {
        return Result::failed("{$entity->getLabel()} has no inventory.");
    }

    /** @var bool */
    private $isSuccessful;

    /** @var ?string */
    private $message;

    private function __construct(bool $isSuccessful, ?string $message)
    {
        $this->isSuccessful = $isSuccessful;
        $this->message = $message;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
