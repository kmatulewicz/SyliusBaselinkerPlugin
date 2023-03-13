<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use SyliusBaselinkerPlugin\Services\BaselinkerApiService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    private BaselinkerApiService $baselinker;

    public function __construct(BaselinkerApiService $baselinker)
    {
        $this->baselinker = $baselinker;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('test:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello World!');
        $lastLogId = (string) ($this->baselinker->getLastLogId());
        $output->writeln($lastLogId);

        return Command::SUCCESS;
    }
}
