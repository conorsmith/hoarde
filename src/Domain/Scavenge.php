<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\Uuid;
use RandomLib\Factory;
use RandomLib\Generator;

final class Scavenge
{
    /** @var iterable */
    private $rollTable;

    /** @var Generator */
    private $generator;

    public function __construct(iterable $rollTable)
    {
        $this->rollTable = $rollTable;
        $this->generator = (new Factory)->getLowStrengthGenerator();
    }

    public function roll(): ScavengingHaul
    {
        $scavengedItems = [];

        $d1000 = $this->generator->generateInt(1, 1000);

        foreach ($this->rollTable as $rollTableEntry) {
            if (in_array($d1000, $rollTableEntry['rolls'])) {
                if (!array_key_exists('quantity', $rollTableEntry)) {
                    $quantity = 1;
                } else {
                    $quantity = $this->generator->generateInt(
                        $rollTableEntry['quantity'][0],
                        $rollTableEntry['quantity'][1]
                    );
                }

                $scavengedItems[] = $rollTableEntry['item']->createItemWithQuantity($quantity);
            }
        }

        return new ScavengingHaul(Uuid::uuid4(), $scavengedItems);
    }
}
