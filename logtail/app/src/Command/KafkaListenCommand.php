<?php

namespace App\Command;

use App\Service\KafkaService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KafkaListenCommand extends Command
{
    protected static $defaultName        = 'kafka:listen';
    protected static $defaultDescription = 'Add a short description for your command';
    /**
     * @var \App\Service\KafkaService
     */
    private KafkaService $kafka;

    public function __construct(KafkaService $kafka, $name = null)
    {
        parent::__construct($name);
        $this->kafka = $kafka;
    }

    protected function configure(): void
    {
        $this->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
             ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->kafka->listen(['logtail']);
    }
}
