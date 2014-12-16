<?php
namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Alert;
use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\NotificationSetting;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\User;

class MailNotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesAlertErrorAllOff()
    {
        $alert = new Alert();
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setWarnings(false);
        $notificationSetting->setErrors(false);
        $notificationSetting->setAllErrors(true);
        $notificationSetting->setAllWarnings(true);
        $notificationSetting->setWarningsWhen(1);
        $notificationSetting->setErrorsWhen(1);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertWarning()
    {
        $alert = new Alert();
        $alert->setType('w');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setWarnings(true);
        $notificationSetting->setWarningsWhen(0);

        $this->assertTrue($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertWarningDifferentWhen()
    {
        $alert = new Alert();
        $alert->setType('w');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setWarnings(true);
        $notificationSetting->setWarningsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 1));
    }

    public function testEmptyMatchesAlertError()
    {
        $alert = new Alert();
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(0);

        $this->assertTrue($notificationSetting->matchesAlert($alert, 0));
    }

    public function testEmptyMatchesAlertErrorDifferentWhen()
    {
        $alert = new Alert();
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 1));
    }

    public function testEmptyMatchesAlertErrorDifferentWhenButIncludes()
    {
        $alert = new Alert();
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(8); // every morning

        $this->assertTrue($notificationSetting->matchesAlert($alert, 1)); // monday morning
    }

    public function testEmptyMatchesAlertErrorDifferentWhenButIncludes2()
    {
        $alert = new Alert();
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(8); // every morning

        $this->assertTrue($notificationSetting->matchesAlert($alert, 5)); // friday morning
    }

    public function testMatchesAlertWarningDifferentProject()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('w');

        $domain2 = new Domain();
        $domain2->setName('test.com');
        $project2 = new Project();
        $project2->setDomain($domain2);
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setProject($project2);
        $notificationSetting->setWarnings(true);
        $notificationSetting->setWarningsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertErrorDifferentProject()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('e');

        $domain2 = new Domain();
        $domain2->setName('test.com');
        $project2 = new Project();
        $project2->setDomain($domain2);
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setProject($project2);
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertErrorDefaultSetting()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('e');

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setErrorsWhen(0);

        $this->assertTrue($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertErrorDifferentUserAllUsersWantedButNoAdmin()
    {
        $user1 = new User();
        $user1->setEmail('test@test.de');
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('e');
        $alert->setUser($user1);

        $user2 = new User();
        $user2->setEmail('test@test.com');
        $user2->setIsAdmin(false);
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setAllErrors(true);
        $notificationSetting->setUser($user2);
        $notificationSetting->setErrorsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertErrorDifferentUserButAllUsersWanted()
    {
        $user1 = new User();
        $user1->setEmail('test@test.de');
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('e');
        $alert->setUser($user1);

        $user2 = new User();
        $user2->setEmail('test@test.com');
        $user2->setIsAdmin(true);
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setAllErrors(true);
        $notificationSetting->setUser($user2);
        $notificationSetting->setErrorsWhen(0);

        $this->assertTrue($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesWarningErrorDifferentUserButAllUsersWanted()
    {
        $user1 = new User();
        $user1->setEmail('test@test.de');
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('w');
        $alert->setUser($user1);

        $user2 = new User();
        $user2->setEmail('test@test.com');
        $user2->setIsAdmin(true);
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setWarnings(true);
        $notificationSetting->setAllWarnings(true);
        $notificationSetting->setUser($user2);
        $notificationSetting->setWarningsWhen(0);

        $this->assertTrue($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesWarningErrorDifferentUser()
    {
        $user1 = new User();
        $user1->setEmail('test@test.de');
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('w');
        $alert->setUser($user1);

        $user2 = new User();
        $user2->setEmail('test@test.com');
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setUser($user2);
        $notificationSetting->setWarnings(true);
        $notificationSetting->setAllWarnings(false);
        $notificationSetting->setWarningsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testMatchesAlertErrorDifferentUser()
    {
        $user1 = new User();
        $user1->setEmail('test@test.de');
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);
        $alert = new Alert();
        $alert->setProject($project1);
        $alert->setType('e');
        $alert->setUser($user1);

        $user2 = new User();
        $user2->setEmail('test@test.com');
        $notificationSetting = new NotificationSetting();
        $notificationSetting->setErrors(true);
        $notificationSetting->setAllErrors(false);
        $notificationSetting->setUser($user2);
        $notificationSetting->setErrorsWhen(0);

        $this->assertFalse($notificationSetting->matchesAlert($alert, 0));
    }

    public function testAllWarningsOrErrorsReturnsAlwaysFalseOnNonAdmin()
    {
        $user = new User();
        $user->setIsAdmin(false);

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setUser($user);
        $notificationSetting->setErrors(true);
        $notificationSetting->setAllErrors(true);
        $notificationSetting->setWarnings(true);
        $notificationSetting->setAllWarnings(true);

        $this->assertFalse($notificationSetting->getAllErrors());
        $this->assertFalse($notificationSetting->getAllWarnings());
    }

    public function testAllWarningsOrErrorsReturnsAlwaysFalseWhenWarningOrErrorsAreSwitchedOff()
    {
        $user = new User();
        $user->setIsAdmin(true);

        $notificationSetting = new NotificationSetting();
        $notificationSetting->setUser($user);
        $notificationSetting->setErrors(false);
        $notificationSetting->setAllErrors(true);
        $notificationSetting->setWarnings(false);
        $notificationSetting->setAllWarnings(true);

        $this->assertFalse($notificationSetting->getAllErrors());
        $this->assertFalse($notificationSetting->getAllWarnings());
    }
}
