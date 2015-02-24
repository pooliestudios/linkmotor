<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Service\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testFindAllWithBegins()
    {
        $option = $this->getMock('\Pool\LinkmotorBundle\Entity\Option');
        $option->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('invoice_company'));
        $option->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('pooliestudios'));

        // Now, mock the repository so it returns the mock of the employee
        $optionRepository = $this->getMockBuilder('\Pool\LinkmotorBundle\Entity\OptionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $optionRepository->expects($this->once())
            ->method('getStartingWith')
            ->will($this->returnValue(array($option)));

        // Last, mock the EntityManager to return the mock of the repository
        $entityManager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($optionRepository));

        $options = new Options($entityManager);

        $result = $options->getAll('invoice');
        $this->assertEquals('pooliestudios', $result['invoice_company']);
        $this->assertEquals(8, count($result)); // 8 default options beginning with "invoice"
    }

    public function testFindAllWithoutBegins()
    {
        $option1 = $this->getMock('\Pool\LinkmotorBundle\Entity\Option');
        $option1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_1'));
        $option1->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('1'));
        $option2 = $this->getMock('\Pool\LinkmotorBundle\Entity\Option');
        $option2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_2'));
        $option2->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('2'));

        // Now, mock the repository so it returns the mock of the employee
        $optionRepository = $this->getMockBuilder('\Pool\LinkmotorBundle\Entity\OptionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $optionRepository->expects($this->once())
            ->method('getStartingWith')
            ->will($this->returnValue(array($option1, $option2)));

        // Last, mock the EntityManager to return the mock of the repository
        $entityManager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($optionRepository));

        $options = new Options($entityManager);

        $result = $options->getAll();
        $this->assertEquals('1', $result['test_1']);
        $this->assertEquals('2', $result['test_2']);
        $this->assertEquals(12, count($result)); // 10 default options plus 2 own
    }
}
