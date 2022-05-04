<?php

namespace App\Command;

use App\Exception\CsvFormatException;
use App\Handler\AddressGroupHandler;
use App\Repository\AddressRepository;
use App\Repository\ElectorRepository;
use App\Repository\GroupedAddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FindGroupsCommand extends Command
{
    protected static $defaultName = 'eleclist:find-groups';

    protected static $defaultDescription = 'Find address groups and persist';

    private AddressGroupHandler $addressGroupHandler;
    private AddressRepository $addressRepository;
    private EntityManagerInterface $entityManager;
    private GroupedAddressRepository $groupedAddressRepository;
    private ElectorRepository $electorRepository;

    public function __construct(
        AddressRepository $addressRepository,
        AddressGroupHandler $addressGroupHandler,
        EntityManagerInterface $entityManager,
        GroupedAddressRepository $groupedAddressRepository,
        ElectorRepository $electorRepository,
        string $name = null
    ) {
        $this->addressGroupHandler = $addressGroupHandler;
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
        $this->groupedAddressRepository = $groupedAddressRepository;
        $this->electorRepository = $electorRepository;

        parent::__construct($name);
    }

    /**
     * @throws CsvFormatException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Clearing previous data...');
        $this->clearData($io);

        $io->section('Grouping addresses...');
        $groupedAddresses = $this->addressRepository->getGroupedAddresses()->getResult();
        $counter = 0;
        $count = count($groupedAddresses);
        $failCount = 0;
        $total = 0;
        $electorsCount = 0;
        $totalElectorsCount = count($this->electorRepository->findAll());

        $progress = $io->createProgressBar($count);

        foreach ($groupedAddresses as $key => $data) {
            $total += 1;
            if (!$this->addressGroupHandler->checkData($data)) {
                $failCount += 1;
                continue;
            }

            $address = $this->addressGroupHandler->createGroupedAddress($data);
            $electorsCount += $address->getElectors()->count();
            $this->entityManager->persist($address);
            $progress->advance();

            if ($counter === 50 || $key === $count - 1) {
                $this->entityManager->flush();
                $counter = 0;
                continue;
            }

            $counter += 1;
        }

        $electorFails = $totalElectorsCount - $electorsCount;

        $progress->finish();
        $io->newLine(3);
        $io->success("Address grouping successful. ");
        $io->newLine();
        $io->info("Grouped addresses: $total success and $failCount fails");
        $io->newLine();
        if ($totalElectorsCount) {
            $io->warning(
                "$electorFails electors on $totalElectorsCount are not grouped to any "
                . "address due to incomplete address data."
            );
        }

        return Command::SUCCESS;
    }

    private function clearData(SymfonyStyle $io)
    {
        $toDelete = $this->groupedAddressRepository->findAll();
        $deleteProgress = $io->createProgressBar(count($toDelete));

        foreach ($toDelete as $address) {
            $this->entityManager->remove($address);
            $deleteProgress->advance(1);
        }

        $this->entityManager->flush();
        $io->newLine(3);
    }
}
