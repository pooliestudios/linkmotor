<?php

namespace Pool\LinkmotorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pool\LinkmotorBundle\Entity\Option;

class LoadDefaultOptions implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $defaultValues = array(
            'account_type' => 0,
            'limit_projects' => 1,
            'limit_users' => 1,
            'limit_prospects' => 1000,
            'limit_backlinks' => 100,
            'self_hosted' => 1
        );

        foreach ($defaultValues as $name => $value) {
            $option = new Option();
            $option->setName($name);
            $option->setValue($value);

            $manager->persist($option);
            $manager->flush();
        }
    }
}
