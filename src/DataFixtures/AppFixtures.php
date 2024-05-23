<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CallApiService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture {

    private $callApiService;
    private $userRepository;

    public function __construct(CallApiService $callApiService, UserRepository $userRepository)
    {
        $this->callApiService = $callApiService;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager) {

        // Augmenter la limite de mémoire
        ini_set('memory_limit', '256M');

        // countries

        $countriesList = $this->callApiService->getCountriesData();

        print "Création des pays et des villes en cours ...";

        $cityObjectList = [];

        foreach ($countriesList as $countryData) {
            $country = new Country();
            $country->setName($countryData['name']);
            $country->setCountryCode($countryData['id']);
            $manager->persist($country);

            $citiesList = $this->callApiService->getCitiesData($countryData['id']);
            foreach ($citiesList as $cityData) {
                $city = new City();
                $city->setName($cityData['name']);
                $city->setCountry($country);
                $cityObjectList[] = $city;
                $manager->persist($city);
            }

            // Libérer la mémoire
            unset($citiesList);
            unset($country);
        }

        // categories

        $categories = [
            'Sport',
            'Culture',
            'Party',
            'Meet',
            'Nature',
            'Food Lover',
            'Music',
        ];

        print "Création des catégories en cours ...";

        $categoriesObjectsList = [];
        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setTitle($categoryName);
            $category->setImage($categoryName . '.jpg');
            $categoriesObjectsList[] = $category;
            $manager->persist($category);
        }

        // users

        print "Création des users en cours ...";

        $usersObjectList = [];
        $gender = ['man', 'woman', 'non-binary'];

        for ($i = 1; $i <= 50; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@example.com');
            $password = 'password';
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setFirstname('Firstname' . $i);
            $user->setLastname('Lastname' . $i);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setNationality('french');
            $user->setGender($gender[array_rand($gender)]);
            $user->setBirthdate(new \DateTimeImmutable('1980-01-01'));

            $user->setFavoriteCity($cityObjectList[array_rand($cityObjectList)]);
            $usersObjectList[] = $user;
            $manager->persist($user);
        }

        // events

        print "Création des événements en cours ...";

        $currentDateTime = new \DateTimeImmutable();

        foreach ($cityObjectList as $city) {
            for ($i = 1; $i <= 2; $i++) {
                $startDate = $currentDateTime->add(new \DateInterval('P' . mt_rand(1, 30) . 'D'));

                $event = new Event();
                $event->setTitle('Event ' . $i . ' for ' . $city->getName());
                $event->setStartAt($startDate);
                $event->setDescription('Description de l\'événement ' . $i . ' in ' . $city->getName());
                $event->setCity($city);
                $event->setCountry($city->getCountry());
                $event->setCategory($categoriesObjectsList[array_rand($categoriesObjectsList)]);
                $creator = $usersObjectList[array_rand($usersObjectList)];
                $event->setCreator($creator);
                $event->setParticipantLimit(mt_rand(5, 20));
                $event->setCreatedAt(new \DateTimeImmutable());
                $event->addParticipant($creator);
                $event->setStatus('OPEN');
                $manager->persist($event);

                // Ajouter entre 1 et 5 participants (autres que le créateur)
                $numParticipants = mt_rand(1, 5);
                $participants = [];
                while (count($participants) < $numParticipants) {
                    $randomUser = $usersObjectList[array_rand($usersObjectList)];
                    if ($randomUser !== $creator && !in_array($randomUser, $participants)) {
                        $participants[] = $randomUser;
                    }
                }

                foreach ($participants as $participant) {
                    $event->addParticipant($participant);
                }
            }
        }

        // Libérer la mémoire des listes temporaires
        unset($cityObjectList);
        unset($usersObjectList);
        unset($categoriesObjectsList);

        $manager->flush();
    }
}
