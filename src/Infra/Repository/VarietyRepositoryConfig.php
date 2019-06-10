<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\ActionRepository;
use ConorSmith\Hoarde\Domain\ResourceContent;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\Variety;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class VarietyRepositoryConfig implements VarietyRepository
{
    public const HUMAN = "fde2146a-c29d-4262-b96f-ec7b696eccad";
    public const WELL = "59aabe00-8418-4127-9ceb-12beca840854";
    public const WATER_BOTTLE = "2f555296-ff9f-4205-a4f7-d181e4455f9d";
    public const COKE_ZERO = "08db1181-2bc9-4408-b378-5270e8dbee4b";
    public const CHERRY_COKE_ZERO = "450349d4-fe21-4da0-8f78-99c684b05b45";
    public const VANILLA_COKE_ZERO = "813980ad-7604-4713-909c-b2701420de1b";
    public const PEACH_COKE_ZERO = "e12981d2-5873-454a-b297-895f42e66bd5";
    public const GINGER_COKE_ZERO = "5de1c51c-2747-426d-a3b0-c854107c7132";
    public const TINNED_STEW = "9c2bb508-c40f-491b-a4ca-fc811087a158";
    public const TINNED_DREW = "cf057538-d3f0-4657-8a4c-f911bc113ad7";
    public const TINNED_SOUP = "fb793da2-cff9-4e88-9f9c-84278c6662ca";
    public const PRINGLE = "275d6f62-16ff-4f5f-8ac6-149ec4cde1e2";
    public const WOODEN_CRATE = "59593b72-3845-491e-9721-4452a337019b";
    public const SHOVEL = "75d861d5-b6b7-4cd2-ad4a-a56db4db1fcf";
    public const HAMMER = "328a1e58-ab91-4b35-87e9-527f4d7d130e";
    public const HAND_SAW = "d38a3e6a-d508-4bf0-ae79-34d2a425dc47";
    public const BUCKET = "6722e875-6d19-404c-a1bd-e49fb3470cd7";
    public const ROPE = "0646f8e6-32b9-476f-86f5-834dc7160d95";
    public const DRIL_FIGURINE_1 = "32b0c544-d393-4256-bcde-125298d59b63";
    public const DRIL_FIGURINE_2 = "4747e2e7-8b99-497c-8944-24667fca681b";
    public const DRIL_FIGURINE_3 = "7674f722-ddce-4089-999c-0d47382be6d8";
    public const DRIL_FIGURINE_4 = "97d9cc1f-35c9-4ad9-8e56-9349c696693e";
    public const DRIL_FIGURINE_5 = "eb6059ab-9ace-4259-a763-96272e7bd0c6";
    public const DRIL_FIGURINE_6 = "270bb4a5-1eaf-4885-bb42-3cfe004caaa2";
    public const DRIL_FIGURINE_7 = "363da664-2f05-4a43-842b-1dcf9ad02745";
    public const DRIL_FIGURINE_8 = "26619339-8298-499e-abf8-1cbdf8b151a6";
    public const DRIL_FIGURINE_9 = "4acf8e15-fdba-4dbd-8206-812bfc5a0174";
    public const DRIL_FIGURINE_10 = "21d18270-471b-45a3-91f8-ef002f76f6f0";
    public const DRIL_FIGURINE_11 = "ce2ade49-a367-4cdd-b815-5505f593a6d9";
    public const DRIL_FIGURINE_12 = "22419ffb-2faa-409b-ae8d-081f4969e0e2";
    public const DRIL_FIGURINE_13 = "b2949529-b2b4-45db-9dc6-cece114418ba";
    public const TIMBER = "e2f811da-d77b-47b1-a167-1df513c2b04b";
    public const NAIL = "2808d535-38cd-4254-952f-c79aa2e66cd0";

    private const DESCRIPTION_TEMPLATE_DRIL_FIGURINE = "A commemorative figurine of Twitter user @dril. Part of a set"
        . " of 13. The base is engraved with a tweet:\n\n";

    private const VARIETIES = [
        self::HUMAN => [
            'label'       => "Human",
            'resources'   => [],
            'weight'      => 75000,
            'icon'        => "user",
            'description' => "Homo sapiens, the only extant members of the subtribe Hominina.",
        ],
        self::WELL => [
            'label'       => "Well",
            'resources'   => [],
            'weight'      => 0,
            'icon'        => "tint",
            'description' => "A 10 metre deep hole reaching the water table.",
        ],
        self::WATER_BOTTLE => [
            'label'       => "Water Bottle",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A bottle of water that is probably still drinkable.",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::COKE_ZERO => [
            'label'       => "Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola!",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::CHERRY_COKE_ZERO => [
            'label'       => "Cherry Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with sweet cherry!",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::VANILLA_COKE_ZERO => [
            'label'       => "Vanilla Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with creamy vanilla!",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::PEACH_COKE_ZERO => [
            'label'       => "Peach Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with overpowering peach!",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::GINGER_COKE_ZERO => [
            'label'       => "Ginger Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, a real spicy boy!",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::TINNED_STEW => [
            'label'       => "Tinned Stew",
            'resources'   => [
                ResourceRepositoryConfig::FOOD => 600,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The faded label indicates it to be some variety of stew.",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::TINNED_DREW => [
            'label'       => "Tinned Drew",
            'resources'   => [
                ResourceRepositoryConfig::FOOD => 600,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The label is thoroughly worn, but it indicates that the can contains... drew?",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::TINNED_SOUP => [
            'label'       => "Tinned Soup",
            'resources'   => [
                ResourceRepositoryConfig::FOOD  => 600,
                ResourceRepositoryConfig::WATER => 500,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The faded label indicates it to be some variety of soup.",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::PRINGLE => [
            'label'       => "Pringle",
            'resources'   => [
                ResourceRepositoryConfig::PRINGLES => 1,
            ],
            'weight'      => 1,
            'icon'        => "moon",
            'description' => "A delicious stackable potato crisp, but be warned: once you pop, you cannot stop.",
            'actions'     => [
                ActionRepositoryConfig::CONSUME,
            ],
        ],
        self::WOODEN_CRATE => [
            'label'       => "Wooden Crate",
            'resources'   => [],
            'weight'      => 4000,
            'icon'        => "box",
            'description' => "A sturdy crate crafted from wood in which items could be protected from the elements.",
            'actions'     => [
                ActionRepositoryConfig::PLACE,
            ],
        ],
        self::SHOVEL => [
            'label'       => "Shovel",
            'resources'   => [],
            'weight'      => 3000,
            'icon'        => "tools",
            'description' => "A tool for digging, lifting, and moving bulk materials.",
            'actions'     => [
                ActionRepositoryConfig::DIG,
            ],
        ],
        self::ROPE => [
            'label'       => "Rope",
            'resources'   => [],
            'weight'      => 3770,
            'icon'        => "tools",
            'description' => "A coil of nylon rope, 10 metres long and 24 mm in diameter.",
        ],
        self::BUCKET => [
            'label'       => "Bucket",
            'resources'   => [],
            'weight'      => 600,
            'icon'        => "fill",
            'description' => "A galvanised steel bucket with a capacity of 10 litres.",
        ],
        self::DRIL_FIGURINE_1 => [
            'label'       => "Dril Figurine #1",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "\"im not owned!  im not owned!!\", i continue to insist as i slowly shrink and transform into a"
                . " corn cob",
        ],
        self::DRIL_FIGURINE_2 => [
            'label'       => "Dril Figurine #2",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "Food $200\n"
                . "Data $150\n"
                . "Rent $800\n"
                . "Candles $3,600\n"
                . "Utility $150\n"
                . "someone who is good at the economy please help me budget this. my family is dying",
        ],
        self::DRIL_FIGURINE_3 => [
            'label'       => "Dril Figurine #3",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "drunk driving may kill a lot of people, but it also helps a lot of people get to work on time, so,"
                . " it;s impossible to say if its bad or not,",
        ],
        self::DRIL_FIGURINE_4 => [
            'label'       => "Dril Figurine #4",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "the wise man bowed his head solemnly and spoke: \"theres actually zero difference between good & bad"
                . " things. you imbecile. you fucking moron\"",
        ],
        self::DRIL_FIGURINE_5 => [
            'label'       => "Dril Figurine #5",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "it is with a heavy heart that i must announce that the celebs are at it again",
        ],
        self::DRIL_FIGURINE_6 => [
            'label'       => "Dril Figurine #6",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "user named \" beavis_sinatra \" has been terrorizing me since 2004, by sending me pictures of cups"
                . " that are too close to the edge of the table",
        ],
        self::DRIL_FIGURINE_7 => [
            'label'       => "Dril Figurine #7",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "if your grave doesnt say \"rest in peace\" on it you are automatically drafted into the skeleton war",
        ],
        self::DRIL_FIGURINE_8 => [
            'label'       => "Dril Figurine #8",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "so long suckers! i rev up my motorcylce and create a huge cloud of smoke. when the cloud dissipates"
                . " im lying completely dead on the pavement",
        ],
        self::DRIL_FIGURINE_9 => [
            'label'       => "Dril Figurine #9",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "another day volunteering at the betsy ross museum. everyone keeps asking me if they can fuck the"
                . " flag. buddy, they wont even let me fuck it",
        ],
        self::DRIL_FIGURINE_10 => [
            'label'       => "Dril Figurine #10",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "\"Is Wario A Libertarian\" - the greatest thread in the history of forums, locked by a moderator"
                . " after 12,239 pages of heated debate,",
        ],
        self::DRIL_FIGURINE_11 => [
            'label'       => "Dril Figurine #11",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "who the fuck is scraeming \"LOG OFF\" at my house. show yourself, coward. i will never log off",
        ],
        self::DRIL_FIGURINE_12 => [
            'label'       => "Dril Figurine #12",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "big bird was obviously just a man in a suit. but the other ones were too small to contain men. so"
                . " what the fuck",
        ],
        self::DRIL_FIGURINE_13 => [
            'label'       => "Dril Figurine #13",
            'resources'   => [],
            'weight'      => 140,
            'icon'        => "street-view",
            'description' => self::DESCRIPTION_TEMPLATE_DRIL_FIGURINE
                . "its the weekend baby. youknow what that means. its time to drink precisely one beer and call 911",
        ],
        self::TIMBER => [
            'label'       => "Timber",
            'resources'   => [],
            'weight'      => 1485,
            'icon'        => "tree",
            'description' => "A piece of timber, 1.8 metres long, 75 mm wide and 22 mm thick. This could be useful for"
                . " constructing many objects.",
        ],
        self::NAIL => [
            'label'       => "Nail",
            'resources'   => [],
            'weight'      => 2,
            'icon'        => "toolbox",
            'description' => "A 50 mm long nail, 2.4 mm in diametre. It&apos;s in fairly good condition.",
        ],
        self::HAMMER => [
            'label'       => "Claw Hammer",
            'resources'   => [],
            'weight'      => 454,
            'icon'        => "hammer",
            'description' => "A tool for driving and pulling nails from objects.",
        ],
        self::HAND_SAW => [
            'label'       => "Hand Saw",
            'resources'   => [],
            'weight'      => 360,
            'icon'        => "tools",
            'description' => "A tool for cutting wood.",
        ],
    ];

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var ActionRepository */
    private $actionRepository;

    public function __construct(ResourceRepository $resourceRepository, ActionRepository $actionRepository)
    {
        $this->resourceRepository = $resourceRepository;
        $this->actionRepository = $actionRepository;
    }

    public function find(UuidInterface $id): ?Variety
    {
        if (!array_key_exists(strval($id), self::VARIETIES)) {
            return null;
        }

        $actions = [];

        if (array_key_exists('actions', self::VARIETIES[strval($id)])) {
            foreach (self::VARIETIES[strval($id)]['actions'] as $actionId) {
                $actions[] = $this->actionRepository->find(Uuid::fromString($actionId));
            }
        }

        $resourceContents = [];

        foreach (self::VARIETIES[strval($id)]['resources'] as $resourceId => $amount) {
            $resourceContents[] = new ResourceContent(
                $this->resourceRepository->find(Uuid::fromString($resourceId)),
                $amount
            );
        }

        return new Variety(
            $id,
            self::VARIETIES[strval($id)]['label'],
            $resourceContents,
            self::VARIETIES[strval($id)]['weight'],
            self::VARIETIES[strval($id)]['icon'],
            self::VARIETIES[strval($id)]['description'],
            $actions
        );
    }
}
