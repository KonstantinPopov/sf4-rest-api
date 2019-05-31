<?php

namespace App\Command;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Services\Wallet\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncBalanceCommand extends Command
{
    /** @var LoggerInterface */
    private $logger;

    /** @var WalletService */
    private $walletService;

    /** @var WalletRepository */
    private $walletRepository;

    protected static $defaultName = 'app:sync-balance';

    public function __construct(LoggerInterface $logger, WalletService $walletService, WalletRepository $walletRepository)
    {
        $this->logger = $logger;
        $this->walletService = $walletService;
        $this->walletRepository = $walletRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('wallet', InputArgument::OPTIONAL, 'Wallet address');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet = null;
        if ($walletAddress = $input->getArgument('wallet')) {
            $wallet = $this->walletRepository->findOneBy(['address' => $walletAddress]);
            if (!$wallet instanceof Wallet) {
                $output->writeln('Nothing synced');

                return;
            }
        }

        try {
            if ($wallet instanceof Wallet) {
                $this->walletService->syncBalance($wallet);
            } else {
                $this->walletService->syncBalance();
            }

            $output->writeln((new \DateTime())->format(\DateTimeInterface::ATOM).' - balance was synced');
        } catch (\Throwable $e) {
            $this->logger->error('Balance not synced', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
            ]);

            throw $e;
        }
    }
}
