<?php

namespace Recca0120\Terminal\Tests\Console\Commands;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Terminal\Console\Commands\ArtisanTinker;
use Symfony\Component\Console\Output\BufferedOutput;

class ArtisanTinkerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleEcho()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = 'echo 123');
        $command->handle();

        $this->assertStringContainsString('123', $this->lf($output->fetch()));
    }

    public function testHandleVarDump()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = 'var_dump(123)');
        $this->assertNull($command->handle());

        $this->assertStringContainsString('int(123)', $this->lf($output->fetch()));
    }

    public function testHandleObject()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = 'new stdClass;');
        $command->handle();

        if (version_compare(phpversion(), '7.3.0', '>=')) {
            $this->assertSame("=> (object) array(\n)\n", $this->lf($output->fetch()));
        } else {
            $this->assertSame("=> stdClass::__set_state(array(\n))\n", $this->lf($output->fetch()));
        }
    }

    public function testHandleArray()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = "['foo' => 'bar'];");
        $command->handle();

        $this->assertSame("=> array (\n  'foo' => 'bar',\n)\n", $this->lf($output->fetch()));
    }

    public function testHandleString()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = "'abc'");
        $command->handle();

        $this->assertSame("=> abc\n", $this->lf($output->fetch()));
    }

    public function testNumeric()
    {
        $command = new ArtisanTinker();
        $this->mockProperty($command, 'input', $input = m::mock('Symfony\Component\Console\Input\InputInterface'));
        $this->mockProperty($command, 'output', $output = new BufferedOutput);

        $input->shouldReceive('getOption')->once()->with('command')->andReturn($cmd = '123');
        $command->handle();

        $this->assertSame("=> 123\n", $this->lf($output->fetch()));
    }

    protected function mockProperty($object, $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }

    protected function lf($content)
    {
        return str_replace("\r\n", "\n", $content);
    }
}
