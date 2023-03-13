<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Webmozart\Assert\Assert;

class TestContext extends KernelTestCase implements Context
{

    private Application $app;
    private string $output;

    public function __construct()
    {
        $kernel = self::bootKernel();

        $this->app = new Application($kernel);
        $this->output = "";

        parent::__construct();
    }


    /**
     * @Given I am the system
     */
    public function iAmTheSystem() : void
    {
        Assert::same('cli', php_sapi_name());
    }

    /**
     * @When I run a test command
     */
    public function iRunATestCommand() : void
    {
        $command = $this->app->find('test:test');
        $tester = new CommandTester($command);
        $tester->execute(['']);
        $this->output = $tester->getDisplay();

        $tester->assertCommandIsSuccessful();
    }

    /**
     * @Then I should see :arg1
     */
    public function iShouldSee(string $arg1) : void
    {
        self::assertStringContainsString($arg1,$this->output);
    }
}
