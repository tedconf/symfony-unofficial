<?php

namespace Symfony\Tests\Component\Form\Renderer;

abstract class RendererTestCase extends \PHPUnit_Framework_TestCase
{
    protected function createFieldMock($name, $id, $displayedData)
    {
        $field = $this->getMock('Symfony\Component\Form\FieldInterface');

        $field->expects($this->any())
                    ->method('getDisplayedData')
                    ->will($this->returnValue($displayedData));
        $field->expects($this->any())
                    ->method('getName')
                    ->will($this->returnValue($name));
        $field->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue($id));

        return $field;
    }
}
