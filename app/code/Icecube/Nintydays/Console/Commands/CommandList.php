<?php
namespace Icecube\Nintydays\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Icecube\Nintydays\Service\NintyDays;

class CommandList extends Command
{
    protected $NintyDays;

    public function __construct(
        NintyDays $NintyDays,
        string $name = null
    ) {
        $this->NintyDays = $NintyDays;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('icecube:nintydays')
            ->setDescription('nintydays old orders.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->NintyDays->execute();
        $output->writeln($result);
    }
}
