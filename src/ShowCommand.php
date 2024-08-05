<?php

namespace Block5Challenge;

use Exception;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends Command
{

    private const API_KEY = '24037734';
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('show')
            ->setDescription('Show information about a movie')
            ->addArgument('title', InputArgument::REQUIRED, 'Movie title')
            ->addOption('fullPlot', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = Command::SUCCESS;

        try {
            $query = [
                'apiKey' => self::API_KEY,
                't' => $input->getArgument(('title')),
                'plot' => !$input->getOption('fullPlot') ? 'short' : 'full'
            ];

            $response = $this->client->request('GET', '/', [
                'query' => $query
            ]);
            $bodyArray = json_decode($response->getBody(), true);

            $rows = [];

            foreach ($bodyArray as $key => $value) {
                $rowValue = [$key, $value];

                if ('Ratings' === $key) {
                    $ratings = [];

                    foreach ($value as $rating) {
                        array_push($ratings, implode(': ', $rating));
                    }

                    $rowValue = [$key, implode(', ', $ratings)];
                }

                array_push($rows, $rowValue);
            }

            $output->writeln('<info>' . $bodyArray['Title'] . ' - ' . $bodyArray['Year'] . '</info>');

            $table = new Table($output);
            $table->setRows($rows)
                ->render();
        } catch (Exception) {
            $result = Command::FAILURE;
        }

        return $result;
    }
}
