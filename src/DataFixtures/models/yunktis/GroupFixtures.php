<?php

namespace App\DataFixtures\models\yunktis;

use App\DataFixtures\MapsFixtures;
use App\DataFixtures\OverlayFixtures;
use App\Entity\AnswerGroup;
use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MapGroup;
use App\Entity\MatchGroup;
use App\Entity\PlanningGroup;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Entity\TwitchGroup;
use App\Repository\LibMapRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    private const MODEL = 'yunktis';

    public function __construct(LibMapRepository $libMapRepository)
    {
        $this->libMapRepository = $libMapRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $this->setCameraGroup($manager);
        $this->setInfoGroup($manager);
        $this->setMatchGroup($manager);
        $this->setPollGroup($manager);
        $this->setPopupGroup($manager);
        $this->setTweetGroup($manager);
        $this->setMapGroupBO3($manager);
        $this->setTwitchPoll($manager);
        $this->setTwitchPrediction($manager);
    }

    private function setCameraGroup(ObjectManager $manager): void
    {
        $playersTeamA = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'];
        $playersTeamB = ['Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett'];

        foreach ($playersTeamA as $key => $player) {
            $camera = new CameraGroup();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setName("Camera $player");
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->setTeam("Alpha");
            $this->addReference("camera-group-" . self::MODEL . "-$player", $camera);

            $manager->persist($camera);
            $manager->flush();
        }

        foreach ($playersTeamB as $key => $player) {
            $camera = new CameraGroup();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setName("Camera $player");
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->setTeam("Beta");
            $this->addReference("camera-group-" . self::MODEL . "-$player", $camera);

            $manager->persist($camera);
            $manager->flush();
        }
    }

    private function setInfoGroup(ObjectManager $manager): void
    {
        $group = new InfoGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Info RoadToLan'));
        $group->setTitre('#RoadToLan');
        $group->setLogo('https://cdn.streamcave.tv/models/roadtolan/logo.svg');
        $group->setDescription('Groupe info de la RoadToLan');
        $group->setTextScroll(['Bienvenue sur la roadtolan !', 'Sixquatre assure le Cast de ce évènement R6 en compagnie de StreamCave.', 'Bonne chance à tous !']);

        $this->addReference('info-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setMatchGroup(ObjectManager $manager): void
    {
        $group = new MatchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Match RoadToLan'));
        $group->setTeamNameA('Alpha');
        $group->setLogoTeamA('https://cdn.streamcave.tv/teams/alpha.png');
        $group->setPlayersTeamA(['Ace', 'Castle', 'Pulse', 'Thatcher', 'Thermite']);
        $group->setScoreA("3");
        $group->setTeamNameB('Beta');
        $group->setLogoTeamB('https://cdn.streamcave.tv/teams/beta.png');
        $group->setPlayersTeamB(['Ash', 'Blackbeard', 'Capitao', 'Doc', 'Montagne']);
        $group->setScoreB("1");
        $group->setRounds("BO1");
        $group->setStartDate(new \DateTimeImmutable("2023-03-31 12:00:00"));
        $group->setNextMatch(false);
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());

        $manager->persist($group);
        $manager->flush();
        $this->addReference('match-group-' . self::MODEL, $group);

    }

    private function setPollGroup(ObjectManager $manager): void
    {
        $group = new PollGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Poll'));
        $group->setQuestion('Quel joueur est en attaque ?');
        $group->setTime(300);
        $group->setChannel("sixquatre");
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());
        $group->setPollStarted(false);
        $group->setVisible(false);
        $group->setChoices(['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett']);

        $this->addReference('poll-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setAnswers(ObjectManager $manager): void
    {
        $group = $this->getReference('poll-group-' . self::MODEL);
        $answers = ['Yes', 'No'];

        foreach ($answers as $key => $answer) {
            $answer = new AnswerGroup();
            $answer->setUuid(Uuid::v5(Uuid::v6(), $answer));
            $answer->setAnswer($answer);
            $answer->setPollGroup($group);
            $answer->setVote("Yes");
            $answer->setUsernameVoter("BRIETGAME");

            $manager->persist($answer);
            $manager->flush();
        }
    }

    private function setPopupGroup(ObjectManager $manager): void
    {
        $group = new PopupGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Popup'));
        $group->setContent('Bienvenue en cettre première édition de 2023 !');

        $this->addReference('popup-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setTweetGroup(ObjectManager $manager): void
    {
        $group = new TweetGroup();
        $group->setPseudo('BRIETGAME');
        $group->setAt('brietgame');
        $group->setContent('Bienvenue sur la roadtolan !');
        $group->setVisible(false);
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());
        $group->setHashtag('RoadToLan');

        $this->addReference('tweet-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setMapGroupBO3(ObjectManager $manager): void
    {
        $maps = ['border', 'oregon', 'kafe'];
        $isPick = [true, true, false];
        $winTeam = ['Alpha', 'Alpha', 'Beta'];

        foreach ($maps as $key => $map) {
            $group = new MapGroup();
            $group->setUuid(Uuid::v5(Uuid::v6(), $map));
            $group->setLibMap($this->getReference('map-' . $map));
            $group->setPickTeam("Alpha");
            $group->setWinTeam($winTeam[$key]);
            $this->addReference('map-group-' . self::MODEL . '-bo3-' . $map, $group);

            $manager->persist($group);
            $manager->flush();
        }
    }

    private function setTwitchPoll(ObjectManager $manager): void
    {
        $group = new TwitchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Twitch Poll'));

        $this->addReference('twitch-poll-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setTwitchPrediction(ObjectManager $manager): void
    {
        $group = new TwitchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Twitch Prediction'));

        $this->addReference('twitch-prediction-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MapsFixtures::class,
            OverlayFixtures::class
        ];
    }
}
