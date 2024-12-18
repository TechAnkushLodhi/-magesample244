<?php
namespace Icecube\OrderReminder\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Icecube\OrderReminder\Service\ReminderService;

class CommandList extends Command
{
    protected $reminderService;

    public function __construct(
        ReminderService $reminderService,
        string $name = null
    ) {
        $this->reminderService = $reminderService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('icecube:reminder')
            ->setDescription('Send order reminder emails to customers with orders.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->reminderService->execute();
        $output->writeln($result);
    }
}
