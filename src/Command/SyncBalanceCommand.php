<?php

namespace App\Command;

use App\Services\Wallet\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncBalanceCommand extends Command
{
    private $logger;

    /** @var WalletService */
    private $walletService;

    protected static $defaultName = 'app:sync-balance';

    public function __construct(LoggerInterface $logger, WalletService $walletService)
    {
        $this->logger = $logger;
        $this->walletService = $walletService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $this->walletService->syncBalance();
        $output->writeln((new \DateTime())->format(\DateTimeInterface::ATOM) . ' - balance was synced');
    }
}
